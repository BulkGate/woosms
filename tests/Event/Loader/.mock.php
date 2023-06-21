<?php declare(strict_types=1);

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\Database\Connection;
use BulkGate\Plugin\Event\Variables;

/**
 * @param mixed ...$arg
 */
function do_action(string $hook_name, ...$arg): void
{
	/**
	 * @var Variables $variables
	 */
	$variables = $arg[0];

	$variables[$hook_name] = 'ok';

	$variables["{$hook_name}_db"] = $arg[1] instanceof Connection ? 'ok' : 'fail';
}


/**
 * @param string $option_name
 * @param mixed $default
 * @return mixed
 */
function get_option(string $option_name, $default)
{
	if ($option_name === 'admin_email')
	{
		return 'xxx@bulkgate.com';
	}
	else if ($option_name === 'woocommerce_currency')
	{
		return 'CZK';
	}
	return $default;
}


function wc_get_order_statuses(): array
{
	return [
		'wc-pending'    => 'Pending payment',
		'wc-processing' => 'Processing',
		'wc-on-hold'    => 'On hold',
		'wc-completed'  => 'Completed',
	];
}


function sanitize_text_field(string $s): string
{
	return "$$s$";
}


class WC_Product
{
	public function get_stock_quantity(): int
	{
		return 10;
	}


	public function get_name(): string
	{
		return 'Product XYZ';
	}


	public function get_sku(): string
	{
		return '||||||||||||||||';
	}


	public function get_price(): float
	{
		return 1.589;
	}
}


class WC_Customer
{
	public function get_billing(): array
	{
		return [
			'first_name' => 'John',
			'last_name'  => 'Doe',
			'company'    => 'BulkGate',
			'address_1'  => 'Street 123',
			'address_2'  => 'Street 456',
			'city'       => 'Prague',
			'state'      => 'OL',
			'postcode'   => '12345',
			'country'    => 'CZ',
			'email'      => 'xxx@bulkgate.com',
			'phone'      => '+420123456789',
		];
	}


	public function get_shipping(): array
	{
		return [
			'first_name' => 'John',
			'last_name'  => 'Doe',
			'company'    => 'BulkGate',
			'address_1'  => 'Street 123',
			'address_2'  => 'Street 456',
			'city'       => 'Prague',
			'state'      => 'OL',
			'postcode'   => '12345',
			'country'    => 'CZ',
		];
	}
}


class WC_Order extends WC_Customer
{
	public function get_order_number(): string
	{
		return 'WC-451';
	}


	public function get_currency(): string
	{
		return 'CZK';
	}


	public function get_payment_method_title(): string
	{
		return 'PayPal';
	}


	public function get_total(): float
	{
		return 451.0;
	}


	public function get_date_created(): DateTime
	{
		return new DateTime('2019-01-01 12:00:00');
	}


	public function get_address(): array
	{
		return parent::get_billing();
	}


	public function get_customer_id(): int
	{
		return 896;
	}


	public function get_customer_note(): string
	{
		return 'Note';
	}


	public function get_items(): array
	{
		return [new WC_Order_Item_Product()];
	}


	public function get_meta_data(): array
	{
		return [new WC_Meta()];
	}
}


class WC_Order_Item_Product
{
	public function get_id(): int
	{
		return 156;
	}


	public function get_total(): string
	{
		return '451.0';
	}


	public function get_quantity(): int
	{
		return 500;
	}


	public function get_name(): string
	{
		return 'Product XYZ';
	}


	public function get_product(): WC_Product
	{
		return new WC_Product();
	}
}


class WC_Meta
{
	public function get_data(): array
	{
		return [
			'key' => 'k',
			'value' => 'v'
		];
	}
}