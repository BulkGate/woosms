<?php declare(strict_types=1);

namespace BulkGate\WooSms\Event\Loader;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{Database\Connection, Event\Variables, Strict, Event\DataLoader};
use function do_action;

class Extension implements DataLoader
{
	use Strict;

	private Connection $database;

	public function __construct(Connection $database)
	{
		$this->database = $database;
	}


	public function load(Variables $variables, array $parameters = []): void
	{
		do_action('woosms_extends_variables', $variables, $this->database);
		do_action('bulkgate_extends_variables', $variables, $this->database);
	}
}
