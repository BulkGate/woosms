<?php declare(strict_types=1);

namespace BulkGate\WooSms\Eshop\Test;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use Tester\{Assert, TestCase};
use BulkGate\WooSms\Eshop\LanguageWordpress;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/.mock-language-default.php';

/**
 * @testCase
 */
class LanguageWordpressDefaultTest extends TestCase
{
	public function testBase(): void
	{
		$language = new LanguageWordpress();

		Assert::same(['en_US' => 'Default'], $language->load());

		Assert::same('en_US', $language->get(10));
	}
}

(new LanguageWordpressDefaultTest())->run();
