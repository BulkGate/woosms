<?php declare(strict_types=1);

namespace BulkGate\WooSms\Event\Loader\Test;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use Mockery;
use Tester\{Assert, TestCase};
use BulkGate\{Plugin\Eshop\Configuration, Plugin\Eshop\Language, WooSms\Event\Loader\Shop, Plugin\Event\Variables};


require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/.mock.php';

/**
 * @testCase
 */
class ShopTest extends TestCase
{
	public function testBase(): void
	{
		$variables = new Variables(['test' => 'ok']);

		$loader = new Shop($configuration = Mockery::mock(Configuration::class), $language = Mockery::mock(Language::class));
		$configuration->shouldReceive('name')->withNoArgs()->once()->andReturn('Eshop Test');
		$configuration->shouldReceive('url')->withNoArgs()->once()->andReturn('https://www.test.com');
		$language->shouldReceive('get')->withNoArgs()->once()->andReturn('cs');

		$loader->load($variables);

		Assert::same([
			'test' => 'ok',
			'shop_id' => 0,
			'shop_name' => 'Eshop Test',
			'shop_email' => 'xxx@bulkgate.com',
			'shop_domain' => 'https://www.test.com',
			'shop_currency' => 'CZK',
			'lang_id' => 'cs',
		], $variables->toArray());

		Mockery::close();
	}


	public function testExistringLanguage(): void
	{
		$variables = new Variables(['test' => 'ok', 'lang_id' => 'en']);

		$loader = new Shop($configuration = Mockery::mock(Configuration::class), $language = Mockery::mock(Language::class));
		$configuration->shouldReceive('name')->withNoArgs()->once()->andReturn('Eshop Test');
		$configuration->shouldReceive('url')->withNoArgs()->once()->andReturn('https://www.test.com');

		$loader->load($variables);

		Assert::same([
			'test' => 'ok',
			'lang_id' => 'en',
			'shop_id' => 0,
			'shop_name' => 'Eshop Test',
			'shop_email' => 'xxx@bulkgate.com',
			'shop_domain' => 'https://www.test.com',
			'shop_currency' => 'CZK',
		], $variables->toArray());

		Mockery::close();
	}
}

(new ShopTest())->run();
