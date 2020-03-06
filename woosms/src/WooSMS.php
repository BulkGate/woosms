<?php

namespace BulkGate\WooSms;

/**
 * @author Lukáš Piják 2020 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Extensions\Json;
use BulkGate\Extensions\IModule;
use BulkGate\Extensions\ISettings;
use BulkGate\Extensions\Strict;

class WooSMS extends Strict implements IModule
{
    /** @var string */
    const PRODUCT = 'ws';

    private $info = array(
        'store' => 'WooCommerce',
        'store_version' => '2.2.x - 3.2.x',
        'name' => 'WooSMS',
        'url' => 'http://www.woo-sms.net',
        'developer' => 'TOPefekt s.r.o.',
        'developer_url' => 'http://www.topefekt.com/',
        'description' => 'WooSMS module extends your WooCommerce store capabilities and creates new opportunities for your business. You can promote your products and sales via personalized bulk SMS. Make your customers happy by notifying them about order status change via SMS notifications. Receive an SMS whenever a new order is placed, a product is out of stock, and much more.',
    );

    /** @var ISettings */
    public $settings;

    /** @var array */
    private $plugin_data = array();


    public function __construct(ISettings $settings)
    {
        $this->settings = $settings;
    }


    public function getUrl($path = '')
    {
        if (defined('BULKGATE_DEBUG'))
        {
            return Escape::url(BULKGATE_DEBUG.$path);
        }
        else
        {
            return Escape::url('https://portal.bulkgate.com'.$path);
        }
    }


    public function statusLoad()
    {
        $status_list = (array) $this->settings->load(':order_status_list', null);
        $actual = (array) wc_get_order_statuses();

        if ($status_list !== $actual)
        {
            $this->settings->set(':order_status_list', Json::encode($actual), array('type' => 'json'));
            return true;
        }
        return false;
    }


    public function languageLoad()
    {
        if ((bool) $this->settings->load('main:language_mutation'))
        {
            $languages = (array) $this->settings->load(':languages', null);
            $actual = (array) woosms_load_languages();

            if ($languages !== $actual)
            {
                $this->settings->set(':languages', Json::encode($actual), array('type' => 'json'));
                return true;
            }
            return false;
        }
        else
        {
            $default_language = array('default' => 'Default');

            $languages = (array) $this->settings->load(':languages', null);

            if ($languages !== $default_language)
            {
                $this->settings->set(':languages', Json::encode($default_language), array('type' => 'json'));
                return true;
            }
            return false;
        }
    }


    public function storeLoad()
    {
        $stores = (array) $this->settings->load(':stores', null);
        $actual = array(0 => woosms_get_shop_name());

        if ($stores !== $actual)
        {
            $this->settings->set(':stores', Json::encode($actual), array('type' => 'json'));
            return true;
        }
        return false;
    }


    public function product()
    {
        return self::PRODUCT;
    }


    public function url()
    {
        return get_site_url();
    }


    public function info($key = null)
    {
        if (empty($this->plugin_data))
        {
            $plugin_data = array_change_key_case(get_plugin_data(__DIR__.'/../../woosms-sms-module-for-woocommerce.php'));

            $this->plugin_data = array_merge(
                array(
                    'version' => isset($plugin_data['version']) ? $plugin_data['version'] : 'unknown',
                    'application_id' => $this->settings->load('static:application_id', -1),
                    'application_product' => $this->product(),
                    'delete_db' => $this->settings->load('main:delete_db', 0),
                    'language_mutation' => $this->settings->load('main:language_mutation', 0)
                ),
                $this->info
            );
        }
        if ($key === null)
        {
            return $this->plugin_data;
        }
        return isset($this->plugin_data[$key]) ? $this->plugin_data[$key] : null;
    }
}
