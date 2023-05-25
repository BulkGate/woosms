<?php declare(strict_types=1);

namespace BulkGate\WooSms\Eshop;


use BulkGate\Plugin\{Eshop\ReturnStatus};


class ReturnStatusWordpress implements ReturnStatus
{
    public function load(): array
    {
        return []; //todo: implement. Nevim co to vlastne ma byt.. je tim snad mysleno toto -> https://woocommerce.com/document/warranty-and-returns/ ??
    }
}
