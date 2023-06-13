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
    'debug' => WP_DEBUG,
    'gate_url' => 'https://dev1.bulkgate.com',
	'language' => substr(get_locale(), 0, 2) ?: 'en',
	'country' => function_exists('wc_get_base_location') ? wc_get_base_location()['country'] ?? null : null,
	'name' => html_entity_decode(get_option('blogname', 'WooSMS Store'), ENT_QUOTES),
    'url' => get_site_url(),
    'plugin_data' => get_plugin_data(__DIR__ . '/../woosms-sms-module-for-woocommerce.php'),
    'api_version' => '1.0'
]);

$woo_sms_di = WooSms\DI\Factory::get();
