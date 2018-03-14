<?php
namespace BulkGate\WooSms;

use BulkGate;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class Database extends BulkGate\Extensions\SmartObject implements BulkGate\Extensions\Database\IDatabase
{
    /** @var \wpdb */
    private $db;

    private $sql = array();

    public function __construct(\wpdb $db)
    {
        $this->db = $db;
    }

    public function execute($sql)
    {
        $output = array();

        $this->sql[] = $sql;

        $result = $this->db->get_results($sql);

        if(is_array($result) && count($result))
        {
            foreach ($result as $key => $item)
            {
                $output[$key] = (object) $item;
            }
        }
        return new BulkGate\Extensions\Database\Result($output);
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
        return $this->escape($this->db->prefix);
    }

    public function getSqlList()
    {
        return $this->sql;
    }
}
