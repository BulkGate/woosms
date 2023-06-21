<?php declare(strict_types=1);

namespace BulkGate\WooSms\Event;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\{Plugin\DI\MissingServiceException, Plugin\Event\Dispatcher, Plugin\Strict, WooSms\DI\Factory};
use function apply_filters, has_filter;

class Helpers
{
	use Strict;


	public static function dispatch(string $name, callable $callback): callable
	{
		return function (...$parameters) use ($name, $callback): void
		{
			$run_hook = true;

			if (has_filter("run_bulkgate_hook_$name"))
			{
				$run_hook = apply_filters("run_bulkgate_hook_$name", ...$parameters);
			}

			if ($run_hook)
			{
				try
				{
					$callback(Factory::get()->getByClass(Dispatcher::class), ...$parameters);
				}
				catch (MissingServiceException $e)
				{
				}
			}
		};
	}
}
