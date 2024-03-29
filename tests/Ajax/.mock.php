<?php declare(strict_types=1);

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

/**
 * @param scalar $s
 */
function sanitize_text_field($s): string
{
	return str_replace(["\\", "$", "@"], '', $s);
}


function site_url(string $path): string
{
	return "https://eshop.com/$path";
}
