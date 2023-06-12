<?php declare(strict_types=1);
/**
 * Plugin Name: BulkGate SMS Plugin for WooCommerce
 * Plugin URI: https://www.bulkgate.com/en/integrations/sms-plugin-for-woocommerce/
 * Description: Notify your customers about order status via SMS notifications.
 * Version: 3.0.0 alfa
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

use BulkGate\WooSms\DI\Factory;
use BulkGate\WooSms\Event\Helpers;
use BulkGate\Plugin\{DI\MissingParameterException, DI\MissingServiceException, Eshop, DI\Container as DIContainer, Event\Asynchronous, Event\Dispatcher, Event\Variables, Settings\Settings};

if (!defined('ABSPATH')) {
    exit;
}

define('WOOSMS_DIR', basename(__DIR__));

require_once ABSPATH . 'wp-admin/includes/plugin.php';

/**
 * Check if WooCommerce is installed
 */
if (is_plugin_active('woocommerce/woocommerce.php')) {

    /**
     * Init WooSMS
     */
    include_once __DIR__ . '/vendor/autoload.php';
    include_once __DIR__ . '/src/init.php';

    /**
     * Connect BulkGate actions for customers and admin SMS
     */
    add_action('woocommerce_order_status_changed', Helpers::dispatch('order_status_change', function (
		Dispatcher $dispatcher,
		int $order_id,
		string $from,
		string $to,
		object $order
    ): void
    {
	    $run_hook = true;

	    if (has_filter('run_woosms_hook_changeOrderStatusHook')) // BC
	    {
		    $run_hook = apply_filters('run_woosms_hook_changeOrderStatusHook', $order);
	    }

	    if ($run_hook)
	    {
		    $dispatcher->dispatch('order', 'change_status', $v = new Variables([
			    'order_id' => $order_id,
			    'order_status_id' => $to,
			    'order_status_id_from' => $from,
		    ]), ['order' => $order]);
		    dump($v);die;
	    }
    }), 100, 4);


    add_action('woocommerce_checkout_order_processed', Helpers::dispatch('order_new', fn (Dispatcher $dispatcher, int $order_id, array $posted_data, WC_Order $order) =>
		$dispatcher->dispatch('order', 'new', new Variables([
		    'order_id' => $order_id,
		]), ['order' => $order])
	), 100, 3);


    add_action('woocommerce_created_customer', Helpers::dispatch('customer_new', fn (Dispatcher $dispatcher, int $customer_id, array $data, string $password_generated) =>
        $dispatcher->dispatch('customer', 'new', new Variables([
		    'customer_id' => $customer_id,
		    'password' => $password_generated,
        ]))
    ), 100, 3);


    add_action('woocommerce_low_stock', 'Woosms_Hook_productOutOfStockHook');
    add_action('woocommerce_no_stock', 'Woosms_Hook_productOutOfStockHook');
    add_action('woocommerce_payment_complete', 'Woosms_Hook_paymentComplete', 100, 1);
    add_action('woocommerce_product_on_backorder', 'Woosms_Hook_productOnBackOrder');
    add_action('woosms_send_sms', 'Woosms_Hook_sendSms', 100, 4);

	/**
	 * Load Back office for BulkGate SMS plugin
	 */
	if (is_admin())
	{
		include __DIR__ . '/woosms-sms-module-for-woocommerce-admin.php';
	}

	add_filter( 'cron_schedules', function (array $schedules): array
	{
		$schedules['bulkgate_send_interval'] ??= [
			'interval' => 60,
			'display' => __('BulkGate Sending Interval')
		];

		$schedules['bulkgate_synchronize_interval'] ??= [
			'interval' => 300,
			'display' => __('BulkGate Synchronize Interval')
		];

		return $schedules;
	});


	add_action( 'init', function (): void
	{
	    if (!wp_next_scheduled('bulkgate_sending'))
		{
		    wp_schedule_event(time(), 'bulkgate_send_interval', 'bulkgate_sending');
	    }

		if (!wp_next_scheduled('bulkgate_synchronize'))
		{
			wp_schedule_event(time(), 'bulkgate_synchronize_interval', 'bulkgate_synchronize');
		}
	});


	add_action('bulkgate_sending', $f = function (): void
	{
		$di = Factory::get();

		/**
		 * @var Settings $settings
		 */
		$settings = $di->getByClass(Settings::class);

		if ($settings->load('main:dispatcher') === 'cron')
		{
			$settings->set('main:cron-run-before', date('Y-m-d H:i:s'), ['type' => 'string']);
			/**
			 * @var Asynchronous $asynchronous
			 */
			$asynchronous = $di->getByClass(Asynchronous::class);

			$asynchronous->run((int) ($settings->load('main:cron-limit') ?? 10));

			$settings->set('main:cron-run', date('Y-m-d H:i:s'), ['type' => 'string']);
		}
	});


	add_action('bulkgate_synchronize', fn () => Factory::get()->getByClass(Eshop\EshopSynchronizer::class)->run());








    /**
     * Product out of stock hook
     *
     * @param stdClass $data Hook data
     *
     * @return void
     */
    function Woosms_Hook_productOutOfStockHook($data)
    {
        woosms_run_hook(
            'product_out_of_stock', new Extensions\Hook\Variables(
                [
                    'product_id' => woosms_isset($data, 'id', 0),
                    'product_quantity' => woosms_isset(get_post_meta($data->id, '_stock'), 0, 0),
                    'product_name' => woosms_isset($data->post, 'post_title', '-'),
                    'product_ref' => woosms_isset($data->post, 'post_name', '-')
                ]
            )
        );
    }


    /**
     * Payment complete hook
     *
     * @param int $order_id Order identification
     *
     * @return void
     */
    function Woosms_Hook_paymentComplete($order_id)
    {
        woosms_run_hook(
            'order_payment_complete', new Extensions\Hook\Variables(
                [
                    'order_id' => $order_id,
                    'lang_id' => woosms_get_post_lang($order_id)
                ]
            )
        );
    }


    /**
     * Custom send sms hook
     *
     * @param string $number    Phone Number
     * @param string $template  Template of SMS
     * @param array  $variables Variables for template
     * @param array  $settings  Additional settings
     *
     * @return void
     */
    function Woosms_Hook_sendSms($number, $template, array $variables = [], array $settings = [])
    {
        /**
         * DI container
         *
         * @var DIContainer $woo_sms_di DI container
        */
        global $woo_sms_di;

        $woo_sms_di->getConnection()->run(
            new BulkGate\Extensions\IO\Request(
                $woo_sms_di->getModule()->getUrl('/module/hook/custom'),
                [
                    'number' => $number,
                    'template' => $template,
                    'variables' => $variables,
                    'settings' => $settings
                ],
                true, 5
            )
        );
    }


    /**
     * Back order hook
     *
     * @param stdClass $data Hook data
     *
     * @return void
     */
    function Woosms_Hook_productOnBackOrder($data)
    {
        $product = woosms_isset($data, 'product');

        woosms_run_hook(
            'product_on_back_order', new Extensions\Hook\Variables(
                [
                    'product_id' => woosms_isset($product, 'id', 0),
                    'product_quantity' => woosms_isset($data, 'quantity', 0),
                    'product_name' => woosms_isset($product->post, 'post_title', '-'),
                    'product_ref' => woosms_isset($product->post, 'post_name', '-'),
                    'order_id' => woosms_isset($data, 'order_id', false),
                    'lang_id' => woosms_get_post_lang(woosms_isset($data, 'order_id', false))
                ]
            )
        );
    }


    /**
     * Register install scripts
     */
    register_activation_hook(__FILE__, fn () => Factory::get()->getByClass(Settings::class)->install());


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
