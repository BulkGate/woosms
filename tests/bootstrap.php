<?php declare(strict_types=1);

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

if (@!include __DIR__ . '/../vendor/autoload.php')
{
	echo 'Install Nette Tester using `composer install`';
	exit(1);
}

Tester\Environment::setup();

date_default_timezone_set('Europe/Prague');
