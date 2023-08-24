<?php declare(strict_types=1);

namespace BulkGate\WooSms\Template\Test;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use Mockery;
use WC_Meta, WC_Order;
use Tester\{Assert, TestCase};
use BulkGate\{Plugin\DI\Container, Plugin\IO\Url, Plugin\Settings\Settings, Plugin\User\Sign, WooSms\Template\SendMessage};
use function ob_get_contents, ob_start, trim;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/.mock-send-message.php';

/**
 * @testCase
 */
class SendMessageTest extends TestCase
{
	public function testInit(): void
	{
		$order = Mockery::mock(WC_Order::class);

		$container = Mockery::mock(Container::class);
		$container->shouldReceive('getByClass')->with(Settings::class)->once()->andReturn($settings = Mockery::mock(Settings::class));
		$settings->shouldReceive('load')->with('main:address_preference')->once()->andReturn('delivery');
		$order->shouldReceive('get_address')->with('shipping')->once()->andReturn([]);
		$order->shouldReceive('get_address')->with('billing')->once()->andReturn([
			'first_name' => 'John',
			'last_name' => 'Doe',
			'company' => 'ACME',
			'address_1' => 'Main Street 1',
			'address_2' => 'Main Street 2',
			'city' => 'New York',
			'state' => 'NY',
			'postcode' => '10001',
			'country' => 'US',
			'phone' => '777777777'
		]);
		$order->shouldReceive('get_meta_data')->withNoArgs()->once()->andReturn([$meta = Mockery::mock(WC_Meta::class)]);
		$meta->shouldReceive('get_data')->withNoArgs()->once()->andReturn(['key' => 'k', 'value' => 'v']);
		$order->shouldReceive('get_status')->withNoArgs()->once()->andReturn('completed');
		$order->shouldReceive('get_billing_email')->withNoArgs()->once()->andReturn('john@doe.com');
		$container->shouldReceive('getByClass')->with(Sign::class)->once()->andReturn($sign = Mockery::mock(Sign::class));
		$sign->shouldReceive('authenticate')->withNoArgs()->once()->andReturn('jwt.token.451');
		$container->shouldReceive('getByClass')->with(Url::class)->once()->andReturn($url = Mockery::mock(Url::class));
		$url->shouldReceive('get')->with('widget/message/send/jwt.token.451?config=init_widget_message_send')->once()->andReturn('https://www.example.com/widget/message/send/jwt.token.451?config=init_widget_message_send');

		ob_start();
		SendMessage::print($container, $order, ['message' => 'test']);

		Assert::same('<gate-send-message data-theme=\'{"palette": {"mode": "light"}}\'></gate-send-message>', trim(ob_get_contents()));

		Assert::same([
			'	function init_widget_message_send(widget) { widget.options.SendMessageProps = {"message":"test","recipients":[{"first_name":"John","last_name":"Doe","company":"ACME","street1":"Main Street 1, Main Street 2","city":"New York","zip":"10001","country":"US","phone_mobile":"777777777","phone_number_iso":"us","email":"john@doe.com","order_status":"Completed","extra_k":"v"}]}; }',
			[
				'src' => '|https://www.example.com/widget/message/send/jwt.token.451?config=init_widget_message_send|',
				'async' => true,
			],
		], $GLOBALS['script']);

		Mockery::close();
	}
}

(new SendMessageTest())->run();
