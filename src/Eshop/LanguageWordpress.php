<?php declare(strict_types=1);

namespace BulkGate\WooSms\Eshop;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{Eshop\Language};
use function get_locale, defined, is_plugin_active, apply_filters;

class LanguageWordpress implements Language
{
	public function load(): array
	{
		$output = [];

		if ($this->hasMultiLanguageSupport())
		{
			$languages = apply_filters('wpml_active_languages', null, 'orderby=id&order=desc');

			foreach ($languages as $iso => $item)
			{
				$output[$iso] = $item['native_name'] ?? $iso;
			}
		}
		else
		{
			$output = [get_locale() => 'Default'];
		}

		return $output;
	}


	public function get(?int $id = null): string
	{
		if ($this->hasMultiLanguageSupport() && defined('ICL_LANGUAGE_CODE'))
		{
			return $id === null ? ICL_LANGUAGE_CODE : ((string) get_post_meta($id, 'wpml_language', true) ?: ICL_LANGUAGE_CODE);
		}
		else
		{
			return get_locale();
		}
	}


	public function hasMultiLanguageSupport(): bool
	{
		return is_plugin_active('sitepress-multilingual-cms-master/sitepress.php') || is_plugin_active('sitepress-multilingual-cms/sitepress.php');
	}
}
