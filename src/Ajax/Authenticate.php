<?php declare(strict_types=1);

namespace BulkGate\WooSms\Ajax;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{Strict, Settings\Settings, User\Sign, Utils\JsonResponse};

class Authenticate
{
	use Strict;

	private Settings $settings;

	private Sign $sign;

	public function __construct(Settings $settings, Sign $sign)
	{
		$this->settings = $settings;
		$this->sign = $sign;
	}
	

	/**
	 * @return never
	 */
	public function authenticate(string $invalid_redirect): void
	{
		if ($this->settings->load('static:application_token') === null)
		{
			JsonResponse::send(['redirect' => $invalid_redirect]);
		}
		else
		{
			JsonResponse::send(['token' => $this->sign->authenticate()]);
		}
	}
}
