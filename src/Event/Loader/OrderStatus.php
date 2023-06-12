<?php declare(strict_types=1);

namespace BulkGate\WooSms\Event\Loader;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{Event\Variables, Strict, Event\DataLoader};
use function function_exists, str_replace, wc_get_order_statuses;

class OrderStatus implements DataLoader
{
	use Strict;

	private ?array $statuses = null;

	public function load(Variables $variables, array $parameters = []): void
	{
		if (function_exists('wc_get_order_statuses') && isset($variables['order_status_id']))
		{
			$this->statuses ??= wc_get_order_statuses();

			$variables['order_status_id'] = 'wc-' . str_replace('wc-', '', $variables['order_status_id'] ?? 'unknown');
			$variables['order_status'] = $this->statuses['wc-' . ($variables['order_status_id'])] ?? $variables['order_status_id'];

			if (isset($variables['order_status_id_from']))
			{
				$variables['order_status_id_from'] = 'wc-' . str_replace('wc-', '', $variables['order_status_id_from'] ?? 'unknown');
				$variables['order_status_from'] = $this->statuses[$variables['order_status_id_from']] ?? $variables['order_status_id_from'];
			}

		}
	}
}
