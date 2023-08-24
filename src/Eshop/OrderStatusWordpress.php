<?php declare(strict_types=1);

namespace BulkGate\WooSms\Eshop;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{Eshop\OrderStatus, Strict};
use function wc_get_order_statuses;

class OrderStatusWordpress implements OrderStatus
{
	use Strict;

    public function load(): array
    {
        return wc_get_order_statuses();
    }
}
