<?php declare(strict_types=1);

namespace BulkGate\WooSms\Event\Loader\Test;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use Tester\{Assert, TestCase};
use BulkGate\{Plugin\Event\Variables, WooSms\Event\Loader\OrderStatus};

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/.mock.php';

/**
 * @testCase
 */
class OrderStatusTest extends TestCase
{
	public function testBase(): void
	{
		$variables = new Variables(['test' => 'ok', 'order_status_id' => 'wc-processing', 'order_status_id_from' => 'wc-pending']);

		$loader = new OrderStatus();

		$loader->load($variables);

		Assert::same([
			'test' => 'ok',
			'order_status_id' => 'wc-processing',
			'order_status_id_from' => 'wc-pending',
			'order_status' => 'Processing',
			'order_status_from' => 'Pending payment',
		], $variables->toArray());
	}


	public function testShort(): void
	{
		$variables = new Variables(['test' => 'ok', 'order_status_id' => 'processing', 'order_status_id_from' => 'pending']);

		$loader = new OrderStatus();

		$loader->load($variables);

		Assert::same([
			'test' => 'ok',
			'order_status_id' => 'wc-processing',
			'order_status_id_from' => 'wc-pending',
			'order_status' => 'Processing',
			'order_status_from' => 'Pending payment',
		], $variables->toArray());
	}
}

(new OrderStatusTest())->run();
