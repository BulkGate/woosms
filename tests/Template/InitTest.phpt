<?php declare(strict_types=1);

namespace BulkGate\WooSms\Template\Test;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use Mockery;
use WC_Order;
use Tester\{Assert, TestCase};
use BulkGate\{Plugin\Debug\Logger,
	Plugin\Debug\Requirements,
	Plugin\DI\Container,
	Plugin\Eshop\EshopSynchronizer,
	Plugin\Settings\Settings,
	Plugin\User\Sign,
	Plugin\Utils\JsonResponse,
	WooSms\Ajax\Authenticate,
	WooSms\Ajax\PluginSettingsChange,
	WooSms\Debug\Page,
	WooSms\DI\Factory,
	WooSms\Template\Basic,
	WooSms\Template\Init,
	WooSms\Template\SendMessage,
	WooSms\Utils\Meta};
use WP_Post;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/.mock-init.php';

/**
 * @testCase
 */
class InitTest extends TestCase
{
	public function testInit(): void
	{
		Init::init();

		$post = Mockery::mock(WP_Post::class);
		$post->ID = 451;
		//$post->shouldReceive('get_id')->withNoArgs()->once()->andReturn(451);
		$debug = Mockery::mock('overload:' . Page::class);
		$basic = Mockery::mock('overload:' . Basic::class);
		$send_message = Mockery::mock('overload:' . SendMessage::class);
		$factory = Mockery::mock('overload:' . Factory::class);
		$json_response = Mockery::mock('overload:' . JsonResponse::class);
		$factory->shouldReceive('get')->withNoArgs()->once()->andReturn($container = Mockery::mock(Container::class));
		$container->shouldReceive('getByClass')->with(Logger::class)->once()->andReturn($logger = Mockery::mock(Logger::class));
		$container->shouldReceive('getByClass')->with(Requirements::class)->once()->andReturn($requirements = Mockery::mock(Requirements::class));
		$debug->shouldReceive('print')->with($logger, $requirements)->once();
		$container->shouldReceive('getByClass')->with(EshopSynchronizer::class)->once()->andReturn($eshop = Mockery::mock(EshopSynchronizer::class));
		$eshop->shouldReceive('run')->withNoArgs()->once();
		$basic->shouldReceive('print')->with($container)->once();
		$container->shouldReceive('getByClass')->with(Settings::class)->once()->andReturn($settings = Mockery::mock(Settings::class));
		$settings->shouldReceive('load')->with('static:application_token')->once()->andReturn('token');
		$send_message->shouldReceive('print')->with($container, Mockery::type(WC_Order::class), [])->once();
		$container->shouldReceive('getByClass')->with(Authenticate::class)->once()->andReturn($authenticate = Mockery::mock(Authenticate::class));
		$authenticate->shouldReceive('run')->with('https://exmaple.com/admin.php?page=bulkgate#/sign/in')->once();
		$container->shouldReceive('getByClass')->with(Sign::class)->twice()->andReturn($sign = Mockery::mock(Sign::class));
		$sign->shouldReceive('in')->with('$xxx@bulkgate.com$', '$P@ssw0rd$', 'https://exmaple.com/admin.php?page=bulkgate#/dashboard')->once()->andReturn(['id' => 'xxx', 'token' => 'xxx']);
		$json_response->shouldReceive('send')->with(['id' => 'xxx', 'token' => 'xxx'])->once();
		$sign->shouldReceive('out')->with('https://exmaple.com/admin.php?page=bulkgate#/sign/in')->once()->andReturn(['redirect' => 'https://exmaple.com/admin.php?page=bulkgate#/sign/in']);
		$json_response->shouldReceive('send')->with(['redirect' => 'https://exmaple.com/admin.php?page=bulkgate#/sign/in'])->once();
		$container->shouldReceive('getByClass')->with(PluginSettingsChange::class)->once()->andReturn($plugin_settings_change = Mockery::mock(PluginSettingsChange::class));
		$plugin_settings_change->shouldReceive('run')->with(['email' => 'xxx@bulkgate.com', 'password' => 'P@ssw0rd'])->once()->andReturn(['redirect' => 'this']);
		$json_response->shouldReceive('send')->with(['redirect' => 'this'])->once();

		$GLOBALS['init_callback']['action_admin_menu']();

		$GLOBALS['init_callback']['page_bulkgate-debug']();
		$GLOBALS['init_callback']['page_bulkgate']();

		Assert::same([Meta::class, 'settingsLink'], $GLOBALS['init_callback']['filter_plugin_action_links']);
		Assert::same([Meta::class, 'links'], $GLOBALS['init_callback']['filter_plugin_row_meta']);

		$GLOBALS['init_callback']['action_add_meta_boxes']('shop_order');
		$GLOBALS['init_callback']['meta_box_bulkgate_send_message']($post);
		$GLOBALS['init_callback']['action_add_meta_boxes']('invalid');
		$GLOBALS['init_callback']['action_wp_ajax_authenticate']();
		$GLOBALS['init_callback']['action_wp_ajax_login']();
		$GLOBALS['init_callback']['action_wp_ajax_logout_module']();
		$GLOBALS['init_callback']['action_wp_ajax_save_module_settings']();

		Assert::same([
			'page_bulkgate-debug' => [
				'BulkGate Debug',
				'BulkGate Debug',
				'manage_options',
				'bulkgate-debug',
				null,
			],
			'page_bulkgate' => [
				'bulkgate',
				'BulkGate SMS',
				'manage_options',
				'bulkgate',
				'data:image/svg+xml;base64,PHN2ZyBpZD0ic3ZnNDA1OCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB2aWV3Qm94PSIwIDAgNDEuMyAzMS43NCI+PGRlZnM+PHN0eWxlPi5jbHMtMXtmaWxsOiM5Y2EyYTc7fTwvc3R5bGU+PC9kZWZzPjx0aXRsZT5sb2dvPC90aXRsZT48ZyBpZD0ibGF5ZXIxIj48ZyBpZD0iZzM0MTgiPjxwYXRoIGlkPSJwYXRoMzQyMCIgY2xhc3M9ImNscy0xIiBkPSJNMTUwLjA2LDE3LjExYzYuNzUsNC42MiwxMS40NywxMC41NSwxMy40MSwyNC4xN2g3LjI0QzE3MCwyNy43LDE2MS44NSwxNiwxNTAuMDYsOS41NCwxMzguMjgsMTYsMTMwLjE3LDI3LjcsMTI5LjQxLDQxLjI4aDcuMjVzMTIuODMuNDgsMjEuMzEtMTIuODdjMCwwLTguNTIsNC41My0xMy41MywyLjY1LTQuNzQtMS43OC0yLjQ1LTUuODktMi4xOS02LjMzYTI4LjI5LDI4LjI5LDAsMCwxLDcuODEtNy42MiIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoLTEyOS40MSAtOS41NCkiLz48L2c+PC9nPjwvc3ZnPg==',
				58,
			],
			'meta_bulkgate_send_message' => [
				'bulkgate_send_message',
				'BulkGate SMS',
				'shop_order',
				'side',
				'high',
				null,
			],
		], $GLOBALS['pages']);

		Mockery::close();
	}
}

(new InitTest())->run();
