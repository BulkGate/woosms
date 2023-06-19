<?php declare(strict_types=1);

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

define('ICL_LANGUAGE_CODE', 'cs');


function is_plugin_active(string $name): bool
{
	return $name === 'sitepress-multilingual-cms-master/sitepress.php' || $name === 'sitepress-multilingual-cms/sitepress.php';
}


/**
 * @param mixed $value
 * @param mixed ...$args
 * @return mixed
 */
function apply_filters(string $hook_name, $value, ...$args)
{
	if ($hook_name === 'wpml_active_languages')
	{
		return [
			'cs' => ['native_name' => 'Čeština'],
			'en' => ['native_name' => 'English'],
			'de' => ['native_name' => 'Deutsch'],
			'fr' => 'Français',
		];
	}
	return null;
}


/**
 * @return mixed
 */
function get_post_meta(int $post_id, string $key = '', bool $single = false)
{
	if ($post_id === 10 && $key === 'wpml_language')
	{
		return 'fr';
	}
	return null;
}