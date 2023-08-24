<?php declare(strict_types=1);

namespace BulkGate\WooSms\Eshop\Test;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use Tester\{Assert, TestCase};
use BulkGate\WooSms\Eshop\OrderStatusWordpress;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/.mock.php';

/**
 * @testCase
 */
class OrderStatusWordpressTest extends TestCase
{
	public function testBase(): void
	{
		$status = new OrderStatusWordpress();

		Assert::same([
			'wc-pending' => 'Pending payment',
			'wc-processing' => 'Processing',
			'wc-on-hold' => 'On hold',
			'wc-completed' => 'Completed',
		], $status->load());
	}
}

(new OrderStatusWordpressTest())->run();
