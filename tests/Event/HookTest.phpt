<?php declare(strict_types=1);

namespace BulkGate\WooSms\Event\Test;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use Mockery;
use WC_Order, WC_Product;
use Tester\{Assert, TestCase};
use BulkGate\{Plugin\DI\Container, Plugin\Event\Dispatcher, Plugin\Event\Variables, WooSms\DI\Factory, WooSms\Event\Hook};

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
		$container->shouldReceive('getByClass')->with(Dispatcher::class)->once()->andReturn($dispatcher = Mockery::mock(Dispatcher::class));

		$callbacks = $GLOBALS['hook_callback'];

		Assert::count(7, $callbacks);

		$dispatcher->shouldReceive('dispatch')->with('order', 'change-status', Mockery::on(fn (Variables $variables): bool => $variables->toArray() === [
			'order_id' => 451,
			'order_status_id' => 'complete',
			'order_status_id_from' => 'processing',
		]), Mockery::type('array'))->once();
		$callbacks['action_woocommerce_order_status_changed'](451, 'processing', 'complete', Mockery::mock(WC_Order::class));

		$dispatcher->shouldReceive('dispatch')->with('order', 'new', Mockery::on(fn (Variables $variables): bool => $variables->toArray() === [
			'order_id' => 451,
		]), Mockery::type('array'))->once();
		$callbacks['action_woocommerce_checkout_order_processed'](451, [], Mockery::mock(WC_Order::class));

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
	}
}

(new HookTest())->run();
