<?php declare(strict_types=1);

namespace BulkGate\WooSms\Utils;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{Strict, Utils\Escape as PluginEscape};
use function esc_html, esc_url, esc_attr, json_encode, str_replace;
use const JSON_UNESCAPED_UNICODE;

class Escape implements PluginEscape
{
	use Strict;

	public static function html(string $s): string
	{
		return esc_html($s);
	}


	/**
	 * @param mixed $s
	 */
	public static function js($s): string
	{
		return str_replace(
			["\xe2\x80\xa8", "\xe2\x80\xa9", ']]>', '<!'],
			['\u2028', '\u2029', ']]\x3E', '\x3C!'],
			json_encode($s, JSON_UNESCAPED_UNICODE) ?: ''
		);
	}


	public static function url(string $s): string
	{
		return esc_url($s);
	}


	public static function htmlAttr(string $s, bool $double = true): string
	{
		return esc_attr($s);
	}
}
