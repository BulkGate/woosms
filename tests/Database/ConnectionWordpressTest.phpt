<?php declare(strict_types=1);

namespace BulkGate\WooSms\Database\Test;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use Mockery;
use BulkGate\WooSms\Database\ConnectionWordpress;
use Tester\{Assert, TestCase};


require_once __DIR__ . '/../bootstrap.php';

/**
 * @testCase
 */
class ConnectionWordpressTest extends TestCase
{
	public function testExecute(): void
	{
		$connection = new ConnectionWordpress($db = Mockery::mock(\wpdb::class));
		$db->shouldReceive('get_results')->with('SQL')->once()->andReturn([
			['id' => 4], ['id' => 5]
		]);

		Assert::same([['id' => 4], ['id' => 5]], $connection->execute('SQL'));

		Assert::same(['SQL'], $connection->getSqlList());
	}


	public function testPrepare(): void
	{
		$connection = new ConnectionWordpress($db = Mockery::mock(\wpdb::class));
		$db->shouldReceive('prepare')->with('SQL', 'test')->once()->andReturn('Prepared SQL');

		Assert::same('Prepared SQL', $connection->prepare('SQL', 'test'));
	}


	public function testLastId(): void
	{
		$connection = new ConnectionWordpress($db = Mockery::mock(\wpdb::class));
		$db->insert_id = 451;

		Assert::same(451, $connection->lastId());
	}


	public function testEscape(): void
	{
		$connection = new ConnectionWordpress($db = Mockery::mock(\wpdb::class));
		$db->shouldReceive('_escape')->with('dangerous string')->once()->andReturn('safe string');

		Assert::same('safe string', $connection->escape('dangerous string'));
	}


	public function testPrefix(): void
	{
		$connection = new ConnectionWordpress($db = Mockery::mock(\wpdb::class));
		$db->prefix = 'wp_';

		Assert::same('wp_', $connection->prefix());
	}


	public function testTable(): void
	{
		$connection = new ConnectionWordpress($db = Mockery::mock(\wpdb::class));
		$db->prefix = 'wp_';

		Assert::same('wp_users', $connection->table('users'));
	}


	public function tearDown(): void
	{
		Mockery::close();
	}
}

(new ConnectionWordpressTest())->run();
