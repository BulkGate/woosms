<?php declare(strict_types=1);

namespace BulkGate\WooSms\Eshop;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{Strict, Eshop\ReturnStatus};

class ReturnStatusWordpress implements ReturnStatus
{
	use Strict;

    public function load(): array
    {
        return []; //todo: implement. Nevim co to vlastne ma byt.. je tim snad mysleno toto -> https://woocommerce.com/document/warranty-and-returns/ ??
    }
}
