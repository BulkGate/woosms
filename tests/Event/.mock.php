<?php declare(strict_types=1);

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

function has_filter(string $hook_name, bool $callback = false): bool
{
	return true;
}


/**
 * @param mixed $value
 * @param mixed ...$args
 */
function apply_filters(string $hook_name, $value, ...$args): bool
{
	return $hook_name === 'run_bulkgate_hook_test';
}