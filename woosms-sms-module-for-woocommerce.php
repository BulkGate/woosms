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

use BulkGate\WooSms\{DI\Factory, Event\Cron, Event\Hook};
use BulkGate\Plugin\{Event\Asynchronous, Event\Dispatcher, Settings\Settings};

if (!defined('ABSPATH')) {
    exit;
}

define('BULKGATE_PLUGIN_DIR', basename(__DIR__));

require_once ABSPATH . 'wp-admin/includes/plugin.php';

/**
 * Check if WooCommerce is installed
 */
if (is_plugin_active('woocommerce/woocommerce.php')) {

    /**
     * Init BulkGate DI container
     */
    include_once __DIR__ . '/vendor/autoload.php';

	add_action('init', fn () => Factory::setup(fn () => [
		'db' => $GLOBALS['wpdb'],
		'debug' => WP_DEBUG,
		'gate_url' => 'https://dev1.bulkgate.com',
		'language' => substr(get_locale(), 0, 2) ?: 'en',
		'country' => function_exists('wc_get_base_location') ? wc_get_base_location()['country'] ?? null : null,
		'name' => html_entity_decode(get_option('blogname', 'WooSMS Store'), ENT_QUOTES),
		'url' => get_site_url(),
		'plugin_data' => get_plugin_data(__FILE__),
		'api_version' => '1.0'
	]));

    /**
     * Connect BulkGate actions for customers and admin SMS
     */
	Hook::init();
	Cron::init();

    //add_action('woosms_send_sms', 'Woosms_Hook_sendSms', 100, 4);

	/**
	 * Load Back office for BulkGate SMS plugin
	 */
	if (is_admin())
	{
		include __DIR__ . '/woosms-sms-module-for-woocommerce-admin.php';
	}

    /**
     * Add frontend Asynchronous task consumer asset
     */
    add_action( 'init', function (): void
    {
		/**
		 * @var Settings $settings
		 */
	    $settings = Factory::get()->getByClass(Settings::class);

		if ($settings->load('main:dispatcher') === 'asset')
		{
			$handle = 'bulkgate_asynchronous_asset';

			add_filter('script_loader_tag', function ($tag, $_handle, $src) use($handle)
			{
				if ($_handle === $handle) //found bulkgate's asynchronous asset consumer
				{
					return wp_get_script_tag(['src' => $src, 'async' => true]);
				}
				else
				{
					return $tag;
				}
			}, 10, 3);

			wp_enqueue_script($handle, '/bulkgate/assets/asynchronous.js/', [], null);
		}
    });

    /**
     * Register custom query_var
     */
    add_filter('query_vars', function( $query_vars )
    {
        $query_vars[] = 'bulkgate_asynchronous';
        return $query_vars;
    });

    /**
     * Implementation of frontend Asynchronous task consumer script
     * This is expected to be invoked via <script src="/bulkgate/assets/asynchronous.js" async />
     */
    add_action('template_redirect', function()
    {
        if (get_query_var('bulkgate_asynchronous') === 'asset')
        {
            header('Content-Type: application/javascript');
            header('Cache-Control: no-store');

            $hit = 'no';
            $di = Factory::get();

            /**
             * @var Settings $settings
             */
            $settings = $di->getByClass(Settings::class);

            if ($settings->load('main:dispatcher') === Dispatcher::Asset)
            {
                /**
                 * @var Asynchronous $asynchronous
                 */
                $asynchronous = $di->getByClass(Asynchronous::class);
                $asynchronous->run(max(5, (int) ($settings->load('main:cron-limit') ?? 10)));
                $hit = 'yes';
            }

            echo "// Asynchronous task consumer (HIT: $hit)";
            exit;
        }
    });





    /*function Woosms_Hook_sendSms($number, $template, array $variables = [], array $settings = [])
    {

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
    }*/



    /**
     * Register install scripts
     */
    register_activation_hook(__FILE__, fn () => Factory::get()->getByClass(Settings::class)->install());
    register_activation_hook(__FILE__, function() {
        add_rewrite_rule( 'bulkgate/assets/asynchronous\.js$', 'index.php?bulkgate_asynchronous=asset', 'top');
        flush_rewrite_rules();
    });

    //todo: TESTING - smazat a otestovat jak funguje pri aktivaci a deaktivaci pluginu
    add_action('init', fn () => add_rewrite_rule( 'bulkgate/assets/asynchronous\.js$', 'index.php?bulkgate_asynchronous=asset', 'top'));


	/**
	 * Register uninstall scripts
	 */
	register_deactivation_hook(__FILE__, fn () => Factory::get()->getByClass(Settings::class)->uninstall());
    register_deactivation_hook(__FILE__, fn () => flush_rewrite_rules());

} else {

    /**
     * WooCommerce is not installed
     */
    deactivate_plugins(plugin_basename(__FILE__));
}
