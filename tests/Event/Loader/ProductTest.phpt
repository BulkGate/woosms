<?php declare(strict_types=1);

namespace BulkGate\WooSms\Event\Loader\Test;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use WC_Product;
use Tester\{Assert, TestCase};
use BulkGate\{Plugin\Event\Variables, WooSms\Event\Loader\Product};

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/.mock.php';

/**
 * @testCase
 */
class ProductTest extends TestCase
{
	public function testBase(): void
	{
		$variables = new Variables(['test' => 'ok', 'product_id' => 451]);

		$loader = new Product();

		$loader->load($variables, ['product' => new WC_Product()]);

		Assert::same([
			'test' => 'ok',
			'product_id' => 451,
			'product_quantity' => 10,
			'product_name' => 'Product XYZ',
			'product_ref' => '||||||||||||||||',
			'product_price' => 1.589,
		], $variables->toArray());
	}


	public function testNotProductId(): void
	{
		$variables = new Variables(['test' => 'ok']);

		$loader = new Product();

		$loader->load($variables);

		Assert::same([
			'test' => 'ok',
		], $variables->toArray());
	}
}

(new ProductTest())->run();
