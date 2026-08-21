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
			'help_desk' => '<a href="https://help.bulkgate.com/en/" aria-label="' . esc_attr__('Help Desk','woosms-sms-module-for-woocommerce') . '">' . esc_html__('Help Desk','woosms-sms-module-for-woocommerce') . '</a>',
			'price_list' => '<a href="https://www.bulkgate.com/en/pricing/" aria-label="' . esc_attr__('Price List','woosms-sms-module-for-woocommerce') . '">' . esc_html__('Price List','woosms-sms-module-for-woocommerce') . '</a>',
			'youtube_channel' => '<a href="https://www.youtube.com/channel/UCGD7ndC4z2NfuWUrS-DGELg" aria-label="' . esc_attr__('YouTube Channel','woosms-sms-module-for-woocommerce') . '">' . esc_html__('YouTube Channel','woosms-sms-module-for-woocommerce') . '</a>',
			'contact_us' => '<a href="https://www.bulkgate.com/en/contact-us/" aria-label="' . esc_attr__('Contact us','woosms-sms-module-for-woocommerce') . '">' . esc_html__('Contact us','woosms-sms-module-for-woocommerce') . '</a>',
			'api' => '<a href="https://www.bulkgate.com/en/developers/sms-api/" aria-label="API">API</a>',
			'github' => '<a href="https://github.com/bulkgate/woosms" aria-label="GitHub">GitHub</a>',
			'terms_of_service' => '<a href="https://portal.bulkgate.com/page/terms-and-conditions" aria-label="' . esc_attr__('Terms of Service', 'woosms-sms-module-for-woocommerce') . '">' . esc_html__('Terms of Service','woosms-sms-module-for-woocommerce') . '</a>',
			'privacy_policy' => '<a href="https://portal.bulkgate.com/page/privacy-policy" aria-label="' . esc_attr__('Privacy Policy','woosms-sms-module-for-woocommerce') . '">' . esc_html__('Privacy Policy','woosms-sms-module-for-woocommerce') . '</a>',
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
			array_unshift($links, '<a href="' . Escape::url(admin_url('tools.php?page=bulkgate-debug')) . '">' . esc_html__('Debug','woosms-sms-module-for-woocommerce') . '</a>');

			if (Factory::get()->getByClass(Settings::class)->load('static:application_token') === null)
			{
				array_unshift($links, '<a href="' . Escape::url(admin_url('admin.php?page=bulkgate#/sign/in')) . '">' . esc_html__('Log In','woosms-sms-module-for-woocommerce') . '</a>');
			}
            else
            {
                array_unshift($links, '<a href="' . Escape::url(admin_url('admin.php?page=bulkgate#/dashboard')) . '">' . esc_html__('Settings','woosms-sms-module-for-woocommerce') . '</a>');
            }
		}
		return $links;
	}

    public static function notice(string $message, array $attributes = [])
    {
        $severity = $attributes['severity'] ?? 'info';
        $button = $attributes['button'] ?? null;
        $color = ['info' => "secondary-color", 'warning' => 'orange-color', 'error' => 'red-color'][$severity] ?? null;
        $icon = $severity === 'info' ? 'info' : 'warning';

        if ($button)
        {
            $button = "<p>$button</p>";
        }

        return <<<HTML
<div class="notice notice-$severity is-dismissible" style="display: flex; padding-left: 0; padding-top: 0; padding-bottom: 0;">
    <div style="position: relative; padding: 12px; color: var(--$color);">
        <div style="background: currentColor; position: absolute; opacity: .18; left: 0; top: 0; right: 0; bottom: 0;"></div>
        <span class="dashicons dashicons-$icon"></span>
    </div>
    <div style="flex-grow: 1; padding-left: 12px;">
        <p>$message</p>
        $button
    </div>
</div>
HTML;

    }
}
