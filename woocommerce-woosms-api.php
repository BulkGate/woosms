<?php
use BulkGate\Extensions, BulkGate\WooSms;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

require_once __DIR__.'/../../../wp-load.php';

if(!defined('ABSPATH'))
{
    exit;
}

require_once ABSPATH.'wp-admin/includes/plugin.php';

if(is_plugin_active('woocommerce-woosms/woocommerce-woosms.php'))
{
    /** @var WooSms\DIContainer $woo_sms_di */
    require_once(__DIR__.'/woosms/src/init.php');

    try
    {
        $headers = new Extensions\Headers();
        $authenticator = new Extensions\Api\Authenticator($woo_sms_di->getSettings());
        $authenticator->authenticate((int) $headers->get('X-BulkGate-Application-ID'), $headers->get('X-BulkGate-Application-Token'));

        new WooSms\Api($headers->get('X-BulkGate-Action', ''), new Extensions\Api\Request($headers), $woo_sms_di->getDatabase(), $woo_sms_di->getSettings());
    }
    catch (Extensions\Api\ConnectionException $e)
    {
        http_response_code ($e->getCode());
        $response = new Extensions\Api\Response(array("error" => (string) $e->getCode()." ".$e->getMessage()));
        $response->send();
        exit;
    }
}
