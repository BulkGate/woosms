<?php declare(strict_types=1);

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

function add_action(string $hook_name, callable $callback, int $priority = 10, int $accepted_args = 1): void
{
	$GLOBALS['hook_callback']["action_$hook_name"] = $callback;
}


function has_filter(string $hook_name, bool $callback = false): bool
{
	return $hook_name === 'run_woosms_hook_changeOrderStatusHook';
}


function apply_filters(string $hook_name, $value, ...$args): bool
{
	return $hook_name === 'run_woosms_hook_changeOrderStatusHook';
}


class WC_Product
{
	public function get_id(): int
	{
		return 1;
	}
}