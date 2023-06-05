<?php declare(strict_types=1);

namespace BulkGate\WooSms\Eshop;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{Settings\Settings, Strict, Eshop\Configuration as EshopConfiguration};
use function array_change_key_case;

class ConfigurationWordpress implements EshopConfiguration
{
	use Strict;

	/**
	 * @var array<string, string>
	 */
	private array $plugin_data;

	private string $site_url;

	private string $site_name;

    private Settings $settings;

    private array $info = [
        'store' => 'WooCommerce',
        'store_version' => '2.2.x +',
        'name' => 'BulkGate SMS Plugin',
        'url' => 'https://www.bulkgate.com/en/integrations/sms-plugin-for-woocommerce/',
        'developer' => 'BulkGate',
        'developer_url' => 'https://www.bulkgate.com/',
        'description' => 'BulkGate SMS plugin extends your WooCommerce store capabilities and creates new opportunities for your business. You can promote your products and sales via personalized bulk SMS. Make your customers happy by notifying them about order status change via SMS notifications. Receive an SMS whenever a new order is placed, a product is out of stock, and much more.',
    ];

	public function __construct(array $plugin_data, string $site_url, string $site_name, Settings $settings)
	{
		$this->plugin_data = array_change_key_case($plugin_data);
		$this->site_url = $site_url;
		$this->site_name = $site_name;
        $this->settings = $settings;
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


	public function name(): string
	{
		return $this->site_name;
	}


    public function info(): array
    {
        return array_merge([
            'version' => $this->version(),
            'application_product' => $this->product(),
            'application_id' => $this->settings->load('static:application_id') ?? -1,
            'delete_db' => $this->settings->load('main:delete_db') ?? 0,
            'language_mutation' => $this->settings->load('main:language_mutation') ?? 0
        ], $this->info);
    }
}
