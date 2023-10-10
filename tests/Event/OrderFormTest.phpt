<?php declare(strict_types=1);

namespace BulkGate\WooSms\Event\Test;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use Mockery;
use Tester\{Assert, TestCase};
use BulkGate\{Plugin\DI\Container, Plugin\Settings\Settings, WooSms\DI\Factory, WooSms\Event\OrderForm};

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/.mock-order-form.php';

/**
 * @testCase
 */
class OrderFormTest extends TestCase
{
	public function testBase(): void
	{
		OrderForm::init('cs_CZ');

		$factory = Mockery::mock('overload:' . Factory::class);
		$factory->shouldReceive('get')->withNoArgs()->once()->andReturn($container = Mockery::mock(Container::class));

		// ADD ASSET
		$container->shouldReceive('getByClass')->with(Settings::class)->once()->andReturn($settings = Mockery::mock(Settings::class));
		$settings->shouldReceive('load')->with('main:marketing_message_opt_in_enabled')->once()->andReturnTrue();
		$settings->shouldReceive('load')->with('main:marketing_message_opt_in_label')->once()->andReturn('label');
		$settings->shouldReceive('load')->with('main:marketing_message_opt_in_default')->once()->andReturnTrue();
		$settings->shouldReceive('load')->with('main:marketing_message_opt_in_url')->once()->andReturn('url');

		$callbacks = $GLOBALS['order_form_callback'];
		$callbacks['action_woocommerce_review_order_before_submit']();

		Assert::same([
			'bulkgate_marketing_message_opt_in' => [
				[
					'type' => 'checkbox',
					'class' => ['form-row mycheckbox'],
					'label_class' => [
						'woocommerce-form__label woocommerce-form__label-for-checkbox checkbox',
					],
					'input_class' => [
						'woocommerce-form__input woocommerce-form__input-checkbox input-checkbox',
					],
					'required' => false,
					'default' => true,
					'description' => '<br><a href=":attr:url:" target="_blank">:html:url:</a>',
					'label' => ':html:label:',
				],
				null,
			],
		], $GLOBALS['order_form_form']);
	}


	public function testDefault(): void
	{
		OrderForm::init('cs_CZ');

		$factory = Mockery::mock('overload:' . Factory::class);
		$factory->shouldReceive('get')->withNoArgs()->once()->andReturn($container = Mockery::mock(Container::class));

		// ADD ASSET
		$container->shouldReceive('getByClass')->with(Settings::class)->once()->andReturn($settings = Mockery::mock(Settings::class));
		$settings->shouldReceive('load')->with('main:marketing_message_opt_in_enabled')->once()->andReturnNull();
		$settings->shouldReceive('load')->with('main:marketing_message_opt_in_label')->once()->andReturnNull();
		$settings->shouldReceive('load')->with('main:marketing_message_opt_in_default')->once()->andReturnNull();
		$settings->shouldReceive('load')->with('main:marketing_message_opt_in_url')->once()->andReturnNull();

		$callbacks = $GLOBALS['order_form_callback'];
		$callbacks['action_woocommerce_review_order_before_submit']();

		Assert::same([
			'bulkgate_marketing_message_opt_in' => [
				[
					'type' => 'checkbox',
					'class' => ['form-row mycheckbox'],
					'label_class' => [
						'woocommerce-form__label woocommerce-form__label-for-checkbox checkbox',
					],
					'input_class' => [
						'woocommerce-form__input woocommerce-form__input-checkbox input-checkbox',
					],
					'required' => false,
					'default' => false,
					'description' => '',
					'label' => ':html:Souhlasím se zasíláním marketingových sdělení prostřednictvím SMS, Viber, RCS, WhatsApp a dalších podobných kanálů.:',
				],
				null,
			],
		], $GLOBALS['order_form_form']);
	}


	public function tearDown(): void
	{
		Mockery::close();
	}
}

(new OrderFormTest())->run();
