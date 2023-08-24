<?php declare(strict_types=1);

namespace BulkGate\WooSms\Utils;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\WooSms\DI\Factory;
use BulkGate\Plugin\{DI\MissingServiceException, Settings\Settings, Strict};
use function basename, dirname, array_merge, admin_url, array_unshift;

class Meta
{
	use Strict;

	/**
	 * @param array<string, string> $links
	 * @return array<string, string>
	 */
	public static function links(array $links, string $file): array
	{
		/**
		 * @phpstan-ignore-next-line
		 */
		if (basename(dirname($file)) !== BULKGATE_PLUGIN_DIR)
		{
			return $links;
		}

		return array_merge($links, [
			'help_desk' => '<a href="https://help.bulkgate.com/en/" aria-label="Help Desk">Help Desk</a>',
			'price_list' => '<a href="https://www.bulkgate.com/en/pricing/" aria-label="Price List">Price List</a>',
			'youtube_channel' => '<a href="https://www.youtube.com/channel/UCGD7ndC4z2NfuWUrS-DGELg" aria-label="YouTube Channel">YouTube Channel</a>',
			'contact_us' => '<a href="https://www.bulkgate.com/en/contact-us/" aria-label="Contact us">Contact us</a>',
			'api' => '<a href="https://www.bulkgate.com/en/developers/sms-api/" aria-label="API">API</a>',
			'github' => '<a href="https://github.com/bulkgate/woosms" aria-label="GitHub">GitHub</a>',
			'terms_of_service' => '<a href="https://portal.bulkgate.com/page/terms-and-conditions" aria-label="Terms of Service">Terms of Service</a>',
			'privacy_policy' => '<a href="https://portal.bulkgate.com/page/privacy-policy" aria-label="Privacy Policy">Privacy Policy</a>',
		]);
	}


	/**
	 * @param array<array-key, string> $links
	 * @return array<array-key, string>
	 * @throws MissingServiceException
	 */
	public static function settingsLink(array $links, string $file): array
	{
		/**
		 * @phpstan-ignore-next-line
		 */
		if (basename(dirname($file)) === BULKGATE_PLUGIN_DIR)
		{
			array_unshift($links, '<a href="' . Escape::url(admin_url('tools.php?page=bulkgate-debug')) . '">Debug</a>');

			if (Factory::get()->getByClass(Settings::class)->load('static:application_token') === null)
			{
				array_unshift($links, '<a href="' . Escape::url(admin_url('admin.php?page=bulkgate#/sign/in')) . '">Log In</a>');
			}
		}
		return $links;
	}
}
