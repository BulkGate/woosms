<?php
use BulkGate\Extensions, BulkGate\WooSms;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

require_once(__DIR__.'/../../extensions/src/_extension.php');
require_once(__DIR__.'/_extension.php');

/**
 * Init woosms
 */
add_action('init', function()
{
    /**
     * @var \wpdb $wpdb
     * @var WooSms\DIContainer $woo_sms_di
     */
    global $wpdb, $woo_sms_di;

    $woo_sms_di = new WooSms\DIContainer($wpdb);

    woosms_synchronize();
});
