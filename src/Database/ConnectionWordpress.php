<?php declare(strict_types=1);

namespace BulkGate\WooSms\Database;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use wpdb;
use BulkGate\Plugin\{Strict, Database\Connection};
use function count, is_array;

class ConnectionWordpress implements Connection
{
	use Strict;

	private wpdb $db;

	/**
	 * @var list<string>
	 */
	private array $sql = [];

	public function __construct(wpdb $db)
	{
		$this->db = $db;
	}


	public function execute(string $sql): ?array
	{
		$output = [];

		$this->sql[] = $sql;

		$result = $this->db->get_results($sql);

		if (is_array($result) && count($result) > 0)
		{
			foreach ($result as $key => $item)
			{
				$output[$key] = (array) $item;
			}
		}

		return $output;
	}


	public function prepare(string $sql, ...$parameters): string
	{
		return $this->db->prepare($sql, ...$parameters);
	}


	public function lastId()
	{
		return $this->db->insert_id;
	}


	public function escape(string $string): string
	{
		return $this->db->_escape($string);
	}


	public function prefix(): string
	{
		return $this->db->prefix;
	}


	public function getSqlList(): array
	{
		return $this->sql;
	}


	public function table(string $table): string
	{
		return $this->prefix() . $table;
	}
}
