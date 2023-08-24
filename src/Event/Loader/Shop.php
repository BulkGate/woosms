<?php declare(strict_types=1);

namespace BulkGate\WooSms\Event\Loader;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{Eshop\Configuration, Eshop\Language, Event\Variables, Strict, Event\DataLoader};
use function get_option;

class Shop implements DataLoader
{
	use Strict;

	private Configuration $configuration;

	private Language $language;


	public function __construct(Configuration $configuration, Language $language)
	{
		$this->configuration = $configuration;
		$this->language = $language;
	}


	public function load(Variables $variables, array $parameters = []): void
	{
		$variables['shop_id'] = 0;
		$variables['shop_name'] = $this->configuration->name();
		$variables['shop_email'] = get_option('admin_email', '@');
		$variables['shop_domain'] = $this->configuration->url();
		$variables['shop_currency'] = get_option('woocommerce_currency', 'USD');

		if (!isset($variables['lang_id']))
		{
			$variables['lang_id'] = $this->language->get();
		}
	}
}
