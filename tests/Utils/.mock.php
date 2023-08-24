<?php declare(strict_types=1);


/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

function admin_url(string $path = '', string $scheme = 'admin'): string
{
	return "$scheme://admin/$path";
}

function esc_html(string $html): string
{
	return htmlspecialchars($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function esc_attr(string $attr): string
{
	return "?_{$attr}_?";
}

function esc_url(string $url): string
{
	return "\$_{$url}_$";
}
