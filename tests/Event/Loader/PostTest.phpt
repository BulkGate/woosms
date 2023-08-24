<?php declare(strict_types=1);

namespace BulkGate\WooSms\Event\Loader\Test;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use Tester\{Assert, TestCase};
use BulkGate\{WooSms\Event\Loader\Post, Plugin\Event\Variables};


require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/.mock.php';

/**
 * @testCase
 */
class PostTest extends TestCase
{
	public function testBase(): void
	{
		$variables = new Variables(['test' => 'ok']);

		$_POST = [
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

			'invalid' => 'invalid'
		];

		$loader = new Post();

		$loader->load($variables);

		Assert::same([
			'test' => 'ok',
			'customer_firstname' => '$customer_firstname$',
			'customer_lastname' => '$customer_lastname$',
			'customer_phone' => '$customer_phone$',
			'customer_mobile' => '$customer_mobile$',
			'customer_email' => '$customer_email$',
			'customer_company' => '$customer_company$',
			'customer_country' => '$customer_country$',
		] , $variables->toArray());
	}
}

(new PostTest())->run();
