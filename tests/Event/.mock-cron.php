<?php declare(strict_types=1);


/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

$GLOBALS['cron_callback'] = [];
$GLOBALS['cron_init'] = [];


function add_filter(string $hook_name, callable $callback, int $priority = 10, int $accepted_args = 1): void
{
	$GLOBALS['cron_callback']["filter_$hook_name"] = $callback;
}


function add_action(string $hook_name, callable $callback, int $priority = 10, int $accepted_args = 1): void
{
	$GLOBALS['cron_callback']["action_$hook_name"] = $callback;
}


function __(string $s): string
{
	return "~$s~";
}


function wp_next_scheduled(string $hook_name, array $args = []): int
{
	return 0;
}


function wp_schedule_event($timestamp, $recurrence, $hook, $args = array(), $wp_error = false )
{
	$GLOBALS['cron_init'][] = [$recurrence, $hook, $args, $wp_error];
}