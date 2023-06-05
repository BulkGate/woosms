<?php declare(strict_types=1);

namespace BulkGate\WooSms\Ajax;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\WooSms\{Post, DI\Factory};
use BulkGate\Plugin\{Strict, User\Sign, Utils\JsonResponse};

class Login
{
	use Strict;

	/**
	 * @return never
	 */
	public function run(string $redirect_url): void
	{
		['email' => $email, 'password' => $password] = Post::get('__bulkgate');

		JsonResponse::send(Factory::get()->getByClass(Sign::class)->in($email, $password, $redirect_url));
	}
}
