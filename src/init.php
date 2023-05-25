<?php

use BulkGate\WooSms;

/**
 * @author Lukáš Piják 2020 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

require_once __DIR__ . '/helpers.php';

global $wpdb, $woo_sms_di;

/**
 * @var wpdb $wpdb
 */
WooSms\DI\Factory::setup(fn () => [
    'db' => $wpdb,
    'debug' => true,//WP_DEBUG,
    'gate_url' => 'http://192.168.80.1:81',
    'url' => get_site_url(),
    'plugin_data' => get_plugin_data(__FILE__),
    'api_version' => '1.0'
]);

$woo_sms_di = WooSms\DI\Factory::get();
