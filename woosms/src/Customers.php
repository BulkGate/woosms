<?php
namespace BulkGate\WooSms;

use BulkGate\Extensions;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class Customers extends Extensions\Strict implements Extensions\ICustomers
{
    /** @var Extensions\Database\IDatabase */
    private $db;

    /** @var bool */
    private $empty = false;

    public function __construct(Extensions\Database\IDatabase $db)
    {
        $this->db = $db;
    }

    public function loadCount(array $filter = array())
    {
        $customers = array();

        $filtered_count = $total = (int) $this->db->execute("SELECT COUNT(DISTINCT `user_id`) AS `total` FROM `{$this->db->table('usermeta')}` WHERE  `meta_key` = 'billing_phone' AND `meta_value` != '' LIMIT 1")->getRow()->total;
        if(count($filter) > 0)
        {
            list($customers, $filtered) = $this->filter($filter);

            if($filtered)
            {
                $filtered_count = (int) $this->db->execute("SELECT COUNT(DISTINCT `user_id`) AS `total` FROM `{$this->db->table('usermeta')}` WHERE `user_id` IN ('".implode("','", $customers)."') AND `meta_key` = 'billing_phone' AND `meta_value` != '' LIMIT 1")->getRow()->total;
            }
        }

        return array('total' => $total, 'count' => $filtered_count, 'limit' => $filtered_count !== 0 ? $this->loadCustomers($customers, 10) : array());
    }

    public function load(array $filter = array())
    {
        $customers = array();

        if(count($filter) > 0)
        {
            list($customers, $filtered) = $this->filter($filter);
        }

        return $this->loadCustomers($customers);
    }

    private function loadCustomers($customers, $limit = null)
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
            ". (count($customers) > 0 ? "WHERE `user_id` IN ('".implode("','", $customers)."') " : "") . "
            GROUP BY `user_id`
            HAVING `phone_mobile` NOT LIKE '' 	
            ". ($limit !== null ? "LIMIT $limit" : ""))->getRows();
    }

    private function filter(array $filters)
    {
        $customers = array(); $filtered = false;

        foreach($filters as $key => $filter)
        {
            if(isset($filter['values']) && count($filter['values']) > 0 && !$this->empty)
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
                }
                $filtered = true;
            }
        }

        return array(array_unique($customers), $filtered);
    }

    private function getSql(array $filter, $key_value = 'meta_value')
    {
        $sql = array();

        if(isset($filter['type']) && isset($filter['values']))
        {
            foreach ($filter['values'] as $value)
            {
                if(in_array($filter['type'], array('enum', 'string', 'float'), true))
                {
                    if($value[0] === 'prefix')
                    {
                        $sql[] = $this->db->prepare("`".$key_value."` LIKE %s", array($value[1].'%'));
                    }
                    elseif($value[0] === 'sufix')
                    {
                        $sql[] = $this->db->prepare("`".$key_value."` LIKE %s", array('%'.$value[1]));
                    }
                    elseif($value[0] === 'substring')
                    {
                        $sql[] = $this->db->prepare("`".$key_value."` LIKE %s", array('%'.$value[1].'%'));
                    }
                    else
                    {
                        $sql[] = $this->db->prepare("`".$key_value."` ".$this->getRelation($value[0])." %s", array($value[1]));
                    }
                }
                elseif($filter['type'] === "date-range")
                {
                    $sql[] = $this->db->prepare("`".$key_value."` BETWEEN %s AND %s", array($value[1], $value[2]));
                }
            }
        }

        return count($sql) > 0 ? implode(' OR ', $sql) : ' FALSE';
    }

    private function getRelation($relation)
    {
        $relation_list = array(
            'is'    => '=',
            'not'   => '!=',
            'gt'    => '>',
            'lt'    => '<'
        );

        return isset($relation_list[$relation]) ? $relation_list[$relation] : '=';
    }

    private function getCustomers(Extensions\Database\Result $result, array $customers)
    {
        $output = array();

        if($result->getNumRows() > 0)
        {
            foreach($result as $row)
            {
                $output[] = (int) $row->user_id;
            }
        }
        else
        {
            $this->empty = true;
        }

        return $this->empty ? array() : count($customers) > 0 ? array_intersect($customers, $output) : $output;
    }
}
