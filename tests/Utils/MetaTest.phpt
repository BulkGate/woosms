<?php declare(strict_types=1);

namespace BulkGate\WooSms\Utils\Test;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use Mockery;
use Tester\{Assert, TestCase};
use BulkGate\{Plugin\DI\Container, Plugin\Settings\Settings, WooSms\DI\Factory, WooSms\Utils\Meta};
use function define;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/.mock.php';

define('BULKGATE_PLUGIN_DIR', 'woosms-sms-module-for-woocommerce');

/**
 * @testCase
 */
class MetaTest extends TestCase
{
	public function testLinks(): void
	{
		Assert::same([
			'xxx' => '<a href="http://www.bulkgate.com/cs/">BulkGate</a>',
			'help_desk' => '<a href="https://help.bulkgate.com/en/" aria-label="Help Desk">Help Desk</a>',
			'price_list' => '<a href="https://www.bulkgate.com/en/pricing/" aria-label="Price List">Price List</a>',
			'youtube_channel' => '<a href="https://www.youtube.com/channel/UCGD7ndC4z2NfuWUrS-DGELg" aria-label="YouTube Channel">YouTube Channel</a>',
			'contact_us' => '<a href="https://www.bulkgate.com/en/contact-us/" aria-label="Contact us">Contact us</a>',
			'api' => '<a href="https://www.bulkgate.com/en/developers/sms-api/" aria-label="API">API</a>',
			'github' => '<a href="https://github.com/bulkgate/woosms" aria-label="GitHub">GitHub</a>',
			'terms_of_service' => '<a href="https://portal.bulkgate.com/page/terms-and-conditions" aria-label="Terms of Service">Terms of Service</a>',
			'privacy_policy' => '<a href="https://portal.bulkgate.com/page/privacy-policy" aria-label="Privacy Policy">Privacy Policy</a>',
		], Meta::links(['xxx' => '<a href="http://www.bulkgate.com/cs/">BulkGate</a>'], '/yyy/woosms-sms-module-for-woocommerce/woosms-sms-module-for-woocommerce.php'));

		Assert::same([
			'xxx' => '<a href="http://www.bulkgate.com/cs/">BulkGate</a>',
		], Meta::links(['xxx' => '<a href="http://www.bulkgate.com/cs/">BulkGate</a>'], '/yyy/woosms/woosms.php'));
	}


	public function testLogged(): void
	{
		$di = Mockery::mock('overload:' . Factory::class);
		$di->shouldReceive('get')->once()->andReturn($container = Mockery::mock(Container::class));
		$container->shouldReceive('getByClass')->with(Settings::class)->once()->andReturn($settings = Mockery::mock(Settings::class));
		$settings->shouldReceive('load')->with('static:application_token')->once()->andReturn('token');

		Assert::same([
			'<a href="$_admin://admin/tools.php?page=bulkgate-debug_$">Debug</a>',
			'xxx' => '<a href="http://www.bulkgate.com/cs/">BulkGate</a>',
		], Meta::settingsLink(['xxx' => '<a href="http://www.bulkgate.com/cs/">BulkGate</a>'], '/yyy/woosms-sms-module-for-woocommerce/woosms-sms-module-for-woocommerce.php'));
	}


	public function testSettingsLinkLogin(): void
	{
		$di = Mockery::mock('overload:' . Factory::class);
		$di->shouldReceive('get')->once()->andReturn($container = Mockery::mock(Container::class));
		$container->shouldReceive('getByClass')->with(Settings::class)->once()->andReturn($settings = Mockery::mock(Settings::class));
		$settings->shouldReceive('load')->with('static:application_token')->once()->andReturnNull();

		Assert::same([
			'<a href="$_admin://admin/admin.php?page=bulkgate#/sign/in_$">Log In</a>',
			'<a href="$_admin://admin/tools.php?page=bulkgate-debug_$">Debug</a>',
			'xxx' => '<a href="http://www.bulkgate.com/cs/">BulkGate</a>',
		], Meta::settingsLink(['xxx' => '<a href="http://www.bulkgate.com/cs/">BulkGate</a>'], '/yyy/woosms-sms-module-for-woocommerce/woosms-sms-module-for-woocommerce.php'));
	}


	public function tearDown(): void
	{
		Mockery::close();
	}
}

(new MetaTest())->run();
