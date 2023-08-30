<?php declare(strict_types=1);

namespace BulkGate\WooSms\Event\Test;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use Mockery;
use Tester\{Assert, TestCase};
use BulkGate\{Plugin\DI\Container, Plugin\Eshop\EshopSynchronizer, Plugin\Event\Asynchronous, Plugin\Event\Dispatcher, Plugin\Settings\Settings, WooSms\DI\Factory, WooSms\Event\Cron};

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/.mock-cron.php';

/**
 * @testCase
 */
class CronTest extends TestCase
{
	public function testBase(): void
	{
		Cron::init();

		$callbacks = $GLOBALS['cron_callback'];

		Assert::count(4, $callbacks);

		$factory = Mockery::mock('overload:' . Factory::class);
		$factory->shouldReceive('get')->withNoArgs()->once()->andReturn($container = Mockery::mock(Container::class));

		// Schedules
		Assert::same([
			'init' => ['interval' => 60, 'display' => 'test'],
			'bulkgate_send_interval' => ['interval' => 60, 'display' => '~BulkGate Sending Interval~'],
			'bulkgate_synchronize_interval' => ['interval' => 3_600, 'display' => '~BulkGate Synchronize Interval~'],
		], $callbacks['filter_cron_schedules'](['init' => ['interval' => 60, 'display' => 'test']]));

		// Init
		Assert::same([], $GLOBALS['cron_init']);
		$callbacks['action_init']();
		Assert::same([
			['bulkgate_send_interval', 'bulkgate_sending', [], false],
			['bulkgate_synchronize_interval', 'bulkgate_synchronize', [], false],
		], $GLOBALS['cron_init']);


		// Hook sending
		$container->shouldReceive('getByClass')->with(Settings::class)->once()->andReturn($settings = Mockery::mock(Settings::class));
		$settings->shouldReceive('load')->with('main:dispatcher')->once()->andReturn(Dispatcher::Cron);
		$settings->shouldReceive('set')->with('main:cron-run-before', Mockery::type('string'), ['type' => 'string'])->once();
		$container->shouldReceive('getByClass')->with(Asynchronous::class)->once()->andReturn($asynchronous = Mockery::mock(Asynchronous::class));
		$settings->shouldReceive('load')->with('main:cron-limit')->once()->andReturn(50);
		$asynchronous->shouldReceive('run')->with(50)->once();
		$settings->shouldReceive('set')->with('main:cron-run', Mockery::type('string'), ['type' => 'string'])->once();
		$callbacks['action_bulkgate_sending']();

		$container->shouldReceive('getByClass')->with(Settings::class)->once()->andReturn($settings = Mockery::mock(Settings::class));
		$settings->shouldReceive('load')->with('main:dispatcher')->once()->andReturn(Dispatcher::Asset);
		$callbacks['action_bulkgate_sending']();


		// Synchronize
		$container->shouldReceive('getByClass')->with(EshopSynchronizer::class)->once()->andReturn($synchronizer = Mockery::mock(EshopSynchronizer::class));
		$synchronizer->shouldReceive('run')->withNoArgs()->once();
		$callbacks['action_bulkgate_synchronize']();
	}
}

(new CronTest())->run();
