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
    /**
     * @var \wpdb $wpdb
     * @var Extensions\IModule $woo_sms_module
     * @var Extensions\Database\IDatabase $woo_sms_database
     * @var Extensions\ISettings $woo_sms_settings
     * @var Extensions\IO\IConnection $woo_sms_connection
     * @var Extensions\Translator $woo_sms_translator
     * @var Extensions\Synchronize $woo_sms_synchronize
     */
    require_once(__DIR__.'/woosms/src/init.php');

    try
    {
        $headers = new Extensions\Headers();
        $authenticator = new Extensions\Api\Authenticator($woo_sms_settings);
        $authenticator->authenticate((int) $headers->get('X-BulkGate-Application-ID'), $headers->get('X-BulkGate-Application-Token'));

        new WooSms\Api($headers->get('X-BulkGate-Action', ''), new Extensions\Api\Request($headers), $woo_sms_database, $woo_sms_settings);
    }
    catch (Extensions\Api\ConnectionException $e)
    {
        http_response_code ($e->getCode());
        $response = new Extensions\Api\Response(array("error" => (string) $e->getCode()." ".$e->getMessage()));
        $response->send();
        exit;
    }
}
