<?php declare(strict_types=1);

namespace BulkGate\WooSms\Event\Loader;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use WC_Product;
use BulkGate\Plugin\{Event\Variables, Strict, Event\DataLoader};

class Product implements DataLoader
{
	use Strict;


	public function load(Variables $variables, array $parameters = []): void
	{
		if (!isset($variables['product_id']))
		{
			return;
		}

		$product = isset($parameters['product']) && $parameters['product'] instanceof WC_Product ? $parameters['product'] : new WC_Product((int) $variables['product_id']);

		$variables['product_quantity'] = (int) $product->get_stock_quantity();
		$variables['product_name'] = $product->get_name();
		$variables['product_ref'] = $product->get_sku();
		$variables['product_price'] = $product->get_price();
	}
}
