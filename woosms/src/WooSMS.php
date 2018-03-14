<?php
namespace BulkGate\WooSms;

use BulkGate\Extensions\Json;
use BulkGate\Extensions\Escape;
use BulkGate\Extensions\IModule;
use BulkGate\Extensions\ISettings;
use BulkGate\Extensions\SmartObject;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class WooSMS extends SmartObject implements IModule
{
    const PRODUCT = 'ws';

    private $info = array(
        'store' => 'WooCommerce',
        'store_version' => '2.2.x - 3.2.x',
        'name' => 'WooSMS',
        'url' => 'http://www.woo-sms.net',
        'developer' => 'TOPefekt s.r.o.',
        'developer_url' => 'http://www.topefekt.com/',
        'description' => 'WooSMS is a comprehensive and powerful module that enables you to send SMSs to your customers or administrators during various events in your WooCommerce store. Improve customer service & notify customers via SMS to establish greater levels of trust. Deepen the relationship with your customers and build a stronger customer loyalty with the help of SMS marketing. Loyal customers tend to buy more & more regularly. And they will frequently recommend your e-shop to others. More customers = higher sales...! Give administrators the advantage of immediate access to information via SMS messages, whether they are at a computer or not. With Woo SMS module you can send SMSs worldwide. The price of the SMS depends on the recipient country, selected sender type and the payment amount. Our prices are among the lowest in the market.',
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
        if(defined('BULKGATE_DEBUG'))
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

        if($status_list !== $actual)
        {
            $this->settings->set(':order_status_list', Json::encode($actual), array('type' => 'json'));
            return true;
        }
        return false;
    }

    public function languageLoad()
    {
        $languages = (array) $this->settings->load(':languages', null);
        $actual = (array) woosms_load_languages();

        if($languages !== $actual)
        {
            $this->settings->set(':languages', Json::encode($actual), array('type' => 'json'));
            return true;
        }
        return false;
    }

    public function storeLoad()
    {
        $stores = (array) $this->settings->load(':stores', null);
        $actual = array(0 => get_option('blogname', 'WooSMS Store'));

        if($stores !== $actual)
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
        if(empty($this->plugin_data))
        {
            $plugin_data = array_change_key_case(get_plugin_data(__DIR__.'/../../woocommerce-woosms.php'));

            $this->plugin_data = array_merge(
                array(
                    'version' => isset($plugin_data['version']) ? $plugin_data['version'] : 'unknown',
                    'application_id' => $this->settings->load('static:application_id', -1),
                    'application_product' => $this->product(),
                    'delete_db' => $this->settings->load('main:delete_db', 0)
                ),
                $this->info
            );
        }
        if($key === null)
        {
            return $this->plugin_data;
        }
        return isset($this->plugin_data[$key]) ? $this->plugin_data[$key] : null;
    }
}
