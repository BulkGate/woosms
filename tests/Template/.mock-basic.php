<?php declare(strict_types=1);

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

function admin_url(): string
{
	return 'http://localhost/wp-admin/';
}


function is_ssl(): bool
{
	return false;
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


function esc_html(string $html): string
{
	return htmlspecialchars($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}


function esc_attr(string $attr): string
{
	return "?_{$attr}_?";
}
