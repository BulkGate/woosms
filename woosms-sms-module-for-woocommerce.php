<?php
/*
  Plugin Name: WooSMS - SMS module for WooCommerce
  Plugin URI: http://www.woo-sms.net/
  Description: Extend your WooCommerce store capabilities. Send personalized bulk SMS messages. Notify your customers about order status via customer SMS notifications. Receive order updates via Admin SMS notifications.
  Version: 2.0.26
  Author: BulkGate SMS gateway
  Author URI: https://www.bulkgate.com/
*/

/**
 * @author Lukáš Piják 2020 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Extensions, BulkGate\WooSms;

if (!defined('ABSPATH'))
{
    exit;
}

define('WOOSMS_DIR', basename(__DIR__));

if (file_exists(__DIR__.'/extensions/src/debug.php'))
{
    require_once __DIR__.'/extensions/src/debug.php';
}

require_once(ABSPATH . 'wp-admin/includes/plugin.php');

/**
 * Check if WooCommerce is installed
 */
if (is_plugin_active('woocommerce/woocommerce.php'))
{
    /**
     * Init WooSMS
     */
    require_once(__DIR__.'/woosms/src/init.php');


    function woosms_version()
    {
        $plugin_data = get_plugin_data(__FILE__);
        return isset($plugin_data['Version']) ? $plugin_data['Version'] : 'unknown';
    }

    /**
     * Connect woosms actions for customers and admin SMS
     */
    add_action('woocommerce_order_status_changed', 'woosms_hook_changeOrderStatusHook');
    add_action('woocommerce_checkout_order_processed', 'woosms_hook_actionValidateOrder');
    add_action('woocommerce_created_customer', 'woosms_hook_customerAddHook', 100, 3);
    add_action('woocommerce_low_stock', 'woosms_hook_productOutOfStockHook');
    add_action('woocommerce_no_stock', 'woosms_hook_productOutOfStockHook');
    add_action('woocommerce_payment_complete', 'woosms_hook_paymentComplete', 100, 1);
    add_action('woocommerce_product_on_backorder', 'woosms_hook_productOnBackOrder');
    add_action('woosms_send_sms', 'woosms_hook_sendSms', 100, 4);


    /**
     * Load backend for woosms
     */
    if (is_admin())
    {
        require(__DIR__.'/woosms-sms-module-for-woocommerce-admin.php');
    }


    /*
     * Woosms actions
     */
    function woosms_hook_actionValidateOrder($order_id)
    {
        woosms_run_hook('order_new', new Extensions\Hook\Variables(array(
            'order_id' => $order_id,
            'lang_id' => woosms_get_post_lang($order_id)
        )));
    }


    function woosms_hook_customerAddHook($customer_id, $data)
    {
        woosms_run_hook('customer_new', new Extensions\Hook\Variables(array(
            'customer_id' => $customer_id,
            'password' => woosms_isset($data, 'user_pass', '-'),
            'shop_id' => 0
        )));
    }


    function woosms_hook_changeOrderStatusHook($order_id)
    {
        $run_hook = true;
        $order = new WC_Order($order_id);

        if (has_filter('run_woosms_hook_changeOrderStatusHook'))
        {
            $run_hook = apply_filters('run_woosms_hook_changeOrderStatusHook', $order);
        }
      
        if ($run_hook)
        {
            woosms_run_hook('order_status_change_wc-'.$order->get_status(), new Extensions\Hook\Variables(array(
                'order_status_id' => $order->get_status(),
                'order_id' => $order_id,
                'lang_id' => woosms_get_post_lang($order_id)
            )));
        }
    }


    function woosms_hook_productOutOfStockHook($data)
    {
        woosms_run_hook('product_out_of_stock', new Extensions\Hook\Variables(array(
            'product_id' => woosms_isset($data, 'id', 0),
            'product_quantity' => woosms_isset(get_post_meta($data->id, '_stock'), 0, 0),
            'product_name' => woosms_isset($data->post, 'post_title', '-'),
            'product_ref' => woosms_isset($data->post, 'post_name', '-'),
        )));
    }


    function woosms_hook_paymentComplete($order_id)
    {
        woosms_run_hook('order_payment_complete', new Extensions\Hook\Variables(array(
            'order_id' => $order_id,
            'lang_id' => woosms_get_post_lang($order_id)
        )));
    }


    function woosms_hook_sendSms($number, $template, array $variables = array(), array $settings = array())
    {
        /** @var WooSms\DIContainer $woo_sms_di */
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
            true, 5));
    }


    function woosms_hook_productOnBackOrder($data)
    {
        $product = woosms_isset($data, 'product');

        woosms_run_hook('product_on_back_order', new Extensions\Hook\Variables(array(
            'product_id' => woosms_isset($product, 'id', 0),
            'product_quantity' => woosms_isset($data, 'quantity', 0),
            'product_name' => woosms_isset($product->post, 'post_title', '-'),
            'product_ref' => woosms_isset($product->post, 'post_name', '-'),
            'order_id' => woosms_isset($data, 'order_id', false),
            'lang_id' => woosms_get_post_lang(woosms_isset($data, 'order_id', false))
        )));
    }


    function woosms_synchronize($now = false)
    {
        /** @var WooSms\DIContainer $woo_sms_di */
        global $woo_sms_di;

        if ($woo_sms_di->getSettings()->load('static:application_token'))
        {
            $module = $woo_sms_di->getModule();

            $woo_sms_di->getSettings()->set('main:version', woosms_version());

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
    register_activation_hook(__FILE__, function ()
    {
        /** @var wpdb $wpdb */
        global $wpdb;

        $woo_sms_di = new WooSms\DIContainer($wpdb);

        $woo_sms_di->getSettings()->install();
    });


    /**
     * Register uninstall scripts
     */
    register_deactivation_hook(__FILE__, function ()
    {
        /** @var WooSms\DIContainer $woo_sms_di */
        global $woo_sms_di;

        $woo_sms_di->getSettings()->uninstall();
    });
}
else
{
    /**
     * WooCommerce is not installed
     */
    deactivate_plugins(plugin_basename(__FILE__));
}
