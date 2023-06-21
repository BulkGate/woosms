<?php declare(strict_types=1);

namespace BulkGate\WooSms\Utils\Test;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use Tester\{Assert, TestCase};
use BulkGate\WooSms\Utils\Escape;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/.mock.php';

/**
 * @testCase
 */
class EscapeTest extends TestCase
{
	public function testEscape(): void
	{
		Assert::same('&lt;a&gt;', Escape::html('<a>'));
		Assert::same('"{}"', Escape::js('{}'));
		Assert::same('$_url_$', Escape::url('url'));
		Assert::same('?_javascript:alert(1)_?', Escape::htmlAttr('javascript:alert(1)'));
	}
}

(new EscapeTest())->run();
