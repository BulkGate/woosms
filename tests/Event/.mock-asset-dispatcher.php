<?php declare(strict_types=1);

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

$GLOBALS['asset_callback'] = [];
$GLOBALS['asset_style'] = [];

function add_filter(string $hook_name, callable $callback, int $priority = 10, int $accepted_args = 1): void
{
	$GLOBALS['asset_callback']["filter_$hook_name"] = $callback;
}


function add_action(string $hook_name, callable $callback, int $priority = 10, int $accepted_args = 1): void
{
	$GLOBALS['asset_callback']["action_$hook_name"] = $callback;
}


function wp_enqueue_script(string $handle, string $src = '', array $deps = [], $ver = false, bool $in_footer = false): void
{
	$GLOBALS['asset_style'][$handle] = [$src, $deps, $ver, $in_footer];
}


function wp_get_script_tag(array $parameters): string
{
	return json_encode($parameters);
}


function get_query_var(string $query_var, $default_value = null): string
{
	return $query_var === 'bulkgate-asynchronous' ? 'asset' : $default_value;
}