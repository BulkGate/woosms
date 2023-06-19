<?php declare(strict_types=1);

namespace BulkGate\WooSms\Event\Loader\Test;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use Mockery;
use Tester\{Assert, TestCase};
use BulkGate\{Plugin\Database\Connection, Plugin\Event\Variables, WooSms\Event\Loader\Extension};

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/.mock.php';

/**
 * @testCase
 */
class ExtensionTest extends TestCase
{
	public function testBase(): void
	{
		$variables = new Variables(['test' => 'ok']);

		$loader = new Extension(Mockery::mock(Connection::class));

		$loader->load($variables);

		Assert::same([
			'test' => 'ok',
			'woosms_extends_variables' => 'ok',
			'woosms_extends_variables_db' => 'ok',
			'bulkgate_extends_variables' => 'ok',
			'bulkgate_extends_variables_db' => 'ok',
		], $variables->toArray());
	}
}

(new ExtensionTest())->run();
