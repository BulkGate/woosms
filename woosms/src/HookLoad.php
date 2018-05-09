<?php
namespace BulkGate\WooSms;

use BulkGate;
use BulkGate\Extensions\Hook\Variables;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class HookLoad extends BulkGate\Extensions\Strict implements BulkGate\Extensions\Hook\ILoad
{
    /** @var Database */
    private $db;
    
    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function order(Variables $variables)
    {
        if($variables->get('order_id'))
        {
            $variables->set("long_order_id", sprintf("%06d", $variables->get('order_id')));

            $row = woosms_get_order_meta_array($variables->get('order_id'));

            $variables->set('customer_id', $row->post_author);
            $variables->set('customer_message', $row->post_excerpt);
            $variables->set('order_ref', $row->_order_key);

            if(isset($row->_order_currency))
            {
                $variables->set('order_currency', $row->_order_currency);
            }

            $variables->set('order_payment', $row->_payment_method_title);

            $variables->set('order_date1', preg_replace('/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/', '\\3.\\2.\\1', $row->post_date));
            $variables->set('order_date2', preg_replace('/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/', '\\3/\\2/\\1', $row->post_date));
            $variables->set('order_date3', preg_replace('/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/', '\\3-\\2-\\1', $row->post_date));
            $variables->set('order_date4', preg_replace('/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/', '\\1-\\2-\\3', $row->post_date));
            $variables->set('order_date5', preg_replace('/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/', '\\2.\\3.\\1', $row->post_date));
            $variables->set('order_date6', preg_replace('/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/', '\\2/\\3/\\1', $row->post_date));
            $variables->set('order_date7', preg_replace('/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/', '\\2-\\3-\\1', $row->post_date));
            $variables->set('order_time', preg_replace('/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/', '\\4:\\5', $row->post_date));
            $variables->set('order_time1', preg_replace('/([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/', '\\4:\\5:\\6', $row->post_date));

            $variables->set('customer_company', $row->_shipping_company, $row->_billing_company);
            $variables->set('customer_lastname', $row->_shipping_last_name, $row->_billing_last_name);
            $variables->set('customer_firstname', $row->_shipping_first_name, $row->_billing_first_name);

            if(strlen($row->_shipping_address_2) > 0)
            {
                $variables->set('customer_address', $row->_shipping_address_1 . ', ' . $row->_shipping_address_2, $row->_billing_address_1 . ', ' . $row->_billing_address_2);
            }
            else
            {
                $variables->set('customer_address', $row->_shipping_address_1, $row->_billing_address_1);
            }

            $variables->set('customer_postcode', $row->_shipping_postcode, $row->_billing_postcode);
            $variables->set('customer_city', $row->_shipping_city, $row->_billing_city);
            $variables->set('customer_country_id', $row->_shipping_country, $row->_billing_country);
            $variables->set('customer_state', $row->_shipping_state, $row->_billing_state);


            $variables->set('customer_country', strtolower($row->_shipping_country));

            $variables->set('customer_phone', $row->_billing_phone);
            $variables->set('customer_mobile', $row->_billing_phone);

            $variables->set('customer_invoice_company', $row->_billing_company, $row->_shipping_company);
            $variables->set('customer_invoice_lastname', $row->_billing_last_name, $row->_shipping_last_name);
            $variables->set('customer_invoice_firstname', $row->_billing_first_name, $row->_shipping_first_name);

            if(strlen($row->_billing_address_2) > 0)
            {
                $variables->set('customer_invoice_address', $row->_billing_address_1 . ', ' . $row->_billing_address_2, $row->_shipping_address_1 . ', ' . $row->_shipping_address_2);
            }
            else
            {
                $variables->set('customer_invoice_address', $row->_billing_address_1, $row->_shipping_address_1);
            }

            $variables->set('customer_invoice_postcode', $row->_billing_postcode);
            $variables->set('customer_invoice_city', $row->_billing_city);
            $variables->set('customer_invoice_country_id', $row->_billing_country);
            $variables->set('customer_invoice_state', $row->_billing_state);

            $variables->set('customer_invoice_country', strtolower($row->_billing_country));

            $variables->set('customer_invoice_phone', $row->_billing_phone);
            $variables->set('customer_invoice_mobile', $row->_billing_phone);

            $variables->set('order_total_paid', number_format($row->_order_total ? $row->_order_total : 0, 2));


            $result = $this->db->execute(
                $this->db->prepare(
                    'SELECT `order_item_id` FROM `'.$this->db->prefix().'woocommerce_order_items` WHERE `order_item_type` = \'line_item\' AND `order_id` = %s',
                    array((int) $variables->get('order_id'))
                )
            );

            $newOrder1_pre = $newOrder2_pre = $newOrder3_pre = $newOrder4_pre = $sms_printer1 = $sms_printer2 = array();

            if($result->getNumRows() > 0)
            {

                foreach($result as $row)
                {
                    $order_item_id = $row->order_item_id;

                    $result = $this->db->execute($this->db->prepare(
                        'SELECT 
                                MAX(CASE WHEN `meta_key` = \'_qty\' THEN `meta_value` END) qty,
                                MAX(CASE WHEN `meta_key` = \'_product_id\' THEN `meta_value` END) product_id,
                                MAX(CASE WHEN `meta_key` = \'_tmcartepo_data\' THEN `meta_value` END) tmcartepo_data
                            FROM  `'.$this->db->prefix().'woocommerce_order_itemmeta` WHERE `order_item_id` = %s
                    ' , array((int) $order_item_id))
                    );

                    if($result->getNumRows())
                    {
                        $qty = $result->getRow()->qty;
                        $product_id = $result->getRow()->product_id;
                        $meta = get_post_meta($product_id, '_sale_price');
                        $data = (array) get_post($product_id);
                        $params = '';

                        if(isset($result->getRow()->tmcartepo_data))
                        {
                            $params = '\n';
                            $product_list = unserialize($result->getRow()->tmcartepo_data);

                            if(is_array($product_list))
                            {
                                foreach($product_list as $item)
                                {
                                    if(strlen($item['name']))
                                    {
                                        $params .= '- ' .$item['quantity'].'x '.$item['name'].': '.$item['value'].' '.$item['price'] . $variables->get('order_currency') . ' \n';
                                    }
                                }
                            }
                        }

                        if(count($data))
                        {
                            $newOrder1_pre[] = $qty.'x '.$data['post_title'].' '.$data['post_name'].' '.$params;
                            $newOrder2_pre[] = $qty.'x '.$data['post_title'].' '.$params;
                            $newOrder3_pre[] = $qty.'x ('.$product_id.')'.$data['post_title'].' '.$data['post_name'].' '.$params;
                            $newOrder4_pre[] = $qty.'x '.$data['post_name'].' '.$params;

                            $sms_printer1[] = $qty.','.$data['post_title'].','.$meta[0];
                            $sms_printer2[] = $qty.';'.$data['post_title'].';'.$meta[0];
                        }
                    }
                }
            }

            $variables->set('order_products1', implode('; ', $newOrder1_pre));
            $variables->set('order_products2', implode('; ', $newOrder2_pre));
            $variables->set('order_products3', implode('; ', $newOrder3_pre));
            $variables->set('order_products4', implode('; ', $newOrder4_pre));

            $variables->set('order_products5', implode('\n', $newOrder1_pre));
            $variables->set('order_products6', implode('\n', $newOrder2_pre));
            $variables->set('order_products7', implode('\n', $newOrder3_pre));
            $variables->set('order_products8', implode('\n', $newOrder4_pre));

            $variables->set('order_smsprinter1', implode(';', $sms_printer1));
            $variables->set('order_smsprinter2', implode(';', $sms_printer2));
        }
    }

    public function customer(Variables $variables)
    {
        /** @var \wpdb $wpdb */
        global $wpdb;

        if($variables->get('customer_id'))
        {
            $result = $this->db->execute(
                $this->db->prepare('SELECT * FROM '.$wpdb->users.' WHERE `ID` = %s', array((int) $variables->get('customer_id')))
            );

            if($result->getNumRows())
            {
                foreach($result as $row)
                {
                    $variables->set('customer_email', $row->user_email);
                    $variables->set('customer_name', $row->display_name);
                }
            }
        }

        $row = woosms_get_address_meta_array($variables->get('customer_id'));

        if(is_array($row) && count($row))
        {
            $variables->set('customer_firstname', $row->first_name);
            $variables->set('customer_lastname', $row->last_name);

            $variables->set('customer_phone', $row->billing_phone);
            $variables->set('customer_mobile', $row->billing_phone);

            $variables->set('customer_company', $row->shipping_company, $row->billing_company);

            $variables->set('customer_country_id', $row->shipping_country, $row->billing_country);

            if(strlen($row->_shipping_address_2) > 0)
            {
                $variables->set('customer_address', $row->shipping_address_1 . ', ' . $row->shipping_address_2, $row->billing_address_1 . ', ' . $row->billing_address_2);
            }
            else
            {
                $variables->set('customer_address', $row->shipping_address_1, $row->billing_address_1);
            }

            $variables->set('customer_postcode', $row->shipping_postcode, $row->billing_postcode);
            $variables->set('customer_city', $row->shipping_city, $row->billing_city);
            $variables->set('customer_country_id', $row->shipping_country, $row->billing_country);
            $variables->set('customer_state', $row->shipping_state, $row->billing_state);

            $variables->set('customer_country', $row->shipping_country);

        }

        if(!$variables->get('shop_id'))
        {
            $variables->set('shop_id', 0);
        }
    }

    public function orderStatus(Variables $variables)
    {
        if(function_exists('wc_get_order_statuses'))
        {
            $statuses = \wc_get_order_statuses();

            if(isset($statuses[$variables->get('order_status_id')]))
            {
                $variables->set('order_status', $statuses[$variables->get('order_status_id')], $variables->get('order_status_id'));
            }
        }
    }

    public function returnOrder(Variables $variables)
    {
    }

    public function shop(Variables $variables)
    {
        $result = $this->db->execute("SELECT * FROM `".$this->db->prefix()."options` WHERE `option_name` IN ('blogname','admin_email','siteurl','woocommerce_currency')");

        if ($result->getNumRows() > 0)
        {
            foreach($result as $row)
            {
                if($row->option_name === 'blogname')
                {
                    $variables->set('shop_name', $row->option_value);
                }

                if($row->option_name === 'admin_email')
                {
                    $variables->set('shop_email', $row->option_value);
                }

                if($row->option_name === 'siteurl')
                {
                    $variables->set('shop_domain', $row->option_value);
                }

                if($row->option_name === 'woocommerce_currency')
                {
                    $variables->set('shop_currency', $row->option_value);
                }
            }
        }
    }

    public function extension(Variables $variables)
    {
        if(class_exists('BulkGate\WooSMS\HookExtension'))
        {
            $hook = new HookExtension();
            $hook->extend($this->db, $variables);
        }
    }

    public function product(Variables $variables)
    {
        // TODO
    }

    public function load(BulkGate\Extensions\Hook\Variables $variables)
    {
        $this->order($variables);
        $this->orderStatus($variables);
        $this->customer($variables);
        $this->returnOrder($variables);
        $this->shop($variables);
        $this->extension($variables);
    }
}
