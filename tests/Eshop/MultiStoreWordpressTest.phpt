<?php declare(strict_types=1);

namespace BulkGate\WooSms\Eshop\Test;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use Mockery;
use Tester\{Assert, TestCase};
use BulkGate\{Plugin\Eshop\Configuration, WooSms\Eshop\MultiStoreWordpress};

require_once __DIR__ . '/../bootstrap.php';

/**
 * @testCase
 */
class MultiStoreWordpressTest extends TestCase
{
	public function testBase(): void
	{
		$multistore = new MultiStoreWordpress($configuration = Mockery::mock(Configuration::class));
		$configuration->shouldReceive('name')->withNoArgs()->once()->andReturn('Test Store');

		Assert::same([0 => 'Test Store'], $multistore->load());

		Mockery::close();
	}
}

(new MultiStoreWordpressTest())->run();
