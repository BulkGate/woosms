<?php
/*
  Plugin Name: WooCommerce WooSMS - BulkGate.com module
  Plugin URI: http://www.woo-sms.net/
  Description: Woo SMS is a comprehensive and powerful module that enables you to send SMSs to your customers or administrators during various events in your WooCommerce store. <a href="http://www.woothemes.com/woocommerce/"><strong>| This module needs WooCommerce module |</strong></a>
  Version: 2.00 alfa
  Author: TOPefekt s.r.o. - BulkGate team
  Author URI: http://www.bulkgate.com/
 */

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Extensions, BulkGate\WooSms;

if (!defined('ABSPATH'))
{
    exit;
}

if(file_exists(__DIR__.'/../../../Tracy/tracy.php'))
{
    error_reporting(1);
    require_once __DIR__.'/../../../Tracy/tracy.php';
    Tracy\Debugger::$strictMode = true;
    Tracy\Debugger::$maxDepth = 10;
    Tracy\Debugger::enable();
}

/**
 * Enable SMS demo feature (Disable save settings in profile)
 */
//define("SMS_DEMO", true);

require_once(ABSPATH . 'wp-admin/includes/plugin.php');

/**
 * Check if woocommerce is installed
 */
if (is_plugin_active('woocommerce/woocommerce.php'))
{
    /**
     * Init woosms
     */
    require_once(__DIR__.'/woosms/init.php');


    /**
     * Connect woosms actions for customers and admin SMS
     */
    add_action("woocommerce_order_status_changed", "woosms_hook_changeOrderStatusHook");
    add_action("woocommerce_checkout_order_processed", "woosms_hook_actionValidateOrder");
    add_action("woocommerce_created_customer", "woosms_hook_customerAddHook");
    add_action("woocommerce_low_stock", "woosms_hook_productOutOfStockHook");
    add_action("woocommerce_no_stock", "woosms_hook_productOutOfStockHook");
    add_action("woocommerce_product_on_backorder", "woosms_hook_productOnBackOrder");

    /**
     * Load backend for woosms
     */
    if (is_admin())
    {
        require("woocommerce-woosms-admin.php");
    }

    /*
     * Woosms actions
     */
    function woosms_hook_actionValidateOrder($order_id)
    {
        woosms_run_hook('order_new', new Extensions\Hook\Variables(array(
            'order_id' => $order_id
        )));
    }

    function woosms_hook_customerAddHook($customer_id)
    {
        woosms_run_hook('customer_new', new Extensions\Hook\Variables(array(
            'customer_id' => $customer_id,
            'shop_id' => 0
        )));
    }

    function woosms_hook_changeOrderStatusHook($order_id)
    {
        $order = new WC_Order($order_id);

        woosms_run_hook('order_status_change_'.$order->post_status, new Extensions\Hook\Variables(array(
            'order_status_id' => woosms_isset($order, 'post_status'),
            'order_id' => $order_id
        )));
    }

    function woosms_hook_productOutOfStockHook($data)
    {
        woosms_run_hook('product_out_of_stock', new Extensions\Hook\Variables(array(
            'product_id' => woosms_isset($data, 'id', 0),
            'product_quantity' => woosms_isset(get_post_meta($data->id, "_stock"), 0, 0),
            'product_name' => woosms_isset($data->post, 'post_title', '-'),
            'product_ref' => woosms_isset($data->post, 'post_name', '-'),
        )));
    }

    function woosms_hook_productOnBackOrder($data)
    {
        $product = woosms_isset($data, 'product');

        woosms_run_hook('product_on_back_order', new Extensions\Hook\Variables(array(
            'product_id' => woosms_isset($product, 'id', 0),
            'product_quantity' => woosms_isset($data, 'quantity', 0),
            'product_name' => woosms_isset($product->post, 'post_title', '-'),
            'product_ref' => woosms_isset($product->post, 'post_name', '-'),
            'order_id' => woosms_isset($data, 'order_id', false)
        )));
    }

    /**
     * Woosms install script
     */
    function woosms_activate()
    {
        /** @var Extensions\Settings $woo_sms_settings */
        global $woo_sms_settings;

        $woo_sms_settings->install();
    }

    /**
     * Woosms uninstall script
     */
    function woosms_deactivate()
    {
        /**
         * @var WooSms\Database $woo_sms_database
         * @var \BulkGate\Extensions\Settings $woo_sms_settings
         */
        global $woo_sms_database, $woo_sms_settings;

        if ($woo_sms_settings->load('main:delete_db'))
        {
            $woo_sms_database->execute("DROP TABLE IF EXISTS `" . $woo_sms_database->prefix() . "bulkgate_module`");
        }
    }

    function woosms_synchronize($now = false)
    {
        /**
         * @var Extensions\IModule $woo_sms_module
         * @var Extensions\Synchronize $woo_sms_synchronize
         */
        global $woo_sms_module, $woo_sms_synchronize;

        $now = $now || $woo_sms_module->statusLoad() || $woo_sms_module->languageLoad() || $woo_sms_module->storeLoad();

        try
        {
            $woo_sms_synchronize->run($woo_sms_module->getUrl('/module/settings/synchronize'), $now);

            return true;
        }
        catch (Extensions\IO\InvalidResultException $e)
        {
            return false;
        }
    }

    /**
     * Register install & uninstall scripts
     */
    register_activation_hook(__FILE__, 'woosms_activate');
    register_deactivation_hook(__FILE__, 'woosms_deactivate');

}
else
{
    /**
     * Woocommerce is not installed
     */
    deactivate_plugins(plugin_basename(__FILE__));
}