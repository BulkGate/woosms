<?php declare(strict_types=1);

namespace BulkGate\WooSms\Event\Loader\Test;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use Tester\{Assert, TestCase};
use BulkGate\{Plugin\Localization\FormatterIntl, WooSms\Event\Loader\Customer, Plugin\Event\Variables};

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/.mock.php';

/**
 * @testCase
 */
class CustomerTest extends TestCase
{
	public function testBase(): void
	{
		$variables = new Variables(['test' => 'ok', 'customer_id' => 451]);

		$loader = new Customer(new FormatterIntl('fr', 'FR'));

		$loader->load($variables);

		Assert::same([
			'test' => 'ok',
			'customer_id' => 451,
			'customer_firstname' => 'John',
			'customer_lastname' => 'Doe',
			'customer_company' => 'BulkGate',
			'customer_street' => 'Street 123, Street 456',
			'customer_city' => 'Prague',
			'customer_state' => 'OL',
			'customer_postcode' => '12345',
			'customer_country' => 'Tchéquie',
			'customer_country_id' => 'cz',
			'customer_phone' => '+420123456789',
			'customer_mobile' => '+420123456789',
			'customer_email' => 'xxx@bulkgate.com',
			'customer_invoice_firstname' => 'John',
			'customer_invoice_lastname' => 'Doe',
			'customer_invoice_company' => 'BulkGate',
			'customer_invoice_street' => 'Street 123, Street 456',
			'customer_invoice_city' => 'Prague',
			'customer_invoice_state' => 'OL',
			'customer_invoice_postcode' => '12345',
			'customer_invoice_country' => 'Tchéquie',
			'customer_invoice_country_id' => 'cz',
			'customer_invoice_phone' => '+420123456789',
			'customer_invoice_mobile' => '+420123456789',
			'customer_invoice_email' => 'xxx@bulkgate.com',
		], $variables->toArray());
	}
}

(new CustomerTest())->run();
