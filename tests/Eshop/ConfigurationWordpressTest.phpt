<?php declare(strict_types=1);

namespace BulkGate\WooSms\Eshop\Test;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use Tester\{Assert, TestCase};
use BulkGate\WooSms\Eshop\ConfigurationWordpress;

require_once __DIR__ . '/../bootstrap.php';

/**
 * @testCase
 */
class ConfigurationWordpressTest extends TestCase
{
	public function testBase(): void
	{
		$configuration = new ConfigurationWordpress(['version' => '1.0.0'], 'url', 'BulkGate Plugin Test Shop');

		Assert::same('url', $configuration->url());
		Assert::same('ws', $configuration->product());
		Assert::same('1.0.0', $configuration->version());
		Assert::same('BulkGate Plugin Test Shop', $configuration->name());
	}
}

(new ConfigurationWordpressTest())->run();
