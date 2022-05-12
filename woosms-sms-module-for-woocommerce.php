<?php
/**
 * Plugin Name: BulkGate SMS Plugin for WooCommerce
 * Plugin URI: https://www.bulkgate.com/en/integrations/sms-plugin-for-woocommerce/
 * Description: Notify your customers about order status via SMS notifications.
 * Version: 2.1.0
 * Author: BulkGate
 * Author URI: https://www.bulkgate.com/
 * Requires at least: 5.7
 * PHP version 7.3
 *
 * @category WooSMS
 * @package  BulkGate
 * @author   Lukáš Piják <pijak@bulkgate.com>
 * @license  GNU General Public License v3.0
 * @link     https://www.bulkgate.com/
 */

use BulkGate\Extensions, BulkGate\WooSms;

if (!defined('ABSPATH')) {
    exit;
}

define('WOOSMS_DIR', basename(__DIR__));

if (file_exists(__DIR__.'/extensions/src/debug.php')) {
    include_once __DIR__ . '/extensions/src/debug.php';
}

require_once ABSPATH . 'wp-admin/includes/plugin.php';

/**
 * Check if WooCommerce is installed
 */
if (is_plugin_active('woocommerce/woocommerce.php')) {

    /**
     * Init WooSMS
     */
    include_once __DIR__ . '/woosms/src/init.php';


    /**
     * Return plugin version
     *
     * @return mixed|string
     */
    function Woosms_Package_version()
    {
        $plugin_data = get_plugin_data(__FILE__);
        return isset($plugin_data['Version']) ? $plugin_data['Version'] : 'unknown';
    }

    /**
     * Connect woosms actions for customers and admin SMS
     */
    add_action('woocommerce_order_status_changed', 'Woosms_Hook_changeOrderStatusHook');
    add_action('woocommerce_checkout_order_processed', 'Woosms_Hook_actionValidateOrder');
    add_action('woocommerce_created_customer', 'Woosms_Hook_customerAddHook', 100, 3);
    add_action('woocommerce_low_stock', 'Woosms_Hook_productOutOfStockHook');
    add_action('woocommerce_no_stock', 'Woosms_Hook_productOutOfStockHook');
    add_action('woocommerce_payment_complete', 'Woosms_Hook_paymentComplete', 100, 1);
    add_action('woocommerce_product_on_backorder', 'Woosms_Hook_productOnBackOrder');
    add_action('woosms_send_sms', 'Woosms_Hook_sendSms', 100, 4);


    /**
     * Load backend for woosms
     */
    if (is_admin()) {
        include __DIR__ . '/woosms-sms-module-for-woocommerce-admin.php';
    }


    /**
     * New order hook
     *
     * @param int $order_id Order identification
     *
     * @return void
     */
    function Woosms_Hook_actionValidateOrder($order_id)
    {
        woosms_run_hook(
            'order_new',
            new Extensions\Hook\Variables(
                array(
                    'order_id' => $order_id,
                    'lang_id' => woosms_get_post_lang($order_id)
                )
            )
        );
    }


    /**
     * New customer hook
     *
     * @param int            $customer_id Customer identification
     * @param array|stdClass $data        Hook Data
     *
     * @return void
     */
    function Woosms_Hook_customerAddHook($customer_id, $data)
    {
        woosms_run_hook(
            'customer_new', new Extensions\Hook\Variables(
                array(
                'customer_id' => $customer_id,
                'password' => woosms_isset($data, 'user_pass', '-'),
                'shop_id' => 0
                )
            )
        );
    }


    /**
     * Change order status hook
     *
     * @param int $order_id Order identification
     *
     * @return void
     */
    function Woosms_Hook_changeOrderStatusHook($order_id)
    {
        $run_hook = true;
        $order = new WC_Order($order_id);

        if (has_filter('run_woosms_hook_changeOrderStatusHook')) {

            $run_hook = apply_filters('run_woosms_hook_changeOrderStatusHook', $order);
        }
      
        if ($run_hook) {

            woosms_run_hook(
                'order_status_change_wc-'.$order->get_status(), new Extensions\Hook\Variables(
                    array(
                    'order_status_id' => $order->get_status(),
                    'order_id' => $order_id,
                    'lang_id' => woosms_get_post_lang($order_id)
                    )
                )
            );
        }
    }


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
                array(
                'product_id' => woosms_isset($data, 'id', 0),
                'product_quantity' => woosms_isset(get_post_meta($data->id, '_stock'), 0, 0),
                'product_name' => woosms_isset($data->post, 'post_title', '-'),
                'product_ref' => woosms_isset($data->post, 'post_name', '-'),
                )
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
                array(
                'order_id' => $order_id,
                'lang_id' => woosms_get_post_lang($order_id)
                )
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
    function Woosms_Hook_sendSms($number, $template, array $variables = array(), array $settings = array())
    {
        /**
         * DI container
         *
         * @var WooSms\DIContainer $woo_sms_di DI container
        */
        global $woo_sms_di;

        $woo_sms_di->getConnection()->run(
            new BulkGate\Extensions\IO\Request(
                $woo_sms_di->getModule()->getUrl('/module/hook/custom'),
                array(
                    'number' => $number,
                    'template' => $template,
                    'variables' => $variables,
                    'settings' => $settings
                ),
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
                array(
                'product_id' => woosms_isset($product, 'id', 0),
                'product_quantity' => woosms_isset($data, 'quantity', 0),
                'product_name' => woosms_isset($product->post, 'post_title', '-'),
                'product_ref' => woosms_isset($product->post, 'post_name', '-'),
                'order_id' => woosms_isset($data, 'order_id', false),
                'lang_id' => woosms_get_post_lang(woosms_isset($data, 'order_id', false))
                )
            )
        );
    }


    /**
     * Synchronize plugin settings with BulkGate portal
     *
     * @param bool $now Instant synchronize
     *
     * @return bool
     */
    function Woosms_synchronize($now = false)
    {
        /**
         * DI Container
         *
         * @var WooSms\DIContainer $woo_sms_di
        */
        global $woo_sms_di;

        if ($woo_sms_di->getSettings()->load('static:application_token')) {

            $module = $woo_sms_di->getModule();

            $woo_sms_di->getSettings()->set('main:version', Woosms_Package_version());

            $status = $module->statusLoad(); $language = $module->languageLoad(); $store = $module->storeLoad();

            $now = $now || $status || $language || $store;

            try
            {
                $woo_sms_di->getSynchronize()->run($module->getUrl('/module/settings/synchronize'), $now);

                return true;
            }
            catch (Extensions\IO\InvalidResultException $e)
            {
            }
        }
        return false;
    }


    /**
     * Register install scripts
     */
    register_activation_hook(
        __FILE__, function () {
            /**
             * Database Connection
             *
             * @var wpdb $wpdb Connection DI container
            */
            global $wpdb;

            $woo_sms_di = new WooSms\DIContainer($wpdb);

            $woo_sms_di->getSettings()->install();
        }
    );


    /**
     * Register uninstall scripts
     */
    register_deactivation_hook(
        __FILE__, function () {
            /**
             * DI Container
             *
             * @var WooSms\DIContainer $woo_sms_di
             */
            global $woo_sms_di;

            $woo_sms_di->getSettings()->uninstall();
        }
    );

} else {

    /**
     * WooCommerce is not installed
     */
    deactivate_plugins(plugin_basename(__FILE__));
}
