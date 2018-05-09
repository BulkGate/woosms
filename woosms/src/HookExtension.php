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
