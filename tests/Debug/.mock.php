<?php declare(strict_types=1);

namespace BulkGate\WooSms\Debug;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

/**
 * @param string $filename
 * @return false|string
 */
function file_get_contents(string $filename)
{
	if ($filename === 'https://portal.bulkgate.com/api/welcome')
	{
		return '{"message":"BulkGate API"}';
	}
	return false;
}
