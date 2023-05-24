<?php

namespace BulkGate\WooSms;

/**
 * @author Lukáš Piják 2020 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Extensions\Database\IDatabase;
use BulkGate\Extensions\Hook\IExtension;
use BulkGate\Extensions\Hook\Variables;
use BulkGate\Extensions\Strict;

class HookExtension extends Strict implements IExtension
{
    public function extend(IDatabase $database, Variables $variables)
    {
        do_action('woosms_extends_variables', $variables, $database);
    }
}
