<?php declare(strict_types=1);

namespace BulkGate\WooSms\Debug\Test;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use Mockery;
use Tester\{Assert, DomQuery, TestCase};
use BulkGate\{Plugin\Debug\Logger, Plugin\Debug\Requirements, WooSms\Debug\Page, WooSms\Utils\Escape};
use function ob_end_clean;
use function ob_get_clean;
use function ob_start;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/.mock.php';

/**
 * @testCase
 */
class LogoTest extends TestCase
{
	public function testEscape(): void
	{
		$GLOBALS['wp_version'] = '6.2.2';

		$escape = Mockery::mock('overload:' . Escape::class);
		$escape->shouldReceive('htmlAttr')->andReturnUsing(function (string $value) {
			return "attr:|$value|";
		});
		$escape->shouldReceive('html')->andReturnUsing(function (string $value) {
			return "html:|$value|";
		});
		$requirements = Mockery::mock(Requirements::class);
		$requirements->shouldReceive('same')->with('{"message":"BulkGate API"}', '{"message":"BulkGate API"}', 'Api Connection')->twice()->andReturn(['passed' => true, 'description' => 'xx', 'color' => 'limegreen', 'error' => null]);
		$requirements->shouldReceive('same')->with(true, true, 'WordPress ver. >= 5.7')->twice()->andReturn(['passed' => true, 'description' => 'xx', 'color' => 'limegreen', 'error' => null]);
		$requirements->shouldReceive('run')->with([
			['passed' => true, 'description' => 'xx', 'color' => 'limegreen', 'error' => null],
			['passed' => true, 'description' => 'xx', 'color' => 'limegreen', 'error' => null]
		])->twice()->andReturn([
			['passed' => true, 'description' => 'xx', 'color' => 'limegreen', 'error' => null],
			['passed' => true, 'description' => 'xx', 'color' => 'limegreen', 'error' => null],
			['passed' => true, 'description' => 'xx', 'color' => 'limegreen', 'error' => null],
		]);

		$logger = Mockery::mock(Logger::class);
		$logger->shouldReceive('getList')->once()->andReturn([
			['message' => 'BulkGate API', 'created' => 545],
			['message' => 'BulkGate API', 'created' => 464],
		]);
		$logger->shouldReceive('getList')->once()->andReturn([]);

		$dom = DomQuery::fromHtml($this->capture(fn () => Page::print($logger, $requirements)));

		Assert::true($dom->has('#bulkgate-requirements-table'));
		Assert::true($dom->has('#bulkgate-log-table'));

		$dom = DomQuery::fromHtml($this->capture(fn () => Page::print($logger, $requirements)));

		Assert::true($dom->has('#bulkgate-requirements-table'));
		Assert::false($dom->has('#bulkgate-log-table'));

		Mockery::close();
	}


	private function capture(callable $function): string
	{
		ob_start(function () {});
		try
		{
			$function();
			return ob_get_clean();
		}
		catch (\Throwable $e)
		{
			ob_end_clean();
			throw $e;
		}
	}
}

(new LogoTest())->run();
