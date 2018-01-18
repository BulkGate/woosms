<?php
namespace BulkGate\WooSms;

use BulkGate\Extensions;
use BulkGate\Extensions\Hook;
use BulkGate\Extensions\Database;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */
class HookExtension extends Extensions\SmartObject implements Hook\IExtension
{
    public function extend(Database\IDatabase $database, Hook\Variables $variables)
    {
    }
}
