<?php declare(strict_types=1);

namespace BulkGate\WooSms\Event;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\{Plugin\DI\MissingServiceException, Plugin\Event\Dispatcher, Plugin\Strict, WooSms\DI\Factory};
use function apply_filters, has_filter, str_replace;

class Helpers
{
	use Strict;

	/**
	 * @var array<string, string|int>
	 */
	private static array $last_used_hook = [];

	public static function dispatch(string $name, callable $callback, ?callable $key_evaluator = null): callable
	{
		return function (...$parameters) use ($name, $callback, $key_evaluator): void
		{
			$run_hook = true;

			if ($key_evaluator !== null)
			{
				$key = $key_evaluator(...$parameters);

				$run_hook = (self::$last_used_hook[$name] ?? null) !== $key;

				self::$last_used_hook[$name] = $key;
			}

			if ($run_hook && has_filter("run_bulkgate_hook_$name"))
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


	public static function resolveOrderStatus(string &$status): string
	{
		static $statuses;
		$statuses ??= wc_get_order_statuses();

		$status = str_replace('wc-', '', $status);

		return $statuses["wc-$status"] ?? $status;
	}
}
