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
        /** @example PSEUDO CODE

        $result = $database->execute('SELECT `tracking_number` FROM `order` WHERE order_id = "'.$database->escape($variables->get('order_id')).'"');

        if($result->getNumRows())
        {
            $row = $result->getRow();

            $variables->set('tracking_number', $row['tracking_number']);
        }

        */
    }
}
