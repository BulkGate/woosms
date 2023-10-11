<?php declare(strict_types=1);

namespace BulkGate\WooSms\Event\Test;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use Mockery;
use WC_Order, WC_Product;
use Tester\{Assert, TestCase};
use BulkGate\{Plugin\DI\Container, Plugin\Event\Dispatcher, Plugin\Event\Hook as HookDispatcher, Plugin\Event\Variables, WooSms\DI\Factory, WooSms\Event\Hook};

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/.mock-hook.php';

/**
 * @testCase
 */
class HookTest extends TestCase
{
	public function testBase(): void
	{
		Hook::init();

		$factory = Mockery::mock('overload:' . Factory::class);
		$factory->shouldReceive('get')->withNoArgs()->once()->andReturn($container = Mockery::mock(Container::class));
		$container->shouldReceive('getByClass')->with(Dispatcher::class)->times(7)->andReturn($dispatcher = Mockery::mock(Dispatcher::class));

		$callbacks = $GLOBALS['hook_callback'];

		Assert::count(9, $callbacks);

		$order = Mockery::mock(WC_Order::class);
		$order->shouldReceive('add_order_note')->with('📲 BulkGate: processing ➡️ complete')->once();
		$dispatcher->shouldReceive('dispatch')->with('order', 'change-status', Mockery::on(fn (Variables $variables): bool => $variables->toArray() === [
			'order_id' => 451,
			'order_status_id' => 'complete',
			'order_status_id_from' => 'processing',
		]), Mockery::type('array'), Mockery::on(function (callable $callback): bool
		{
			$callback();
			return true;
		}))->once();
		$callbacks['action_woocommerce_order_status_changed'](451, 'processing', 'complete', $order);

		$order->shouldReceive('get_id')->withNoArgs()->once()->andReturn(451);
		$order->shouldReceive('add_order_note')->with('📲 BulkGate: New Order')->once();
		$dispatcher->shouldReceive('dispatch')->with('order', 'new', Mockery::on(fn (Variables $variables): bool => $variables->toArray() === [
			'order_id' => 451,
		]), Mockery::type('array'), Mockery::on(function (callable $callback): bool
		{
			$callback();
			return true;
		}))->once();
		$callbacks['action_woocommerce_checkout_order_created']($order);

		$dispatcher->shouldReceive('dispatch')->with('customer', 'new', Mockery::on(fn (Variables $variables): bool => $variables->toArray() === [
			'customer_id' => 789,
			'password' => 'password'
		]))->once();
		$callbacks['action_woocommerce_created_customer'](789, [], 'password');

		$dispatcher->shouldReceive('dispatch')->with('order', 'payment', Mockery::on(fn (Variables $variables): bool => $variables->toArray() === [
			'order_id' => 451,
			'order_payment_transaction_id' => 'testPayment'
		]))->once();
		$callbacks['action_woocommerce_payment_complete'](451, 'testPayment');

		$dispatcher->shouldReceive('dispatch')->with('product', 'out-of-stock', Mockery::on(fn (Variables $variables): bool => $variables->toArray() === [
			'product_id' => 1,
		]), Mockery::type('array'))->once();
		$callbacks['action_woocommerce_low_stock'](new WC_Product());

		$dispatcher->shouldReceive('dispatch')->with('product', 'out-of-stock', Mockery::on(fn (Variables $variables): bool => $variables->toArray() === [
			'product_id' => 1,
		]), Mockery::type('array'))->once();
		$callbacks['action_woocommerce_no_stock'](new WC_Product());

		$dispatcher->shouldReceive('dispatch')->with('product', 'on-back-order', Mockery::on(fn (Variables $variables): bool => $variables->toArray() === [
			'product_id' => 1,
			'order_id' => 451,
			'order_back_quantity' => 153,
		]), Mockery::type('array'))->once();
		$callbacks['action_woocommerce_product_on_backorder'](['product' => new \WC_Product(), 'order_id' => 451, 'quantity' => 153]);

		$container->shouldReceive('getByClass')->with(HookDispatcher::class)->twice()->andReturn($hook = Mockery::mock(HookDispatcher::class));
		$hook->shouldReceive('send')->with('/api/2.0/advanced/transactional', [
			'number' => '420777777777',
			'application_product' => 'ws',
			'tag' => 'module_custom',
			'variables' => ['abc' => 'test'],
			'country' => 'cz',
			'channel' => [
				'sms' => [
					'sender_id' => 'gText',
					'sender_id_value' => 'BulkGate',
					'unicode' => true,
					'text' => 'test <abc>',
				]
			]
		])->twice();
		$callbacks['action_bulkgate_send_sms']('420777777777', 'test <abc>', ['abc' => 'test'], ['unicode' => true, 'country' => 'cz', 'senderValue' => 'BulkGate', 'senderType' => 'gText']);
		$callbacks['action_woosms_send_sms']('420777777777', 'test <abc>', ['abc' => 'test'], ['unicode' => true, 'country' => 'cz', 'senderValue' => 'BulkGate', 'senderType' => 'gText']);

		Mockery::close();
	}
}

(new HookTest())->run();
