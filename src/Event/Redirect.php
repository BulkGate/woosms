<?php declare(strict_types=1);

namespace BulkGate\WooSms\Event;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\Strict;
use function array_merge, preg_replace, wp_safe_redirect;

class Redirect
{
	use Strict;

	public const QueryVar = 'bulkgate-redirect';

	public static function init(): void
	{
		add_filter('query_vars', fn (array $query_vars) => array_merge($query_vars, [self::QueryVar]));

		add_action('template_redirect', function (): void
		{
			$target = get_query_var(self::QueryVar);

			if (!empty($target))
			{
				wp_safe_redirect(admin_url('admin.php?page=bulkgate#/') . preg_replace('~[^a-z/-_]~', '', $target));
				exit;
			}
		}, 20);
	}
}
