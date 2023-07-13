<?php declare(strict_types=1);

namespace BulkGate\WooSms\Event;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\Strict;
use function array_merge, is_string, preg_replace, wp_safe_redirect;

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
				$target = preg_replace('~[^a-z/-_]~', '', $target);

				if (!is_string($target) || $target === '')
				{
					$target = 'dashboard';
				}

				wp_safe_redirect(admin_url('admin.php?page=bulkgate#/') . $target);
				exit;
			}
		}, 20);
	}
}
