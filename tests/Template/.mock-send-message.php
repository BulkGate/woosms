<?php declare(strict_types=1);

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

class WC_Order
{
}

class WC_Meta
{
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


function wp_print_inline_script_tag(string $javascript, array $attributes = []): void
{
	$GLOBALS['script'][] = $javascript;
}


function wp_print_script_tag(array $attributes): void
{
	$GLOBALS['script'][] = $attributes;
}


function esc_url(string $url, ?string $protocols = null, string $_context = 'display'): string
{
	return "|$url|";
}
