<?php declare(strict_types=1);

namespace BulkGate\WooSms\Template;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{Debug\Logger, Debug\Requirements, Eshop, Settings\Settings, Strict, User\Sign, Utils\JsonResponse};
use BulkGate\WooSms\{Ajax\Authenticate, Ajax\PluginSettingsChange, Debug\Page, DI\Factory, Event\Helpers, Utils\Logo, Utils\Meta};
use function method_exists, in_array;

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
				$di = Factory::get();
				$di->getByClass(Eshop\EshopSynchronizer::class)->run();

				Basic::print($di);
			}, Logo::Menu, 58);

			add_filter('plugin_action_links', [Meta::class, 'settingsLink'], 10, 2);
			add_filter('plugin_row_meta', [Meta::class, 'links'], 10, 2);
		});

		add_action('add_meta_boxes', function (string $post_type): void
		{
			if (in_array($post_type, ['shop_order', 'woocommerce_page_wc-orders'], true) && Factory::get()->getByClass(Settings::class)->load('static:application_token'))
			{
				add_meta_box('bulkgate_send_message', 'BulkGate SMS', fn ($post) => SendMessage::print(Factory::get(), wc_get_order(method_exists($post, 'get_id') ? $post->get_id() : $post->ID), []), $post_type, 'side', 'high');
			}
		});

		add_action('wp_ajax_authenticate', function (): void
		{
			Helpers::checkAccess($_POST[Helpers::CrossSiteRequestForgerySecurityParameter] ?? null) && Factory::get()->getByClass(Authenticate::class)->run(admin_url('admin.php?page=bulkgate#/sign/in'));
		});

		add_action('wp_ajax_login', function (): void
		{
			Helpers::checkAccess($_POST[Helpers::CrossSiteRequestForgerySecurityParameter] ?? null) && JsonResponse::send(Factory::get()->getByClass(Sign::class)->in(
				sanitize_text_field((string)($_POST['__bulkgate']['email'] ?? '')),
				sanitize_text_field((string)($_POST['__bulkgate']['password'] ?? '')),
				admin_url('admin.php?page=bulkgate#/dashboard')
			));
		});

		add_action('wp_ajax_logout_module', function (): void
		{
			Helpers::checkAccess($_POST[Helpers::CrossSiteRequestForgerySecurityParameter] ?? null) && JsonResponse::send(Factory::get()->getByClass(Sign::class)->out(admin_url('admin.php?page=bulkgate#/sign/in')));
		});
		add_action('wp_ajax_save_module_settings', function (): void
		{
			Helpers::checkAccess($_POST[Helpers::CrossSiteRequestForgerySecurityParameter] ?? null) && JsonResponse::send(Factory::get()->getByClass(PluginSettingsChange::class)->run($_POST['__bulkgate'] ?? []));
		});
	}
}
