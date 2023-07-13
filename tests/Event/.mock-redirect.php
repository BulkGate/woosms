<?php declare(strict_types=1);

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

$GLOBALS['redirect_callback'] = [];


function add_filter(string $hook_name, callable $callback, int $priority = 10, int $accepted_args = 1): void
{
	$GLOBALS['redirect_callback']["filter_$hook_name"] = $callback;
}


function add_action(string $hook_name, callable $callback, int $priority = 10, int $accepted_args = 1): void
{
	$GLOBALS['redirect_callback']["action_$hook_name"] = $callback;
}


function get_query_var(string $query_var, $default_value = null): string
{
	return $query_var === 'bulkgate-redirect' ? '/redirect' : $default_value;
}


function wp_safe_redirect(string $location, int $status = 302): void
{
}


function admin_url(string $path = '', string $scheme = 'admin'): string
{
	return "url/$path";
}