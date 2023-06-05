<?php declare(strict_types=1);

namespace BulkGate\WooSms\Utils;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\WooSms\DI\Factory;
use BulkGate\Plugin\{DI\MissingParameterException, DI\MissingServiceException, Settings\Settings, Strict};
use function basename, dirname, array_merge, esc_url, admin_url, array_unshift;

class Meta
{
	use Strict;

	/**
	 * @param array<string, string> $links
	 * @return array<string, string>
	 */
	public static function links(array $links, string $file): array
	{
		if (basename(dirname($file)) !== WOOSMS_DIR)
		{
			return $links;
		}

		return array_merge($links, [
			'help_desk' => '<a href="https://help.bulkgate.com/en/" aria-label="Help Desk">Help Desk</a>',
			'price_list' => '<a href="https://www.bulkgate.com/en/pricing/" aria-label="Price List">Price List</a>',
			'youtube_channel' => '<a href="https://www.youtube.com/channel/UCGD7ndC4z2NfuWUrS-DGELg" aria-label="YouTube Channel">YouTube Channel</a>',
			'contact_us' => '<a href="https://www.bulkgate.com/en/contact-us/" aria-label="Contact us">Contact us</a>',
			'api' => '<a href="https://www.bulkgate.com/en/developers/sms-api/" aria-label="API">API</a>',
		]);
	}


	/**
	 * @throws MissingServiceException|MissingParameterException
	 */
	public static function settingsLink(array $links, string $file): array
	{
		if (basename(dirname($file)) === WOOSMS_DIR)
		{
			if (Factory::get()->getByClass(Settings::class)->load('static:application_token') ?? null)
			{
				$settings_link = '<a href="' . esc_url(admin_url("admin.php?page=bulkgate#/module-settings/default")) . '">Settings</a>';
			}
			else
			{
				$settings_link = '<a href="' . esc_url(admin_url("admin.php?page=bulkgate#/sign/in")) . '">Log In</a>';
			}

			array_unshift($links, $settings_link);
		}
		return $links;
	}
}
