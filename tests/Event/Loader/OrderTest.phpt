<?php declare(strict_types=1);

namespace BulkGate\WooSms\Event\Loader\Test;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use Mockery;
use Tester\{Assert, Expect, TestCase};
use BulkGate\{Plugin\Eshop\Language, Plugin\Localization\FormatterIntl, Plugin\Event\Variables, WooSms\Event\Loader\Order};

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/.mock.php';

/**
 * @testCase
 */
class OrderTest extends TestCase
{
	public function testBase(): void
	{
		$variables = new Variables(['test' => 'ok', 'order_id' => 451]);

		$loader = new Order(new FormatterIntl('fr', 'FR'), $language = Mockery::mock(Language::class));
		$language->shouldReceive('get')->with(451)->once()->andReturn('cs_CZ');

		$loader->load($variables);

		Assert::equal([
			'test' => 'ok',
			'order_id' => 451,
			'lang_id' => 'cs_CZ',
			'long_order_id' => '000451',
			'order_ref' => 'WC-451',
			'order_reference' => 'WC-451',
			'order_currency' => 'CZK',
			'order_payment' => 'PayPal',
			'order_total_paid' => '451',
			'order_total_formatted' => '451,00 CZK',
			'order_date' => '1 janv. 2019',
			'order_datetime' => Expect::type('string'),
			'order_time' => '12:00',
			'customer_id' => 896,
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
			'customer_message' => 'Note',
			'order_products1' => '500x Product XYZ |||||||||||||||| 451,00 CZK',
			'order_products2' => '500x Product XYZ 451,00 CZK',
			'order_products3' => '500x (156) Product XYZ |||||||||||||||| 451,00 CZK',
			'order_products4' => '500x |||||||||||||||| 451,00 CZK',
			'order_products5' => '500x Product XYZ |||||||||||||||| 451,00 CZK',
			'order_products6' => '500x Product XYZ 451,00 CZK',
			'order_products7' => '500x (156) Product XYZ |||||||||||||||| 451,00 CZK',
			'order_products8' => '500x |||||||||||||||| 451,00 CZK',
			'order_smsprinter1' => '500,Product XYZ,451.0',
			'order_smsprinter2' => '500;Product XYZ;451.0',
			'order_date1' => '01.01.2019',
			'order_date2' => '01/01/2019',
			'order_date3' => '01-01-2019',
			'order_date4' => '2019-01-01',
			'order_date5' => '01.01.2019',
			'order_date6' => '01/01/2019',
			'order_date7' => '01-01-2019',
			'order_time1' => '12:00:00',
			'extra_k' => 'v',
		], $variables->toArray());
	}


	public function testNoOrderId(): void
	{
		$variables = new Variables(['test' => 'ok']);

		$loader = new Order(new FormatterIntl('fr', 'FR'), Mockery::mock(Language::class));

		$loader->load($variables);

		Assert::same(['test' => 'ok'], $variables->toArray());
	}
}

(new OrderTest())->run();
