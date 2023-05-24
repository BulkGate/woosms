<?php

namespace BulkGate\WooSms;

/**
 * @author Lukáš Piják 2020 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use wpdb;
use BulkGate, BulkGate\Extensions;
use BulkGate\Extensions\ServiceNotFoundException;

/**
 * @method Database getDatabase()
 * @method WooSMS getModule()
 * @method Customers getCustomers()
 */
class DIContainer extends Extensions\DIContainer
{
    /** @var wpdb */
    private $wpdb;


    public function __construct(wpdb $wpdb)
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
     * @return Extensions\IModule|WooSMS
     * @throws ServiceNotFoundException
     */
    protected function createModule()
    {
        return new WooSMS($this->getService('settings'));
    }


    /**
     * @return Extensions\ICustomers|Customers
     * @throws ServiceNotFoundException
     */
    protected function createCustomers()
    {
        return new Customers($this->getService('database'));
    }
}
