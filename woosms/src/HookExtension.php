<?php
namespace BulkGate\WooSms;

use BulkGate\Extensions;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class HookExtension extends Extensions\Strict implements Extensions\Hook\IExtension
{
    public function extend(Extensions\Database\IDatabase $database, Extensions\Hook\Variables $variables)
    {
        do_action('woosms_extends_variables', $variables, $database);
    }
}
