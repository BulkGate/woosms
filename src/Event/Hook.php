<?php declare(strict_types=1);

namespace BulkGate\WooSms\Event;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use WC_Order, WC_Product;
use BulkGate\WooSms\DI\Factory;
use BulkGate\Plugin\{DI\MissingServiceException, Event\Hook as HookDispatcher, Strict, Event\Dispatcher, Event\Variables};
use function add_action, apply_filters, has_filter;

class Hook
{
	use Strict;

	public static function init(): void
	{
		add_action('woocommerce_order_status_changed', Helpers::dispatch('order_status_change', function (
			Dispatcher $dispatcher,
			int $order_id,
			string $from,
			string $to,
			object $order
		): void
		{
			$run_hook = true;

			if (has_filter('run_woosms_hook_changeOrderStatusHook')) // BC
			{
				$run_hook = apply_filters('run_woosms_hook_changeOrderStatusHook', $order);
			}

			if ($run_hook)
			{
				$dispatcher->dispatch('order', 'change-status', new Variables([
					'order_id' => $order_id,
					'order_status_id' => $to,
					'order_status_id_from' => $from,
				]), ['order' => $order], fn () => $order instanceof WC_Order && $order->add_order_note("📲 BulkGate: $from ➡️ $to"));
			}
		}), 100, 4);


		add_action('woocommerce_checkout_order_created', Helpers::dispatch('order_new', fn (Dispatcher $dispatcher, WC_Order $order) =>
			$dispatcher->dispatch('order', 'new', new Variables([
				'order_id' => $order->get_id(),
			]), ['order' => $order], fn () => $order->add_order_note('📲 BulkGate: New Order'))
		), 100, 3);


		add_action('woocommerce_created_customer', Helpers::dispatch('customer_new', fn (Dispatcher $dispatcher, int $customer_id, array $data, /*string|bool*/ $password_generated) =>
			$dispatcher->dispatch('customer', 'new', new Variables([
				'customer_id' => $customer_id,
				'password' => (string) $password_generated,
			]))
		), 100, 3);


		add_action('woocommerce_payment_complete', Helpers::dispatch('order_payment', fn (Dispatcher $dispatcher, int $order_id, /*string|int*/ $transaction_id) =>
			$dispatcher->dispatch('order', 'payment', new Variables([
				'order_id' => $order_id,
				'order_payment_transaction_id' => (string) $transaction_id
			]))
		), 100, 2);


		add_action('woocommerce_low_stock', Helpers::dispatch('product_out_of_stock', fn (Dispatcher $dispatcher, WC_Product $product) =>
			$dispatcher->dispatch('product', 'out-of-stock', new Variables([
				'product_id' => $product->get_id(),
			]), ['product' => $product])
		), 100, 2);


		add_action('woocommerce_no_stock', Helpers::dispatch('product_out_of_stock', fn (Dispatcher $dispatcher, WC_Product $product) =>
			$dispatcher->dispatch('product', 'out-of-stock', new Variables([
				'product_id' => $product->get_id(),
			]), ['product' => $product])
		), 100, 2);


		add_action('woocommerce_product_on_backorder', Helpers::dispatch('product_on_backorder', fn (Dispatcher $dispatcher, array $data) =>
			$dispatcher->dispatch('product', 'on-back-order', new Variables([
				'product_id' => $data['product']->get_id(),
				'order_id' => $data['order_id'] ?? null,
				'order_back_quantity' => $data['quantity'] ?? null,
			]), $data)
		), 100, 2);

		add_action('woosms_send_sms', [Hook::class, 'customMessage'], 100, 4);
		add_action('bulkgate_send_sms', [Hook::class, 'customMessage'], 100, 4);
	}


	/**
	 * @param array<string, scalar> $variables
	 * @param array{unicode?: bool, country?: string, senderType?: string, senderValue?: string} $settings
	 * @throws MissingServiceException
	 */
	public static function customMessage(string $number, string $template, array $variables, array $settings): void
	{
		Factory::get()->getByClass(HookDispatcher::class)->send('/api/2.0/advanced/transactional', [
			'number' => $number,
			'application_product' => 'ws',
			'tag' => 'module_custom',
			'variables' => $variables,
			'country' => $settings['country'] ?? null,
			'channel' => [
				'sms' => [
					'sender_id' => $settings['senderType'] ?? 'gSystem',
			        'sender_id_value' => $settings['senderValue'] ?? '',
			        'unicode' => $settings['unicode'] ?? false,
			        'text' => $template
		        ]
			]
		]);
	}
}
