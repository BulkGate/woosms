<?php declare(strict_types=1);

namespace BulkGate\WooSms\Eshop\Test;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use Tester\{Assert, TestCase};
use BulkGate\WooSms\Eshop\ReturnStatusWordpress;

require_once __DIR__ . '/../bootstrap.php';

/**
 * @testCase
 */
class ReturnStatusWordpressTest extends TestCase
{
	public function testBase(): void
	{
		$return = new ReturnStatusWordpress();

		Assert::same([], $return->load());
	}
}

(new ReturnStatusWordpressTest())->run();
