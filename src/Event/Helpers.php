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


	/**
	 * @param array<string, string|null> $primary
	 * @param array<string, string|null> $secondary
	 */
	public static function address(string $key, array $primary, array $secondary): ?string
	{
		if (!empty($primary[$key]))
		{
			return $primary[$key];
		}
		else if (!empty($secondary[$key]))
		{
			return $secondary[$key];
		}
		return null;
	}


	/**
	 * @param array<string, string|null> $primary
	 * @param array<string, string|null> $secondary
	 */
	public static function joinStreet(string $street1, string $street2, array $primary, array $secondary): ?string
	{
		if (!empty($primary[$street1]))
		{
			return $primary[$street1] . (!empty($primary[$street2]) ? ', ' . $primary[$street2] : '');
		}
		else if (!empty($secondary[$street1]))
		{
			return $secondary[$street1] . (!empty($secondary[$street2]) ? ', ' . $secondary[$street2] : '');
		}
		return null;
	}


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
					/**
					 * @var Dispatcher $dispatcher
					 */
					$dispatcher = Factory::get()->getByClass(Dispatcher::class);

					$callback($dispatcher, ...$parameters);
				}
				catch (MissingServiceException $e)
				{
				}
			}
		};
	}
}
