<?php declare(strict_types=1);

namespace BulkGate\WooSms\Event\Test;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use Mockery;
use Tester\{Assert, TestCase};
use BulkGate\WooSms\Event\Redirect;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/.mock-redirect.php';

/**
 * @testCase
 */
class RedirectTest extends TestCase
{
	public function testBase(): void
	{
		Redirect::init();

		$callbacks = $GLOBALS['redirect_callback'];

		Assert::count(2, $callbacks);

		// ENABLE PARAMETER
		Assert::same(['test', 'bulkgate-redirect'], $callbacks['filter_query_vars'](['test']));

		// DISPATCH ASSET
		$callbacks['action_template_redirect']();
	}


	public function tearDown(): void
	{
		Mockery::close();
	}
}

(new RedirectTest())->run();
