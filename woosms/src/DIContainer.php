<?php
namespace BulkGate\WooSms;

use BulkGate, BulkGate\Extensions;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 * @method Database getDatabase()
 * @method WooSMS getModule()
 * @method Customers getCustomers()
 */
class DIContainer extends Extensions\DIContainer
{
    /** @var \wpdb */
    private $wpdb;

    public function __construct(\wpdb $wpdb)
    {
        $this->wpdb = $wpdb;
    }


    /**
     * @return Database
     */
    protected function createDatabase()
    {
        return new Database($this->wpdb);
    }


    /**
     * @return WooSMS
     */
    protected function createModule()
    {
        return new WooSMS($this->getService('settings'));
    }


    /**
     * @return Customers
     */
    protected function createCustomers()
    {
        return new Customers($this->getService('database'));
    }
}
