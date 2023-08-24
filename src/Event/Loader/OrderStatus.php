<?php declare(strict_types=1);

namespace BulkGate\WooSms\Event\Loader;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\{WooSms\Event\Helpers, Plugin\Event\Variables, Plugin\Strict, Plugin\Event\DataLoader};
use function function_exists;

class OrderStatus implements DataLoader
{
	use Strict;

	public function load(Variables $variables, array $parameters = []): void
	{
		if (function_exists('wc_get_order_statuses') && isset($variables['order_status_id']))
		{
			$order_status_id = $variables['order_status_id'] ?? 'unknown';

			$variables['order_status'] = Helpers::resolveOrderStatus($order_status_id);
			$variables['order_status_id'] = "wc-$order_status_id";

			if (isset($variables['order_status_id_from']))
			{
				$order_status_id_from = $variables['order_status_id_from'] ?? 'unknown';

				$variables['order_status_from'] = Helpers::resolveOrderStatus($order_status_id_from);
				$variables['order_status_id_from'] = "wc-$order_status_id_from";
			}
		}
	}
}
