<?php declare(strict_types=1);

namespace BulkGate\WooSms\Event\Test;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use Mockery;
use Tester\{Assert, TestCase};
use BulkGate\{Plugin\DI\Container, Plugin\Event\Asynchronous, Plugin\Event\Dispatcher, Plugin\Settings\Settings, WooSms\DI\Factory, WooSms\Event\AssetDispatcher};

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/.mock-asset-dispatcher.php';

/**
 * @testCase
 */
class AssetDispatcherTest extends TestCase
{
	public function testBase(): void
	{
		AssetDispatcher::init();

		$callbacks = $GLOBALS['asset_callback'];

		Assert::count(3, $callbacks);

		$factory = Mockery::mock('overload:' . Factory::class);
		$factory->shouldReceive('get')->withNoArgs()->once()->andReturn($container = Mockery::mock(Container::class));

		// ADD ASSET
		$container->shouldReceive('getByClass')->with(Settings::class)->once()->andReturn($settings = Mockery::mock(Settings::class));
		$settings->shouldReceive('load')->with('main:dispatcher')->once()->andReturn(Dispatcher::Asset);

		$callbacks['action_init']();

		$callbacks = $GLOBALS['asset_callback'];

		Assert::same('{"src":"src.com","id":"bulkgate-asynchronous-asset-js","async":true}', $callbacks['filter_script_loader_tag']('xxx', 'bulkgate-asynchronous-asset', 'src.com'));
		Assert::same('xxx', $callbacks['filter_script_loader_tag']('xxx', 'bulkgate-asynchronous-asset-invalid', 'src.com'));

		Assert::same(['/?bulkgate-asynchronous=asset', [], null, false], $GLOBALS['asset_style']['bulkgate-asynchronous-asset']);

		// ENABLE PARAMETER
		Assert::same(['test', 'bulkgate-asynchronous'], $callbacks['filter_query_vars'](['test']));

		// DISPATCH ASSET
		$container->shouldReceive('getByClass')->with(Asynchronous::class)->once()->andReturn($asynchronous = Mockery::mock(Asynchronous::class));
		$settings->shouldReceive('load')->with('main:cron-limit')->once()->andReturn(150);
		$asynchronous->shouldReceive('run')->with(150)->once()->andReturn(54);
		$callbacks['action_template_redirect']();
	}


	public function tearDown(): void
	{
		Mockery::close();
	}
}

(new AssetDispatcherTest())->run();
