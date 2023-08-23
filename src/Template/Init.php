<?php declare(strict_types=1);

namespace BulkGate\WooSms\Template;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use WP_Post, WC_Order;
use BulkGate\{Plugin\Debug\Logger, Plugin\Debug\Requirements, Plugin\Eshop, Plugin\Settings\Settings, Plugin\Strict, Plugin\User\Sign, Plugin\Utils\JsonResponse, WooSms\Ajax\Authenticate, WooSms\Ajax\PluginSettingsChange, WooSms\Debug\Page, WooSms\DI\Factory, WooSms\Utils\Logo, WooSms\Utils\Meta};

class Init
{
	use Strict;

	public static function init(): void
	{
		add_action('admin_menu', function (): void
		{
			add_management_page(
				'BulkGate Debug',
				'BulkGate Debug',
				'manage_options',
				'bulkgate-debug',
				fn () => Page::print(Factory::get()->getByClass(Logger::class), Factory::get()->getByClass(Requirements::class))
			);

			add_menu_page('bulkgate', 'BulkGate SMS', 'manage_options', 'bulkgate', function (): void
			{
				Factory::get()->getByClass(Eshop\EshopSynchronizer::class)->run();

				Basic::print(Factory::get());
			}, Logo::Menu, 58);

			add_filter('plugin_action_links', [Meta::class, 'settingsLink'], 10, 2);
			add_filter('plugin_row_meta', [Meta::class, 'links'], 10, 2);
		});

		add_action('wp_ajax_authenticate', fn () => Factory::get()->getByClass(Authenticate::class)->run(admin_url('admin.php?page=bulkgate#/sign/in')));

		add_action('wp_ajax_login', fn () => JsonResponse::send(Factory::get()->getByClass(Sign::class)->in(
			sanitize_text_field((string) ($_POST['__bulkgate']['email'] ?? '')),
			sanitize_text_field((string) ($_POST['__bulkgate']['password'] ?? '')),
			admin_url('admin.php?page=bulkgate#/dashboard')
		)));

		add_action('wp_ajax_logout_module', fn () => JsonResponse::send(Factory::get()->getByClass(Sign::class)->out(admin_url('admin.php?page=bulkgate#/sign/in'))));
		add_action('wp_ajax_save_module_settings', fn () => JsonResponse::send(Factory::get()->getByClass(PluginSettingsChange::class)->run($_POST['__bulkgate'] ?? [])));

		add_action('add_meta_boxes', function (string $post_type): void
		{
			if ($post_type === 'shop_order' && Factory::get()->getByClass(Settings::class)->load('static:application_token'))
			{
				add_meta_box('bulkgate_send_message', 'BulkGate SMS', fn (WP_Post $post) => SendMessage::print(Factory::get(), new WC_Order($post->ID), []), 'shop_order', 'side', 'high');
			}
		});
	}
}
