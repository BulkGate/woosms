<?php declare(strict_types=1);

namespace BulkGate\WooSms\Event\Loader;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use Exception;
use WC_Customer;
use BulkGate\{Plugin\Event\Helpers, Plugin\Event\Variables, Plugin\Localization\Formatter, Plugin\Strict, Plugin\Event\DataLoader, Plugin\Utils\Strings};

class Customer implements DataLoader
{
	use Strict;

	private Formatter $formatter;

	public function __construct(Formatter $formatter)
	{
		$this->formatter = $formatter;
	}


	public function load(Variables $variables, array $parameters = []): void
	{
		if (isset($variables['customer_id']) && !isset($variables['customer_mobile']))
		{
			$customer = isset($parameters['customer']) && $parameters['customer'] instanceof WC_Customer ? $parameters['customer'] : new WC_Customer((int) $variables['customer_id']);

			$billing = $customer->get_billing();
			$shipping = $customer->get_shipping();

			$variables['customer_firstname'] = Helpers::address('first_name', $shipping, $billing);
			$variables['customer_lastname'] = Helpers::address('last_name', $shipping, $billing);
			$variables['customer_company'] = Helpers::address('company', $shipping, $billing);
			$variables['customer_street'] = Helpers::joinStreet('address_1', 'address_2', $shipping, $billing);
			$variables['customer_city'] = Helpers::address('city', $shipping, $billing);
			$variables['customer_state'] = Helpers::address('state', $shipping, $billing);
			$variables['customer_postcode'] = Helpers::address('postcode', $shipping, $billing);
			$variables['customer_country'] = $this->formatter->format('country', Helpers::address('country', $billing, $shipping));
			$variables['customer_country_id'] = Strings::lower((string) Helpers::address('country', $billing, $shipping));
			$variables['customer_mobile'] = $variables['customer_phone'] = Helpers::address('phone', $shipping, $billing);
			$variables['customer_email'] = Helpers::address('email', $shipping, $billing);

			$variables['customer_invoice_firstname'] = Helpers::address('first_name', $billing, $shipping);
			$variables['customer_invoice_lastname'] = Helpers::address('last_name', $billing, $shipping);
			$variables['customer_invoice_company'] = Helpers::address('company', $billing, $shipping);
			$variables['customer_invoice_street'] = Helpers::joinStreet('address_1', 'address_2', $billing, $shipping);
			$variables['customer_invoice_city'] = Helpers::address('city', $billing, $shipping);
			$variables['customer_invoice_state'] = Helpers::address('state', $billing, $shipping);
			$variables['customer_invoice_postcode'] = Helpers::address('postcode', $billing, $shipping);
			$variables['customer_invoice_country'] = $this->formatter->format('country', Helpers::address('country', $billing, $shipping));
			$variables['customer_invoice_country_id'] = Strings::lower((string) Helpers::address('country', $billing, $shipping));
			$variables['customer_invoice_mobile'] = $variables['customer_invoice_phone'] = Helpers::address('phone', $billing, $shipping);
			$variables['customer_invoice_email'] = Helpers::address('email', $billing, $shipping);
		}
	}
}
