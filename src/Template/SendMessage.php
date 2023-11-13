<?php declare(strict_types=1);

namespace BulkGate\WooSms\Template;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use WC_Order, WC_Order_Refund;
use BulkGate\{Plugin\DI\Container, Plugin\Event\Helpers, Plugin\IO\Url, Plugin\Settings\Settings, Plugin\Strict, Plugin\User\Sign, Plugin\Utils\Strings, WooSms\Event\Helpers as EventHelpers, WooSms\Utils\Escape};
use function array_filter, array_merge;

class SendMessage
{
	use Strict;

	/**
     * @param WC_Order|WC_Order_Refund|bool|null $order
	 * @param array<string, mixed> $props
	 */
	public static function print(Container $di, $order, array $props = []): void
	{
		$escape_js = [Escape::class, 'js'];

		if ($order instanceof WC_Order)
        {
			$preference = $di->getByClass(Settings::class)->load('main:address_preference') ?? 'delivery';

			$address = $order->get_address($preference === 'delivery' ? 'shipping' : 'billing');

            if (empty($address['phone'] ?? null))
            {
                $address = $order->get_address($preference === 'delivery' ? 'billing' : 'shipping');
            }

            if (!empty($address['phone'] ?? null))
            {
	            $extra = [];

	            foreach ($order->get_meta_data() as $meta) {
		            ['key' => $key, 'value' => $value] = $meta->get_data();

		            $extra["extra_$key"] = $value;
	            }

	            $status = $order->get_status();

	            $props['recipients'][] = array_filter(array_merge([
		            'first_name' => $address['first_name'],
		            'last_name' => $address['last_name'],
		            'company' => $address['company'],
		            'street1' => Helpers::joinStreet('address_1', 'address_2', $address, []),
		            'city' => $address['city'],
		            'zip' => $address['postcode'],
		            'country' => $address['country'],
		            'phone_mobile' => $address['phone'],
		            'phone_number_iso' => Strings::lower($address['country']),
		            'email' => $order->get_billing_email(),
		            'order_status' => EventHelpers::resolveOrderStatus($status),
	            ], $extra), fn($item) => !empty($item));
            }
        }

		wp_print_inline_script_tag(<<<JS
			function init_widget_message_send(widget) { widget.options.SendMessageProps = {$escape_js((object)$props)}; }
		JS);

		$jwt = $di->getByClass(Sign::class)->authenticate();

		wp_print_script_tag([
			'src' => Escape::url($di->getByClass(Url::class)->get("widget/message/send/$jwt?config=init_widget_message_send")),
			'async' => true,
		]);
		?>

        <gate-send-message data-theme='{"palette": {"mode": "light"}}'></gate-send-message>

		<?php
	}
}
