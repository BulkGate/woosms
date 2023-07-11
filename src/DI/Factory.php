<?php declare(strict_types=1);

namespace BulkGate\WooSms\DI;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use wpdb;
use Tracy\Debugger;
use BulkGate\Plugin\{Debug\Logger, Debug\Repository\LoggerSettings, DI\FactoryStatic, DI\InvalidStateException, Event, Eshop, Exception, IO, Localization, Settings, Strict, DI\Container, DI\Factory as DIFactory, User};
use BulkGate\WooSms\{Ajax\Authenticate,
	Ajax\PluginSettingsChange,
	Database\ConnectionWordpress,
	Eshop\ConfigurationWordpress,
	Eshop\LanguageWordpress,
	Eshop\MultiStoreWordpress,
	Eshop\OrderStatusWordpress,
	Eshop\ReturnStatusWordpress,
	Event\Loader\Customer,
	Event\Loader\Extension,
	Event\Loader\Order,
	Event\Loader\OrderStatus,
	Event\Loader\Post,
	Event\Loader\Product,
	Event\Loader\Shop};
use function extension_loaded, is_int;

class Factory implements DIFactory
{
	use Strict;
	use FactoryStatic;

	/**
	 * @param array<string, mixed> $parameters
	 * @throws Exception
	 */
	protected static function createContainer(array $parameters = []): Container
	{
		$container = new Container($parameters['mode'] ?? 'strict');

		if ($parameters['debug'] ?? false)
		{
			Debugger::$strictMode = true;
			Debugger::$maxDepth = 10;
			Debugger::enable(Debugger::Development);
		}

		if (!($parameters['db'] ?? null) instanceof wpdb)
		{
			throw new InvalidStateException('Database connection is not set.');
		}

		if (!isset($parameters['url']))
		{
			throw new InvalidStateException('Eshop url is not set.');
		}

		$parameters['language'] ??= 'en';

		// Ajax
		$container['ajax.authenticate'] = Authenticate::class;
		$container['ajax.plugin_settings'] = PluginSettingsChange::class;

		// Database
		$container['database.connection'] = ['factory' => ConnectionWordpress::class, 'parameters' => ['db' => $parameters['db']]];

		// Debug
		$container['debug.logger.repository'] = ['factory' => LoggerSettings::class, 'factory_method' => function () use ($container, $parameters): LoggerSettings
		{
			$service = new LoggerSettings($container->getByClass(Settings\Settings::class));
			$service->setup(is_int($parameters['logger_limit'] ?? null) ? $parameters['logger_limit'] : 100);
			return $service;
		}];
		$container['debug.logger'] = Logger::class;

		// Eshop
		$container['eshop.synchronizer'] = Eshop\EshopSynchronizer::class;
		$container['eshop.configuration'] = ['factory' => Eshop\Configuration::class, 'factory_method' => fn () => new ConfigurationWordpress($parameters['plugin_data'] ?? [], $parameters['url'], $parameters['name'] ?? 'Store')];
		$container['eshop.order_status'] = OrderStatusWordpress::class;
		$container['eshop.return_status'] = ReturnStatusWordpress::class;
        $container['eshop.language'] = LanguageWordpress::class;
        $container['eshop.multistore'] = MultiStoreWordpress::class;

        // Event loaders
		$container['event.loader.extension'] = ['factory' => Extension::class, 'auto_wiring' => false];
		$container['event.loader.shop'] = ['factory' => Shop::class, 'auto_wiring' => false];
		$container['event.loader.order'] = ['factory' => Order::class, 'auto_wiring' => false];
		$container['event.loader.order_status'] = ['factory' => OrderStatus::class, 'auto_wiring' => false];
		$container['event.loader.customer'] = ['factory' => Customer::class, 'auto_wiring' => false];
		$container['event.loader.product'] = ['factory' => Product::class, 'auto_wiring' => false];
		$container['event.loader.post'] = ['factory' => Post::class, 'auto_wiring' => false];

		// Event
		$container['event.hook'] = ['factory' => Event\Hook::class, 'parameters' => ['version' => $parameters['api_version'] ?? '1.0']];
		$container['event.asynchronous.repository'] = Event\Repository\AsynchronousDatabase::class;
		$container['event.asynchronous'] = Event\Asynchronous::class;
		$container['event.loader'] = ['factory' => Event\Loader::class, 'factory_method' => fn () => new Event\Loader([
			$container->getByClass(Order::class),
			$container->getByClass(OrderStatus::class),
			$container->getByClass(Customer::class),
			$container->getByClass(Shop::class),
			$container->getByClass(Product::class),
			$container->getByClass(Post::class),
			$container->getByClass(Extension::class),
		])];
		$container['event.dispatcher'] = Event\Dispatcher::class;

		// IO
		$container['io.connection.factory'] = ['factory' => IO\ConnectionFactory::class, 'factory_method' => function () use ($container): IO\ConnectionFactory
		{
			/**
			 * @var Eshop\Configuration $configuration
			 */
			$configuration = $container->getByClass(Eshop\Configuration::class);

			return new IO\ConnectionFactory($configuration->url(), $configuration->product(), $container->getByClass(Settings\Settings::class));
		}];
		$container['io.connection'] = ['factory' => IO\Connection::class, 'factory_method' => fn () => $container->getByClass(IO\ConnectionFactory::class)->create()];
		$container['io.url'] = ['factory' => IO\Url::class, 'parameters' => ['url' => $parameters['gate_url'] ?? 'https://portal.bulkgate.com']];

		// Localization
		$container['localization.translator'] = Localization\TranslatorSettings::class;
		$container['localization.formatter'] = extension_loaded('intl') ? ['factory' => Localization\FormatterIntl::class, 'factory_method' => fn () => new Localization\FormatterIntl($parameters['language'], $parameters['country'] ?? null)] : Localization\FormatterBasic::class;

		// Settings
		$container['settings.repository.database'] = Settings\Repository\SettingsDatabase::class;
		$container['settings.settings'] = Settings\Settings::class;
		$container['settings.repository.synchronizer'] = Settings\Repository\SynchronizationDatabase::class;
		$container['settings.synchronizer'] = Settings\Synchronizer::class;

		// User
		$container['user.sign'] = User\Sign::class;

		return $container;
	}
}
