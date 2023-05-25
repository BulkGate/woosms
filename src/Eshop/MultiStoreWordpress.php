<?php declare(strict_types=1);

namespace BulkGate\WooSms\Eshop;


use BulkGate\Plugin\{Eshop\MultiStore};

class MultiStoreWordpress implements MultiStore
{
    public function load(): array
    {
        return []; //todo: implement. Nevim jestli ve wordpressu je vubec multi-store
    }
}
