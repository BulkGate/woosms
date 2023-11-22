<?php declare(strict_types=1);

namespace BulkGate\WooSms\Event;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\{Plugin\DI\MissingServiceException, Plugin\Event\Dispatcher, Plugin\Strict, WooSms\DI\Factory};
use function apply_filters, has_filter, str_replace, current_user_can, wp_die, wp_verify_nonce;

class Helpers
{
	use Strict;

	public const CrossSiteRequestForgerySecurityParameter = 'security';

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


	public static function resolveOrderStatus(string &$status): string
	{
		static $statuses;
		$statuses ??= wc_get_order_statuses();

		$status = str_replace('wc-', '', $status);

		return $statuses["wc-$status"] ?? $status;
	}


	public static function checkAccess(?string $nonce): bool
	{
		if (!current_user_can('manage_options') || wp_verify_nonce($nonce ?? '') === false)
		{
			wp_die('', 403);
            
            return false;
		}

		return true;
	}
}
