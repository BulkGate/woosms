<?php declare(strict_types=1);

namespace BulkGate\WooSms\Utils\Test;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use Tester\{Assert, TestCase};
use BulkGate\WooSms\Utils\Logo;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/.mock.php';

/**
 * @testCase
 */
class LogoTest extends TestCase
{
	public function testEscape(): void
	{
		Assert::match('~^data:image/svg\+xml;base64,.+~', Logo::Menu);
	}
}

(new LogoTest())->run();
