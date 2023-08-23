<?php declare(strict_types=1);

namespace BulkGate\WooSms\Event\Test;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use Mockery;
use Tester\{Assert, TestCase};
use BulkGate\{Plugin\DI\Container, Plugin\DI\MissingServiceException, Plugin\Event\Dispatcher, WooSms\DI\Factory, WooSms\Event\Helpers, Plugin\Event\Variables};

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/.mock.php';

/**
 * @testCase
 */
class HelpersTest extends TestCase
{
	public function testBase(): void
	{
		$variables = new Variables();

		$factory = Mockery::mock('overload:' . Factory::class);
		$factory->shouldReceive('get')->withNoArgs()->once()->andReturn($container = Mockery::mock(Container::class));
		$container->shouldReceive('getByClass')->with(Dispatcher::class)->once()->andReturn($dispatcher = Mockery::mock(Dispatcher::class));
		$dispatcher->shouldReceive('dispatch')->with( 'x', 'y', $variables)->once();

		Helpers::dispatch('test', fn (Dispatcher $dispatcher, string $x, string $y) => $dispatcher->dispatch($x, $y, $variables))('x', 'y');

		Assert::true(true);

		Mockery::close();
	}


	public function testNotFound(): void
	{
		$variables = new Variables();

		$factory = Mockery::mock('overload:' . Factory::class);
		$factory->shouldReceive('get')->withNoArgs()->once()->andReturn($container = Mockery::mock(Container::class));
		$container->shouldReceive('getByClass')->with(Dispatcher::class)->once()->andThrow(MissingServiceException::class, 'invalid_service');

		Helpers::dispatch('test', fn (Dispatcher $dispatcher, string $x, string $y) => $dispatcher->dispatch($x, $y, $variables))('x', 'y');

		Assert::true(true);

		Mockery::close();
	}


	public function testDisable(): void
	{
		$variables = new Variables();

		Helpers::dispatch('test_invalid', fn (Dispatcher $dispatcher, string $x, string $y) => $dispatcher->dispatch($x, $y, $variables))('x', 'y');

		Assert::true(true);
	}


	public function testResolveOrderStatus(): void
	{
		$status = 'wc-processing';
		Assert::same('Processing', Helpers::resolveOrderStatus($status));
		Assert::same('processing', $status);

		$status = 'processing';
		Assert::same('Processing', Helpers::resolveOrderStatus($status));
		Assert::same('processing', $status);

		$status = 'wc-completed';
		Assert::same('Completed', Helpers::resolveOrderStatus($status));
		Assert::same('completed', $status);
	}
}

(new HelpersTest())->run();
