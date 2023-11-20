<?php declare(strict_types=1);

namespace BulkGate\WooSms\Utils;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{Strict};
use function current_user_can, wp_verify_nonce, wp_die;

class Authorization
{
	use Strict;

	public static function check(?string $nonce): bool
    {

        if (current_user_can('manage_options') && wp_verify_nonce($nonce) !== false)
        {
            return true;
        }
        else
        {
            wp_die("", 403);
        }
    }
}
