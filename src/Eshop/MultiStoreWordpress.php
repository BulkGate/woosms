<?php declare(strict_types=1);

namespace BulkGate\WooSms\Eshop;

/**
 * @author Lukáš Piják 2023 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Plugin\{Strict, Eshop\Configuration, Eshop\MultiStore};

class MultiStoreWordpress implements MultiStore
{
	use Strict;

	private Configuration $configuration;

	public function __construct(Configuration $configuration)
	{
		$this->configuration = $configuration;
	}


    public function load(): array
    {
        return [0 => $this->configuration->name()];
    }
}
