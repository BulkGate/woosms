<?php declare(strict_types=1);

namespace BulkGate\WooSms\Ajax;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\WooSms\DI\Factory;
use BulkGate\Plugin\{DI\MissingParameterException, DI\MissingServiceException, Strict, User\Sign, Utils\JsonResponse};

class Logout
{
	use Strict;

	/**
	 * @return never
	 * @throws MissingParameterException|MissingServiceException
	 */
	public function run(string $redirect_url): void
	{
		JsonResponse::send(Factory::get()->getByClass(Sign::class)->out($redirect_url));
	}
}
