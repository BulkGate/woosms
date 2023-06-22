<?php declare(strict_types=1);

namespace BulkGate\WooSms\Test;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use Tester\{Assert, TestCase};
use BulkGate\WooSms\Utils\Escape;
use function file_exists;

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../src/debug.default.php';

/**
 * @testCase
 */
class DebugTest extends TestCase
{
	public function testEscape(): void
	{
		Assert::same('https://portal.bulkgate.com', BulkGateDebugUrl);
	}
}

(new DebugTest())->run();
