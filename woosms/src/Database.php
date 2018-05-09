<?php
namespace BulkGate\WooSms;

use BulkGate;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class Database extends BulkGate\Extensions\Strict implements BulkGate\Extensions\Database\IDatabase
{
    /** @var \wpdb */
    private $db;

    private $sql = array();

    public function __construct(\wpdb $db)
    {
        $this->db = $db;
    }

    public function execute($sql, array $params = array())
    {
        $output = array();

        $this->sql[] = $sql;

        $result = $this->db->get_results($this->db->prepare($sql, $params));

        if(is_array($result) && count($result))
        {
            foreach ($result as $key => $item)
            {
                $output[$key] = (object) $item;
            }
        }
        return new BulkGate\Extensions\Database\Result($output);
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

    public function getSqlList()
    {
        return $this->sql;
    }
}
