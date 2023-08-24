<?php declare(strict_types=1);

namespace BulkGate\WooSms\Event\Loader;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\Event\{DataLoader, Variables};

class Post implements DataLoader
{
	private const Mapper = [
		'first_name' => 'customer_firstname',
		'last_name' => 'customer_lastname',
		'phone' => 'customer_phone',
		'mobile' => 'customer_mobile',
		'phone_number' => 'customer_phone',
		'phone_mobile' => 'customer_mobile',
		'email' => 'customer_email',

		'shipping_first_name' => 'customer_firstname',
		'shipping_last_name' => 'customer_lastname',
		'shipping_phone' => 'customer_phone',
		'shipping_company' => 'customer_company',
		'shipping_country' => 'customer_country',

		'billing_first_name' => 'customer_firstname',
		'billing_last_name' => 'customer_lastname',
		'billing_phone' => 'customer_mobile',
		'billing_company' => 'customer_company',
		'billing_country' => 'customer_country',
		'bulkgate_marketing_message_opt_in' => 'marketing_message_opt_in'
	];


	public function load(Variables $variables, array $parameters = []): void
	{
		foreach (self::Mapper as $key => $value)
		{
			if (isset($_POST[$key]))
			{
				$variables[$value] = sanitize_text_field($_POST[$key]);
			}
		}
	}
}
