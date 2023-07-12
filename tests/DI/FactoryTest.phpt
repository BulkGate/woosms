<?php declare(strict_types=1);

namespace BulkGate\WooSms\Event\Test;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use Mockery;
use Tester\{Assert, TestCase};
use BulkGate\{Plugin\Debug\Logger,
	Plugin\Debug\Repository\LoggerSettings,
	Plugin\DI\Container,
	Plugin\DI\InvalidStateException,
	Plugin\Eshop\Configuration,
	Plugin\Eshop\EshopSynchronizer,
	Plugin\Event\Asynchronous,
	Plugin\Event\Dispatcher,
	Plugin\Event\Loader,
	Plugin\Event\Repository\AsynchronousDatabase,
	Plugin\IO\Connection as IO,
	Plugin\IO\ConnectionFactory,
	Plugin\IO\Url,
	Plugin\Localization\Formatter,
	Plugin\Localization\TranslatorSettings,
	Plugin\Settings\Repository\SettingsDatabase,
	Plugin\Settings\Repository\SynchronizationDatabase,
	Plugin\Settings\Settings,
	Plugin\User\Sign,
	WooSms\Ajax\Authenticate,
	WooSms\Ajax\PluginSettingsChange,
	WooSms\Database\ConnectionWordpress,
	WooSms\DI\Factory,
	WooSms\Eshop\LanguageWordpress,
	WooSms\Eshop\MultiStoreWordpress,
	WooSms\Eshop\OrderStatusWordpress,
	WooSms\Eshop\ReturnStatusWordpress,
	Plugin\Event\Hook,
	WooSms\Event\Loader\Customer,
	WooSms\Event\Loader\Extension,
	WooSms\Event\Loader\Order,
	WooSms\Event\Loader\OrderStatus,
	WooSms\Event\Loader\Post,
	WooSms\Event\Loader\Product,
	WooSms\Event\Loader\Shop};
use wpdb;

require_once __DIR__ . '/../bootstrap.php';

/**
 * @testCase
 */
class FactoryTest extends TestCase
{
	public function testFail(): void
	{
		Assert::exception(fn () => Factory::get(), InvalidStateException::class, 'Database connection is not set.');

		Factory::setup(fn () => [
			'db' => Mockery::mock(wpdb::class)
		]);

		Assert::exception(fn () => Factory::get(), InvalidStateException::class, 'Eshop url is not set.');
	}


	public function testBase(): void
	{
		Factory::setup(fn () => [
			'db' => Mockery::mock(wpdb::class),
			'url' => 'https://www.bulkgate.com',
			'debug' => true
		]);

		Assert::type(Container::class, Factory::get());
		Assert::same(Factory::get(), Factory::get());

		Assert::type(Authenticate::class, Factory::get()->getService('ajax.authenticate'));
		Assert::type(PluginSettingsChange::class, Factory::get()->getService('ajax.plugin_settings'));

		Assert::type(ConnectionWordpress::class, Factory::get()->getService('database.connection'));

		Assert::type(Logger::class, Factory::get()->getService('debug.logger'));
		Assert::type(LoggerSettings::class, Factory::get()->getService('debug.repository.logger'));

		Assert::type(EshopSynchronizer::class, Factory::get()->getService('eshop.synchronizer'));
		Assert::type(Configuration::class, Factory::get()->getService('eshop.configuration'));
		Assert::type(OrderStatusWordpress::class, Factory::get()->getService('eshop.order_status'));
		Assert::type(ReturnStatusWordpress::class, Factory::get()->getService('eshop.return_status'));
		Assert::type(LanguageWordpress::class, Factory::get()->getService('eshop.language'));
		Assert::type(MultiStoreWordpress::class, Factory::get()->getService('eshop.multistore'));

		Assert::type(Extension::class, Factory::get()->getService('event.loader.extension'));
		Assert::type(Shop::class, Factory::get()->getService('event.loader.shop'));
		Assert::type(Order::class, Factory::get()->getService('event.loader.order'));
		Assert::type(OrderStatus::class, Factory::get()->getService('event.loader.order_status'));
		Assert::type(Customer::class, Factory::get()->getService('event.loader.customer'));
		Assert::type(Product::class, Factory::get()->getService('event.loader.product'));
		Assert::type(Post::class, Factory::get()->getService('event.loader.post'));

		Assert::type(Dispatcher::class, Factory::get()->getService('event.dispatcher'));
		Assert::type(Hook::class, Factory::get()->getService('event.hook'));
		Assert::type(AsynchronousDatabase::class, Factory::get()->getService('event.asynchronous.repository'));
		Assert::type(Asynchronous::class, Factory::get()->getService('event.asynchronous'));
		Assert::type(Loader::class, Factory::get()->getService('event.loader'));

		Assert::type(ConnectionFactory::class, Factory::get()->getService('io.connection.factory'));
		Assert::type(IO::class, Factory::get()->getService('io.connection'));
		Assert::type(Url::class, Factory::get()->getService('io.url'));

		Assert::type(TranslatorSettings::class, Factory::get()->getService('localization.translator'));
		Assert::type(Formatter::class, Factory::get()->getService('localization.formatter'));

		Assert::type(SettingsDatabase::class, Factory::get()->getService('settings.repository.database'));
		Assert::type(Settings::class, Factory::get()->getService('settings.settings'));
		Assert::type(SynchronizationDatabase::class, Factory::get()->getService('settings.repository.synchronizer'));

		Assert::type(Sign::class, Factory::get()->getService('user.sign'));

		Assert::count(33, Factory::get());
	}
}

(new FactoryTest())->run();
