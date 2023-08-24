<?php declare(strict_types=1);

namespace BulkGate\WooSms\Debug;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\{Plugin\Strict, Plugin\Debug\Logger, Plugin\Debug\Requirements, WooSms\Utils\Escape, WooSms\Utils\Logo};
use function array_reverse, date, file_get_contents, version_compare;

class Page
{
	use Strict;

	public static function print(Logger $logger, Requirements $requirements): void
	{
		echo '
		<div style="max-width: 1000px; margin: 50px auto;">
		<h1><img src="' . Escape::htmlAttr(Logo::Menu) . '" alt="" width="25"/>&nbsp;BulkGate Debug</h1>
		<p>This page serves as a comprehensive tool for users to monitor, analyze, and troubleshoot the plugin, including tracking errors in the log. It also provides essential information and troubleshooting capabilities.</p
		>';

		echo '
		<h2>Requirements test</h2>
		<table id="bulkgate-requirements-table" class="widefat striped">
			<tbody>
				<tr>
					<td style="width: 180px">
						<b>PHP Version</b>
					</td>
					<td class="desc">
						<b style="background: none;font-weight: 900;">' . Escape::html(phpversion()) . '</b>
					</td>
				</tr>
				<tr>
					<td>
						<b>WordPress Version</b>
					</td>
					<td class="desc">
						<b style="background: none;font-weight: 900;">' . Escape::html($GLOBALS['wp_version']) . '</b>
					</td>
				</tr>
		';

		foreach ($requirements->run([
			$requirements->same('{"message":"BulkGate API"}', file_get_contents('https://portal.bulkgate.com/api/welcome'), 'Api Connection'),
			$requirements->same(true, version_compare($GLOBALS['wp_version'], '5.7', '>='), 'WordPress ver. >= 5.7'),
		]) as $item)
		{
			echo '
					<tr>
						<td>
							<b>' . Escape::html($item['description']) . '</b>
						</td>
						<td class="desc">
							<b style="background: none;color: ' . Escape::htmlAttr($item['color']) . ';">' . Escape::html($item['passed'] ? 'OK' : "FAIL - {$item['error']}") . '</b>
						</td>
					</tr>
			';
		}

		echo '
			</tbody>
		</table>
		';

		echo '<br/>';

		$list = $logger->getList();

		echo '<h2>Error log</h2>';

		if ($list !== [])
		{
			echo '
			<table id="bulkgate-log-table" class="widefat striped">
				<tbody>
			';

				foreach (array_reverse($list) as $item)
				{
					echo '
					<tr>
						<td style="width: 130px">
							<b>' . Escape::html(date('d.m. Y H:i:s', $item['created'])) . '</b>
						</td>
						<td class="desc">
							<code style="background: none;">' . Escape::html($item['message']) . '</code>
						</td>
					</tr>
				';
				}

				echo '
				</tbody>
			</table>
			';
		}
		else
		{
			echo '<p>No errors found.</p>';
		}

		echo '</div>';
	}
}
