<?php declare(strict_types=1);

namespace BulkGate\WooSms\Eshop;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{Eshop\Language};
use function woosms_load_languages;

class LanguageWordpress implements Language
{
    public function load(): array
    {
        return woosms_load_languages();
    }
}
