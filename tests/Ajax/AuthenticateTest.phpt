<?php declare(strict_types=1);

namespace BulkGate\WooSms\Ajax\Test;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use Mockery;
use Tester\{Assert, TestCase};
use BulkGate\{Plugin\User\Sign, Plugin\Utils\JsonResponse, Plugin\Settings\Settings, WooSms\Ajax\Authenticate};

require_once __DIR__ . '/../bootstrap.php';

/**
 * @testCase
 */
class AuthenticateTest extends TestCase
{
	public function testToken(): void
	{
		$authenticate = new Authenticate(
			$settings = Mockery::mock(Settings::class),
			$sign = Mockery::mock(Sign::class)
		);
		$response = Mockery::mock('overload:' . JsonResponse::class);
		$settings->shouldReceive('load')->with('static:application_token')->once()->andReturn('token');
		$response->shouldReceive('send')->with(['token' => 'jwt'])->once();

		$sign->shouldReceive('authenticate')->once()->andReturn('jwt');

		$authenticate->run('redirect');

		Assert::true(true);
	}


	public function testGuest(): void
	{
		$authenticate = new Authenticate(
			$settings = Mockery::mock(Settings::class),
			Mockery::mock(Sign::class)
		);
		$response = Mockery::mock('overload:' . JsonResponse::class);
		$settings->shouldReceive('load')->with('static:application_token')->once()->andReturnNull();
		$response->shouldReceive('send')->with(['redirect' => 'invalid_redirect'])->once();

		$authenticate->run('invalid_redirect');

		Assert::true(true);
	}


	public function tearDown(): void
	{
		Mockery::close();
	}
}

(new AuthenticateTest())->run();
