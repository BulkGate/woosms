<?php

/**
 * @author Lukáš Piják 2020 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\WooSms;

require_once(__DIR__.'/../../extensions/src/_extension.php');
require_once(__DIR__.'/_extension.php');

/**
 * Init WooSMS
 */
add_action('init', function()
{
    /**
     * @var wpdb $wpdb
     * @var WooSms\DIContainer $woo_sms_di
     */
    global $wpdb, $woo_sms_di;

    $woo_sms_di = new WooSms\DIContainer($wpdb);
});
