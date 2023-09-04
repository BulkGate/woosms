<?php declare(strict_types=1);

namespace BulkGate\WooSms\Ajax\Test;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use Mockery;
use Tester\{Assert, TestCase};
use BulkGate\{Plugin\Settings\Synchronizer, Plugin\Settings\Settings, WooSms\Ajax\PluginSettingsChange};

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/.mock.php';

/**
 * @testCase
 */
class PluginSettingsChangeTest extends TestCase
{
	public function testToken(): void
	{
		$plugin_settings = new PluginSettingsChange($settings = Mockery::mock(Settings::class), $synchronize = Mockery::mock(Synchronizer::class));

		$settings->shouldReceive('load')->with('main:language')->once()->andReturn('en');
		$settings->shouldReceive('set')->with('main:dispatcher', 'cron', ['type' => 'string'])->once();
		$settings->shouldReceive('set')->with('main:synchronization', 'all', ['type' => 'string'])->once();
		$settings->shouldReceive('set')->with('main:language', 'en', ['type' => 'string'])->once();
		$settings->shouldReceive('set')->with('main:language_mutation', false, ['type' => 'bool'])->once();
		$settings->shouldReceive('set')->with('main:delete_db', false, ['type' => 'bool'])->once();
		$settings->shouldReceive('set')->with('main:address_preference', 'invoice', ['type' => 'string'])->once();
		$settings->shouldReceive('set')->with('main:marketing_message_opt_in_enabled', true, ['type' => 'bool'])->once();
		$settings->shouldReceive('set')->with('main:marketing_message_opt_in_label', 'xxx', ['type' => 'string'])->once();
		$settings->shouldReceive('set')->with('main:marketing_message_opt_in_default', true, ['type' => 'bool'])->once();
		$settings->shouldReceive('set')->with('main:marketing_message_opt_in_url', 'xxx', ['type' => 'string'])->once();
		$synchronize->shouldReceive('synchronize')->with(true)->once();

		Assert::same([
				'data' => [
					'layout' => [
						'server' => [
							'application_settings' => [
								'dispatcher' => 'cron',
								'synchronization' => 'all',
								'language' => 'en',
								'language_mutation' => false,
								'delete_db' => false,
								'address_preference' => 'invoice',
								'marketing_message_opt_in_enabled' => true,
								'marketing_message_opt_in_label' => 'xxx',
								'marketing_message_opt_in_default' => true,
								'marketing_message_opt_in_url' => 'xxx',
							],
						],
					],
				],
		], $plugin_settings->run([
			'dispatcher' => 'cron',
			'synchronization' => 'all',
			'language' => 'en',
			'language_mutation' => 0,
			'delete_db' => 0,
			'address_preference' => 'invoice',
			'marketing_message_opt_in_enabled' => true,
			'marketing_message_opt_in_label' => 'xxx',
			'marketing_message_opt_in_default' => 'true',
			'marketing_message_opt_in_url' => 'xxx',
			'invalid' => 'xxx',
		]));
	}


	public function testLanguageRedirect(): void
	{
		$plugin_settings = new PluginSettingsChange($settings = Mockery::mock(Settings::class), $synchronize = Mockery::mock(Synchronizer::class));

		$settings->shouldReceive('load')->with('main:language')->once()->andReturn('en');
		$settings->shouldReceive('set')->with('main:dispatcher', 'cron', ['type' => 'string'])->once();
		$settings->shouldReceive('set')->with('main:synchronization', 'all', ['type' => 'string'])->once();
		$settings->shouldReceive('set')->with('main:language', 'cs', ['type' => 'string'])->once();
		$settings->shouldReceive('set')->with('main:language_mutation', false, ['type' => 'bool'])->once();
		$settings->shouldReceive('set')->with('main:delete_db', false, ['type' => 'bool'])->once();
		$synchronize->shouldReceive('synchronize')->with(true)->once();

		Assert::same([
            'data' => ['redirect' => 'https://eshop.com//?bulkgate-redirect=dashboard'],
        ], $plugin_settings->run([
			'dispatcher' => 'cron@',
			'synchronization' => 'all$',
			'language' => 'cs',
			'language_mutation' => 0,
			'delete_db' => 0,
			'invalid' => 'xxx'
		]));
	}


	public function tearDown(): void
	{
		Mockery::close();
	}
}

(new PluginSettingsChangeTest())->run();
