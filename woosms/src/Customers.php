<?php

namespace BulkGate\WooSms;

/**
 * @author Lukáš Piják 2020 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Extensions;
use BulkGate\Extensions\Database\IDatabase;

class Customers extends Extensions\Customers
{
    public function __construct(IDatabase $db)
    {
        parent::__construct($db);
        $this->table_user_key = 'user_id';
    }


    public function getTotal()
    {
        return (int) $this->db->execute("SELECT COUNT(DISTINCT `user_id`) AS `total` FROM `{$this->db->table('usermeta')}` WHERE  `meta_key` = 'billing_phone' AND `meta_value` != '' LIMIT 1")->getRow()->total;
    }


    public function getFilteredTotal(array $customers)
    {
        return (int) $this->db->execute("SELECT COUNT(DISTINCT `user_id`) AS `total` FROM `{$this->db->table('usermeta')}` WHERE `user_id` IN ('".implode("','", $customers)."') AND `meta_key` = 'billing_phone' AND `meta_value` != '' LIMIT 1")->getRow()->total;
    }


    protected function loadCustomers(array $customers, $limit = null)
    {
        return $this->db->execute("
            SELECT      `user_id` AS `order`,
                        MAX(CASE WHEN meta_key = 'billing_first_name' AND meta_value IS NOT NULL THEN meta_value ELSE (CASE WHEN meta_key = 'first_name' THEN  meta_value ELSE (CASE WHEN meta_key = 'shipping_first_name' THEN  meta_value END) END) END) first_name,
                        MAX(CASE WHEN meta_key = 'billing_last_name' AND meta_value IS NOT NULL THEN meta_value ELSE (CASE WHEN meta_key = 'last_name' THEN  meta_value ELSE (CASE WHEN meta_key = 'shipping_last_name' THEN  meta_value END)  END) END) last_name,
                        MAX(CASE WHEN meta_key = 'billing_phone' THEN meta_value END) phone_mobile,
                        MAX(CASE WHEN meta_key = 'billing_company' THEN meta_value ELSE (CASE WHEN meta_key = 'shipping_company' THEN  meta_value END) END) company_name,
                        MAX(CASE WHEN meta_key = 'billing_country' THEN LOWER(meta_value) ELSE (CASE WHEN meta_key = 'shipping_country' THEN  LOWER(meta_value) END)  END) country,
                        MAX(CASE WHEN meta_key = 'billing_address_1' THEN meta_value ELSE (CASE WHEN meta_key = 'shipping_address_1' THEN  meta_value END) END) street1,
                        MAX(CASE WHEN meta_key = 'billing_address_2' THEN meta_value ELSE (CASE WHEN meta_key = 'shipping_address_2' THEN  meta_value END) END) street2,
                        MAX(CASE WHEN meta_key = 'billing_postcode' THEN meta_value ELSE (CASE WHEN meta_key = 'shipping_postcode' THEN  meta_value END) END) zip,
                        MAX(CASE WHEN meta_key = 'billing_city' THEN meta_value ELSE (CASE WHEN meta_key = 'shipping_city' THEN  meta_value END) END) city,
                        MAX(CASE WHEN meta_key = 'billing_email' THEN meta_value END) email
            FROM `{$this->db->table('usermeta')}`
            ". (count($customers) > 0 ? "WHERE `user_id` IN ('".implode("','", $customers)."') " : '') . "
            GROUP BY `user_id`
            HAVING `phone_mobile` NOT LIKE '' 	
            ". ($limit !== null ? "LIMIT $limit" : ''))->getRows();
    }


    protected function filter(array $filters)
    {
        $customers = array(); $filtered = false;

        foreach ($filters as $key => $filter)
        {
            if (isset($filter['values']) && count($filter['values']) > 0 && !$this->empty)
            {
                switch ($key)
                {
                    case 'first_name':
                        $customers = $this->getCustomers($this->db->execute("SELECT `user_id` FROM `{$this->db->table('usermeta')}` WHERE `meta_key` IN ('first_name', 'billing_first_name', 'shipping_first_name') AND {$this->getSql($filter)}"), $customers);
                        break;
                    case 'last_name':
                        $customers = $this->getCustomers($this->db->execute("SELECT `user_id` FROM `{$this->db->table('usermeta')}` WHERE `meta_key` IN ('last_name', 'billing_last_name', 'shipping_last_name') AND {$this->getSql($filter)}"), $customers);
                        break;
                    case 'country':
                        $customers = $this->getCustomers($this->db->execute("SELECT `user_id` FROM `{$this->db->table('usermeta')}` WHERE  `meta_key` IN ('shipping_country', 'billing_country') AND {$this->getSql($filter)}"), $customers);
                        break;
                    case 'city':
                        $customers = $this->getCustomers($this->db->execute("SELECT `user_id` FROM `{$this->db->table('usermeta')}` WHERE `meta_key` IN ('billing_city', 'shipping_city') AND {$this->getSql($filter)}"), $customers);
                        break;
                    case 'order_amount':
                        $customers = $this->getCustomers($this->db->execute("SELECT `post_author` AS `user_id`, MAX(`meta_value`) AS `meta_value` FROM `{$this->db->table('posts')}` INNER JOIN `{$this->db->table('postmeta')}` ON `ID` = `post_id` WHERE `meta_key` = '_order_total' GROUP BY `post_author` HAVING {$this->getSql($filter)}"), $customers);
                        break;
                    case 'all_orders_amount':
                        $customers = $this->getCustomers($this->db->execute("SELECT `post_author` AS `user_id`, SUM(`meta_value`) AS `meta_value` FROM `{$this->db->table('posts')}` INNER JOIN `{$this->db->table('postmeta')}` ON `ID` = `post_id` WHERE `meta_key` = '_order_total' GROUP BY `post_author` HAVING {$this->getSql($filter)}"), $customers);
                        break;
                    case 'product':
                        $customers = $this->getCustomers($this->db->execute("SELECT `post_author` AS `user_id` FROM `{$this->db->table('woocommerce_order_items')}` INNER JOIN `{$this->db->table('posts')}` ON `ID` = `order_id` WHERE {$this->getSql($filter, 'order_item_name')}"), $customers);
                        break;
                    case 'registration_date':
                        $customers = $this->getCustomers($this->db->execute("SELECT `ID` AS `user_id` FROM `{$this->db->table('users')}` WHERE {$this->getSql($filter, 'user_registered')}"), $customers);
                        break;
                    case 'order_date':
                        $customers = $this->getCustomers($this->db->execute("SELECT `post_author` AS `user_id` FROM `{$this->db->table('posts')}` WHERE `post_type`='shop_order' AND {$this->getSql($filter, 'post_date')}"), $customers);
                        break;
                    case 'order_status':
                        $customers = $this->getCustomers($this->db->execute("SELECT `post_author` AS `user_id` FROM `{$this->db->table('posts')}` WHERE `post_type`='shop_order' AND {$this->getSql($filter, 'post_status')}"), $customers);
                        break;
                }
                $filtered = true;
            }
        }

        return array(array_unique($customers), $filtered);
    }
}
