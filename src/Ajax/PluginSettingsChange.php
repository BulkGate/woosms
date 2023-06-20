<?php declare(strict_types=1);

namespace BulkGate\WooSms\Ajax;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{Settings\Synchronizer, Strict, Settings\Settings as SettingsPlugin};
use function _PHPStan_f6e65bd66\RingCentral\Psr7\str;
use function is_scalar;

class PluginSettingsChange
{
	use Strict;

	private SettingsPlugin $settings;

	private Synchronizer $synchronizer;

	public function __construct(SettingsPlugin $settings, Synchronizer $synchronizer)
	{
		$this->settings = $settings;
		$this->synchronizer = $synchronizer;
	}


	/**
	 * @param array<array-key, mixed> $unsafe_post_data
	 * @return array{data: array{layout: array{server: array{application_settings: array<string, string>}}}}
	 */
	public function run(array $unsafe_post_data = []): array
	{
		$output = [];

		$this->change('dispatcher', $unsafe_post_data, $output);
		$this->change('synchronization', $unsafe_post_data, $output);
		$this->change('language', $unsafe_post_data, $output);
		$this->change('language_mutation', $unsafe_post_data, $output, 'bool');
		$this->change('delete_db', $unsafe_post_data, $output, 'bool');

		$this->synchronizer->synchronize(true);

		return ['data' => ['layout' => ['server' => ['application_settings' => $output]]]];
	}


	/**
	 * @param array<array-key, mixed> $unsafe_data
	 * @param array<array-key, string> $output
	 */
	private function change(string $key, array $unsafe_data, array &$output, string $type = 'string'): void
	{
		if (isset($unsafe_data[$key]) && is_scalar($unsafe_data[$key]))
		{
			$this->settings->set("main:$key", $output[$key] = sanitize_text_field((string) $unsafe_data[$key]), ['type' => $type]);
		}
	}
}
