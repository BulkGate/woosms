<?php declare(strict_types=1);

namespace BulkGate\WooSms\DI;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use wpdb;
use Tracy\Debugger;
use BulkGate\Plugin\{DI\FactoryStatic, DI\InvalidStateException, Event, Eshop, Exception, IO, Localization, Settings, Strict, DI\Container, DI\Factory as DIFactory, User};
use BulkGate\WooSms\{Ajax\Authenticate, Ajax\Login, Ajax\Logout, Eshop\ConfigurationWordpress, Eshop\MultiStoreWordpress, Eshop\OrderStatusWordpress, Eshop\ReturnStatusWordpress, Eshop\LanguageWordpress, Database\ConnectionWordpress};

class Factory implements DIFactory
{
	use Strict;
	use FactoryStatic;

	/**
	 * @throws Exception
	 */
	private static function createContainer(array $parameters = []): Container
	{
		$container = new Container($parameters['mode'] ?? 'strict');

		if ($parameters['debug'] ?? false)
		{
			Debugger::$strictMode = true;
			Debugger::$maxDepth = 10;
			Debugger::enable(Debugger::Development);
		}

		if (!$parameters['db'] instanceof wpdb)
		{
			throw new InvalidStateException('Database connection is not set.');
		}

		if (!isset($parameters['url']))
		{
			throw new InvalidStateException('Eshop url is not set.');
		}

		// Ajax
		$container['ajax.authenticate'] = Authenticate::class;
		$container['ajax.login'] = Login::class;
		$container['ajax.logout'] = Logout::class;

		// Database
		$container['database.connection'] = ['factory' => ConnectionWordpress::class, 'parameters' => ['db' => $parameters['db']]];

		// Eshop
		$container['eshop.synchronizer'] = Eshop\EshopSynchronizer::class;
		$container['eshop.configuration'] = ['factory' => Eshop\Configuration::class, 'factory_method' => fn () => new ConfigurationWordpress($parameters['plugin_data'] ?? [], $parameters['url'], $parameters['name'] ?? 'Store', $container->getByClass(Settings\Settings::class))];
		$container['eshop.order_status'] = OrderStatusWordpress::class;
		$container['eshop.return_status'] = ReturnStatusWordpress::class;
        $container['eshop.language'] = LanguageWordpress::class;
        $container['eshop.multistore'] = MultiStoreWordpress::class;

        // Event
		$container['event.hook'] = ['factory' => Event\Hook::class, 'parameters' => ['version' => $parameters['api_version'] ?? '1.0']];
		$container['event.asynchronous.repository'] = Event\Repository\AsynchronousDatabase::class;
		$container['event.asynchronous'] = Event\Asynchronous::class;

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
