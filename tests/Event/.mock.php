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


function wc_get_order_statuses(): array
{
	return [
		'wc-pending'    => 'Pending payment',
		'wc-processing' => 'Processing',
		'wc-on-hold'    => 'On hold',
		'wc-completed'  => 'Completed',
	];
}

function current_user_can($capability, ...$args): bool
{
	return $capability === 'manage_options';
}


function wp_verify_nonce($capability, ...$args): bool
{
	return $capability === 'nonce_token';
}


function wp_die(): void
{
	throw new Exception('wp_die() called');
}
