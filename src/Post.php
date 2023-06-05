<?php

namespace BulkGate\WooSms;

/**
 * @author Lukáš Piják 2020 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\Strict;

class Post
{
	use Strict;

	/**
	 * @var array<array-key, mixed>
	 */
	private static array $post = [];


	/**
	 * @param string $name
	 * @param mixed $default
	 * @param array $skip
	 * @return array|null|string
	 */
	public static function get(string $name, $default = null, array $skip = [])
	{
		if (!isset(self::$post[$name]))
		{
			self::$post[$name] = self::sanitize($_POST[$name] ?? $default, $skip);
		}

		return self::$post[$name];
	}


	/**
	 * @param $array_name
	 * @param $name
	 * @param null $default
	 * @return mixed|null
	 */
	public static function getFromArray($array_name, $name, $default = null)
	{
		$array = self::get($array_name);

		if (is_array($array) && isset($array[$name]))
		{
			return $array[$name];
		}
		return $default;
	}


	/**
	 * @param mixed $data
	 * @param list<string> $skip
	 * @return array<array-key, mixed>|null|string
	 */
	private static function sanitize($data, array $skip = [])
	{
		if (is_array($data))
		{
			$output = [];
			foreach ($data as $key => $item)
			{
				$output[sanitize_text_field($key)] = is_array($item) ?
					self::sanitize($item) : (
					in_array($key, $skip) ?
						wp_check_invalid_utf8(stripslashes($item)) :
						sanitize_text_field($item)
					);
			}
			return $output;
		}
		else if (is_scalar($data))
		{
			return sanitize_text_field($data);
		}
		return null;
	}
}
