<?php declare(strict_types=1);

namespace BulkGate\WooSms\Event\Loader;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use WC_Order, WC_Product, WC_Meta_Data;
use BulkGate\{Plugin\Event\Helpers, Plugin\Eshop\Language, Plugin\Event\Variables, Plugin\Localization\Formatter, Plugin\Strict, Plugin\Event\DataLoader, Plugin\Utils\Strings};
use function date, implode, sprintf, strtotime;

class Order implements DataLoader
{
	use Strict;

	private Formatter $formatter;

	private Language $language;

	public function __construct(Formatter $formatter, Language $language)
	{
		$this->formatter = $formatter;
		$this->language = $language;
	}


	public function load(Variables $variables, array $parameters = []): void
	{
		if (!isset($variables['order_id']))
		{
			return;
		}

		/**
		 * @var WC_Order $order
		 */
		$order = isset($parameters['order']) && $parameters['order'] instanceof WC_Order ? $parameters['order'] : wc_get_order((int) $variables['order_id']);

		$variables['lang_id'] = $this->language->get((int) $variables['order_id']);

		$variables['long_order_id'] = sprintf("%06d", $variables['order_id']);
		$variables['order_reference'] = $variables['order_ref'] = $order->get_order_number();
		$variables['order_currency'] = $order->get_currency();
		$variables['order_payment'] = $order->get_payment_method_title();
		$variables['order_total_paid'] = (string) $order->get_total();
		$variables['order_total_formatted'] = $this->formatter->format('price', $variables['order_total_paid'], $variables['order_currency']);

		$date = $order->get_date_created();

		if ($date !== null)
		{
			$date = $date->format('Y-m-d H:i:s');
		}

		$variables['order_date'] = $this->formatter->format('date', $date);
		$variables['order_datetime'] = $this->formatter->format('datetime', $date);
		$variables['order_time'] = $this->formatter->format('time', $date);

		$billing = $order->get_address('billing');
		$shipping = $order->get_address('shipping');

		$variables['customer_id'] = $order->get_customer_id() ?: null;

		$variables['customer_firstname'] = Helpers::address('first_name', $shipping, $billing);
		$variables['customer_lastname'] = Helpers::address('last_name', $shipping, $billing);
		$variables['customer_company'] = Helpers::address('company', $shipping, $billing);
		$variables['customer_street'] = Helpers::joinStreet('address_1', 'address_2', $shipping, $billing);
		$variables['customer_city'] = Helpers::address('city', $shipping, $billing);
		$variables['customer_state'] = Helpers::address('state', $shipping, $billing);
		$variables['customer_postcode'] = Helpers::address('postcode', $shipping, $billing);
		$variables['customer_country'] = $this->formatter->format('country', Helpers::address('country', $billing, $shipping));
		$variables['customer_country_id'] = Strings::lower(Helpers::address('country', $billing, $shipping) ?? '') ?: null;
		$variables['customer_mobile'] = $variables['customer_phone'] = Helpers::address('phone', $shipping, $billing);
		$variables['customer_email'] = Helpers::address('email', $shipping, $billing);

		$variables['customer_invoice_firstname'] = Helpers::address('first_name', $billing, $shipping);
		$variables['customer_invoice_lastname'] = Helpers::address('last_name', $billing, $shipping);
		$variables['customer_invoice_company'] = Helpers::address('company', $billing, $shipping);
		$variables['customer_invoice_street'] = Helpers::joinStreet('address_1', 'address_2', $billing, $shipping);
		$variables['customer_invoice_city'] = Helpers::address('city', $billing, $shipping);
		$variables['customer_invoice_state'] = Helpers::address('state', $billing, $shipping);
		$variables['customer_invoice_postcode'] = Helpers::address('postcode', $billing, $shipping);
		$variables['customer_invoice_country'] = $this->formatter->format('country', Helpers::address('country', $billing, $shipping));
		$variables['customer_invoice_country_id'] = Strings::lower(Helpers::address('country', $billing, $shipping) ?? '') ?: null;
		$variables['customer_invoice_mobile'] = $variables['customer_invoice_phone'] = Helpers::address('phone', $billing, $shipping);
		$variables['customer_invoice_email'] = Helpers::address('email', $billing, $shipping);

		$variables['customer_message'] = $order->get_customer_note();

		$v1 = $v2 = $v3 = $v4 = $p1 = $p2 = [];

		foreach ($order->get_items() as $item)
		{
			$qty = $item->get_quantity();
			$name = $item->get_name();
			$model = $name;
			$total = '0.0';

			if ($item instanceof \WC_Order_Item_Product)
			{
				$product = $item->get_product();
				$total = $item->get_total();
				$model = $product instanceof WC_Product ? $product->get_sku() : $name;
			}

			$product_id = $item->get_id();
			$total_formatted = $this->formatter->format('price', $total, $variables['order_currency']);

			$v1[] = "{$qty}x $name $model $total_formatted";
			$v2[] = "{$qty}x $name $total_formatted";
			$v3[] = "{$qty}x ($product_id) $name $model $total_formatted";
			$v4[] = "{$qty}x $model $total_formatted";

			$p1[] = "$qty,$name,$total";
			$p2[] = "$qty;$name;$total";
		}

		$variables['order_products1'] = implode('; ', $v1);
		$variables['order_products2'] = implode('; ', $v2);
		$variables['order_products3'] = implode('; ', $v3);
		$variables['order_products4'] = implode('; ', $v4);

		$variables['order_products5'] = implode("\n", $v1);
		$variables['order_products6'] = implode("\n", $v2);
		$variables['order_products7'] = implode("\n", $v3);
		$variables['order_products8'] = implode("\n", $v4);

		$variables['order_smsprinter1'] = implode(';', $p1);
		$variables['order_smsprinter2'] = implode(';', $p2);

		$timestamp = $date !== null ? strtotime($date) ?: time() : time();
		$variables['order_date1'] = date('d.m.Y', $timestamp);
		$variables['order_date2'] = date('d/m/Y', $timestamp);
		$variables['order_date3'] = date('d-m-Y', $timestamp);
		$variables['order_date4'] = date('Y-m-d', $timestamp);
		$variables['order_date5'] = date('m.d.Y', $timestamp);
		$variables['order_date6'] = date('m/d/Y', $timestamp);
		$variables['order_date7'] = date('m-d-Y', $timestamp);
		$variables['order_time1'] = date('H:i:s', $timestamp);

		/**
		 * @var WC_Meta_Data $meta
		 */
		foreach ($order->get_meta_data() as $meta)
		{
			['key' => $key, 'value' => $value] = $meta->get_data();

			$variables["extra_$key"] = $value;
		}
	}
}
