<?php declare(strict_types=1);

namespace BulkGate\WooSms\Eshop;

use BulkGate\Plugin\{
    Eshop\OrderStatus
};
use function wc_get_order_statuses;

class OrderStatusWordpress implements OrderStatus
{
    public function load(): array
    {
        return (array) wc_get_order_statuses();
    }
}
