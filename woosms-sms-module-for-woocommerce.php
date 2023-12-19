<?php declare(strict_types=1);

/**
 * Plugin Name: BulkGate SMS Plugin for WooCommerce
 * Plugin URI: https://www.bulkgate.com/en/integrations/sms-plugin-for-woocommerce/
 * Description: Notify your customers about order status via SMS notifications.
 * Version: 3.0.6
 * Author: BulkGate
 * Author URI: https://www.bulkgate.com/
 * Requires at least: 5.7
 * PHP version 7.4
 *
 * @category BulkGate plugin for WooCommerce
 * @package  BulkGate
 * @author   Lukáš Piják <pijak@bulkgate.com>
 * @license  GNU General Public License v3.0
 * @link     https://www.bulkgate.com/
 */

use BulkGate\{Plugin\Event\Dispatcher, WooSms\Event\OrderForm, WooSms\DI\Factory, WooSms\Event\AssetDispatcher, WooSms\Event\Cron, WooSms\Event\Hook, Plugin\Settings\Settings, WooSms\Event\Redirect, WooSms\Template\Init};

if (!defined('ABSPATH')) {
	exit;
}

define('BULKGATE_PLUGIN_DIR', basename(__DIR__));

require_once ABSPATH . 'wp-admin/includes/plugin.php';

/**
 * Check if WooCommerce is installed
 */
if (is_plugin_active('woocommerce/woocommerce.php')) {

	require_once __DIR__ . '/vendor/autoload.php';

	!file_exists(__DIR__ . '/debug.php') ?: include_once __DIR__ . '/debug.php';

	function BulkgateContainerSetup(): void
	{
		Factory::setup(fn () => [
			'db' => $GLOBALS['wpdb'],
			'debug' => defined('BulkGateDebug') ? BulkGateDebug : WP_DEBUG,
			'gate_url' => defined('BulkGateDebugUrl') ? BulkGateDebugUrl : 'https://portal.bulkgate.com',
			'language' => substr(get_locale(), 0, 2) ?: 'en',
			'country' => function_exists('wc_get_base_location') ? wc_get_base_location()['country'] ?? null : null,
			'name' => html_entity_decode(get_option('blogname', 'BulkGate Plugin Store'), ENT_QUOTES),
			'url' => get_site_url(),
			'plugin_data' => get_plugin_data(__FILE__),
			'api_version' => '1.0',
			'dispatcher' => Dispatcher::Asset,
			'logger_limit' => 100
		]);
	}


	/**
	 * Init BulkGate DI container
	 */
	add_action('init', 'BulkgateContainerSetup');

	/**
	 * Init plugin
	 */
	Hook::init();
	Cron::init();
	Redirect::init();
	AssetDispatcher::init();
	OrderForm::init(get_locale());

	/**
	 * Load Back office for BulkGate SMS plugin
	 */
	is_admin() && Init::init();

	/**
	 * Register install scripts
	 */
	register_activation_hook(__FILE__, function (): void {
		BulkgateContainerSetup();
		Factory::get()->getByClass(Settings::class)->install();
	});


	/**
	 * Register uninstall scripts
	 */
	register_deactivation_hook(__FILE__, fn () => Factory::get()->getByClass(Settings::class)->uninstall());

} else {

	/**
	 * WooCommerce is not installed
	 */
	deactivate_plugins(plugin_basename(__FILE__));
}
