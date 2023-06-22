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
class AssetDispatcherOffTest extends TestCase
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

		// DISPATCH ASSET
		$settings->shouldReceive('load')->with('main:dispatcher')->once()->andReturn(Dispatcher::Cron);
		$container->shouldReceive('getByClass')->with(Asynchronous::class)->once()->andReturn($asynchronous = Mockery::mock(Asynchronous::class));
		$settings->shouldReceive('load')->with('main:cron-limit')->once()->andReturn(150);
		$callbacks['action_template_redirect']();
	}


	public function tearDown(): void
	{
		Mockery::close();
	}
}

(new AssetDispatcherOffTest())->run();
