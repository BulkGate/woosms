<?php declare(strict_types=1);

namespace BulkGate\WooSms\Eshop\Test;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use Tester\{Assert, TestCase};
use BulkGate\WooSms\Eshop\LanguageWordpress;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/.mock-language-wpml.php';

/**
 * @testCase
 */
class LanguageWordpressWpmlTest extends TestCase
{
	public function testBase(): void
	{
		$language = new LanguageWordpress();

		Assert::same(['cs' => 'Čeština', 'en' => 'English', 'de' => 'Deutsch', 'fr' => 'fr'], $language->load());

		Assert::same('cs', $language->get());
		Assert::same('fr', $language->get(10));
	}
}

(new LanguageWordpressWpmlTest())->run();
