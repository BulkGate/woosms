<?php declare(strict_types=1);


/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

class WC_Order
{
}

$_POST['__bulkgate'] = ['email' => 'xxx@bulkgate.com', 'password' => 'P@ssw0rd'];


function add_action(string $hook_name, callable $callback, int $priority = 10, int $accepted_args = 1): void
{
	$GLOBALS['init_callback']["action_$hook_name"] = $callback;
}


function add_filter(string $hook_name, callable $callback, int $priority = 10, int $accepted_args = 1): void
{
	$GLOBALS['init_callback']["filter_$hook_name"] = $callback;
}

function add_management_page(string $page_title, string $menu_title, string $capability, string $menu_slug, callable $function = null, ?int $position = null): void
{
	$GLOBALS['pages']["page_$menu_slug"] = [$page_title, $menu_title, $capability, $menu_slug, $position];
	$GLOBALS['init_callback']["page_$menu_slug"] = $function;
}


function add_menu_page(string $page_title, string $menu_title, string $capability, string $menu_slug, callable $function = null, string $icon_url = '', int $position = null): void
{
	$GLOBALS['pages']["page_$menu_slug"] = [$page_title, $menu_title, $capability, $menu_slug, $icon_url, $position];
	$GLOBALS['init_callback']["page_$menu_slug"] = $function;
}


function add_meta_box(string $id, string $title, callable $callback, string $screen = null, string $context = 'advanced', string $priority = 'default', ?array $callback_args = null): void
{
	$GLOBALS['pages']["meta_$id"] = [$id, $title, $screen, $context, $priority, $callback_args];
	$GLOBALS['init_callback']["meta_box_$id"] = $callback;
}


function esc_attr(string $attr): string
{
	return "?_{$attr}_?";
}


function esc_html(string $html): string
{
	return htmlspecialchars($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}


function admin_url(string $path): string
{
	return "https://exmaple.com/$path";
}


function sanitize_text_field(string $text): string
{
	return "$$text$";
}

function current_user_can(): bool
{
    return true;
}

function wp_verify_nonce(): int
{
    return 1;
}

function wc_get_order(int $id): WC_Order
{
	return new WC_Order();
}
