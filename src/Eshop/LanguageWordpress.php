<?php declare(strict_types=1);

namespace BulkGate\WooSms\Eshop;


use BulkGate\Plugin\{Eshop\Language};
use function woosms_load_languages;

class LanguageWordpress implements Language
{
    public function load(): array
    {
        return woosms_load_languages();
    }
}
