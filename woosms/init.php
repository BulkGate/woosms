<?php
use BulkGate\Extensions, BulkGate\WooSms;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
define("WOOSMS_DIR", basename(__DIR__));

require_once(__DIR__.'/../extensions/_extension.php');
require_once(__DIR__.'/../woosms/_extension.php');

/**
 * Init woosms
 */
add_action('init', function()
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
    global $wpdb, $woo_sms_module, $woo_sms_database, $woo_sms_settings, $woo_sms_connection, $woo_sms_translator, $woo_sms_synchronize;

    $woo_sms_database = new WooSms\Database($wpdb);
    $woo_sms_settings = new Extensions\Settings($woo_sms_database);
    $woo_sms_module = new WooSms\WooSMS($woo_sms_settings);
    $woo_sms_connection_factory = new Extensions\IO\ConnectionFactory($woo_sms_settings);
    $woo_sms_connection = $woo_sms_connection_factory->create($woo_sms_module->url(), $woo_sms_module->product());
    $woo_sms_translator = new Extensions\Translator($woo_sms_settings);
    $woo_sms_synchronize = new Extensions\Synchronize($woo_sms_settings, $woo_sms_connection);

    setcookie('language_iso', $woo_sms_settings->load(':lang', 'en'), 0, '/');

    woosms_synchronize();
});
