<?php

namespace BulkGate\WooSms;

/**
 * @author Lukáš Piják 2020 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use wpdb;
use BulkGate;
use BulkGate\Extensions\Strict;
use BulkGate\Extensions\Database\Result;
use BulkGate\Extensions\Database\IDatabase;

class Database extends Strict implements IDatabase
{
    /** @var wpdb */
    private $db;

    /** @var array */
    private $sql = array();


    public function __construct(wpdb $db)
    {
        $this->db = $db;
    }


    public function execute($sql)
    {
        $output = array();

        $this->sql[] = $sql;

        $result = $this->db->get_results($sql);

        if (is_array($result) && count($result))
        {
            foreach ($result as $key => $item)
            {
                $output[$key] = (object) $item;
            }
        }
        return new Result($output);
    }


    public function prepare($sql, array $array = array())
    {
        return $this->db->prepare($sql, $array);
    }


    public function lastId()
    {
        return $this->db->insert_id;
    }


    public function escape($string)
    {
        return $this->db->_escape($string);
    }


    public function prefix()
    {
        return $this->db->prefix;
    }


    public function table($table)
    {
        return $this->prefix().$table;
    }


    public function getSqlList()
    {
        return $this->sql;
    }
}
