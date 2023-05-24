<?php declare(strict_types=1);

namespace BulkGate\WooSms\Eshop;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{Strict, Eshop\Configuration as EshopConfiguration};
use function array_change_key_case;

class ConfigurationWordpress implements EshopConfiguration
{
	use Strict;

	/**
	 * @var array<string, string>
	 */
	private array $plugin_data;

	private string $site_url;

	public function __construct(array $plugin_data, string $site_url)
	{
		$this->plugin_data = array_change_key_case($plugin_data);
		$this->site_url = $site_url;
	}


	public function url(): string
	{
		return $this->site_url;
	}


	public function product(): string
	{
		return 'ws';
	}


	public function version(): string
	{
		return $this->plugin_data['version'] ?? 'unknown';
	}
}
