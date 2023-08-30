<?php declare(strict_types=1);

namespace BulkGate\WooSms\Event;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\{Plugin\Event\Dispatcher, WooSms\DI\Factory, Plugin\Eshop\EshopSynchronizer, Plugin\Event\Asynchronous, Plugin\Settings\Settings, Plugin\Strict};

class Cron
{
	use Strict;

	private const IntervalSending = 'bulkgate_send_interval';
	private const IntervalSynchronization = 'bulkgate_synchronize_interval';
	private const HookSending = 'bulkgate_sending';
	private const HookSynchronization = 'bulkgate_synchronize';


	public static function init(): void
	{
		add_filter('cron_schedules', function (array $schedules): array
		{
			$schedules[self::IntervalSending] ??= [
				'interval' => 60,
				'display' => __('BulkGate Sending Interval')
			];

			$schedules[self::IntervalSynchronization] ??= [
				'interval' => 3_600,
				'display' => __('BulkGate Synchronize Interval')
			];

			return $schedules;
		});

		add_action('init', function (): void
		{
			if (!wp_next_scheduled(self::HookSending))
			{
				wp_schedule_event(time(), self::IntervalSending, self::HookSending);
			}

			if (!wp_next_scheduled(self::HookSynchronization))
			{
				wp_schedule_event(time(), self::IntervalSynchronization, self::HookSynchronization);
			}
		});

		add_action(self::HookSending, function (): void
		{
			$di = Factory::get();

			$settings = $di->getByClass(Settings::class);

			if ($settings->load('main:dispatcher') === Dispatcher::Cron)
			{
				$settings->set('main:cron-run-before', date('Y-m-d H:i:s'), ['type' => 'string']);

				$asynchronous = $di->getByClass(Asynchronous::class);

				$asynchronous->run(max(1, (int) ($settings->load('main:cron-limit') ?? 10)));

				$settings->set('main:cron-run', date('Y-m-d H:i:s'), ['type' => 'string']);
			}
		});

		add_action(self::HookSynchronization, fn () => Factory::get()->getByClass(EshopSynchronizer::class)->run());
	}
}
