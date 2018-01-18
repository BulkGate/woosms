<?php
namespace BulkGate\WooSms;

use BulkGate\Extensions\Json;
use BulkGate\Extensions\Escape;
use BulkGate\Extensions\IModule;
use BulkGate\Extensions\ISettings;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class WooSMS implements IModule
{
    const PRODUCT = 'ws';

    /** @var ISettings */
    public $settings;

    public function __construct(ISettings $settings)
    {
        $this->settings = $settings;
    }

    public function getUrl($path = '')
    {
        return Escape::url('http://localhost/bulkgate'.$path);
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
}