<?php declare(strict_types=1);

namespace BulkGate\WooSms\Event;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\{WooSms\DI\Factory, Plugin\Event\Asynchronous, Plugin\Strict, Plugin\Event\Dispatcher, Plugin\Settings\Settings};
use function add_action, add_filter, get_query_var, wp_enqueue_script, wp_get_script_tag, array_merge, header, http_build_query;

class AssetDispatcher
{
	use Strict;

	private const QueryVar = 'bulkgate-asynchronous';

	private const Handle = 'bulkgate-asynchronous-asset';

	public static function init(): void
	{
		add_action( 'init', function (): void
		{
			if ((Factory::get()->getByClass(Settings::class)->load('main:dispatcher') ?? Dispatcher::$default_dispatcher) === Dispatcher::Asset)
			{
				add_filter('script_loader_tag', fn (string $tag, string $handle, string $src): string => $handle === self::Handle ? wp_get_script_tag(['src' => $src, 'id' => "$handle-js", 'async' => true]) : $tag, 10, 3);
				wp_enqueue_script(self::Handle, '/?' . http_build_query([self::QueryVar => Dispatcher::Asset]), [], null);
			}
		});

		add_filter('query_vars', fn (array $query_vars) => array_merge($query_vars, [self::QueryVar]));

		add_action('template_redirect', function (): void
		{
			if (get_query_var(self::QueryVar) === Dispatcher::Asset)
			{
				header('Content-Type: application/javascript');
				header('Cache-Control: no-store');

				$di = Factory::get();

				$settings = $di->getByClass(Settings::class);

				if (($settings->load('main:dispatcher') ?? Dispatcher::$default_dispatcher) === Dispatcher::Asset)
				{
					$count = $di->getByClass(Asynchronous::class)->run(max(5, (int) ($settings->load('main:cron-limit') ?? 10)));

					echo "// Asynchronous task consumer has processed $count tasks";
				}
				else
				{
					echo "// Asynchronous task consumer is disabled";
				}
				exit;
			}
		}, 15);
	}
}
