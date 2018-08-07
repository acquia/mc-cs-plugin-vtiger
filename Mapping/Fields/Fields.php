<?php

declare(strict_types=1);

/*
 * @copyright   2018 Mautic Inc. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MagentoBundle\Mapping\Fields;

use Countable;
use Iterator;
use MauticPlugin\MagentoBundle\Enumerator\CacheEnum;
use MauticPlugin\MagentoBundle\Exception\DisabledFieldException;

final class Fields implements Iterator, Countable
{
    /**
     * @var FieldInterface[]
     */
    private $fields = [];

    /**
     * @var int
     */
    private $position = 0;

    /**
     * @var array|int[]
     */
    private $keys = [];

    /**
     * Store original API response - used by cache.
     *
     * @var array
     */
    private $customerFields = [];

    /**
     * Store original API response - used by cache.
     *
     * @var array
     */
    private $addressFields = [];

    /**
     * @param array $customerFields
     * @param array $addressFields
     */
    public function __construct(array $customerFields, array $addressFields)
    {
        foreach ($customerFields as $field) {
            try {
                $customer                           = new Customer($field);
                $this->fields[$customer->getName()] = $customer;
            } catch (DisabledFieldException $e) {
            }
        }

        foreach ($addressFields as $field) {
            try {
                $addressShipping = new AddressShipping($field);
                $addressBilling  = new AddressBilling($field);

                $this->fields[$addressShipping->getName()] = $addressShipping;
                $this->fields[$addressBilling->getName()]  = $addressBilling;
            } catch (DisabledFieldException $e) {
            }
        }

        $this->keys = array_keys($this->fields);

        $this->customerFields = $customerFields;
        $this->addressFields  = $addressFields;
    }

    /**
     * @return array
     */
    public function __toArray(): array
    {
        return [
            CacheEnum::KEY_CUSTOMER => $this->customerFields,
            CacheEnum::KEY_ADDRESS  => $this->addressFields,
        ];
    }

    public function getTypeByAlias(string $alias): string
    {
        return $this->fields[$alias]->getType();
    }

    public function current(): FieldInterface
    {
        $key = $this->keys[$this->position];

        return $this->fields[$key];
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function key(): int
    {
        return $this->keys[$this->position];
    }

    public function valid(): bool
    {
        return isset($this->keys[$this->position]);
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function count(): int
    {
        return count($this->fields);
    }
}
