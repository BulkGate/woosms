<?php declare(strict_types=1);

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

$GLOBALS['order_form_callback'] = [];

function add_action(string $hook_name, callable $callback, int $priority = 10, int $accepted_args = 1): void
{
	$GLOBALS['order_form_callback']["action_$hook_name"] = $callback;
}

$GLOBALS['order_form_form'] = [];

function woocommerce_form_field(string $key, array $args, $value = null): void
{
	$GLOBALS['order_form_form'][$key] = [$args, $value];
}


function esc_attr(string $s): string
{
	return ":attr:$s:";
}


function esc_html(string $s): string
{
	return ":html:$s:";
}

