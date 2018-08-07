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

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\DTO;

use DateTimeImmutable;

final class Contact implements SyncableInterface
{

    /**
     * @var string
     */
    public const NAME = 'Contact';

    /**
     * @var int
     */
    private $id;

    /**
     * @var DateTimeImmutable
     */
    private $updatedAt;

    /**
     * @var BillingAddress
     */
    private $billingAddress;

    /**
     * @var ShippingAddress
     */
    private $shippingAddress;

    /**
     * @var array
     */
    private $fields = [];

    public function __construct(array $customerData)
    {
        $this->populate($customerData);
    }

    /**
     * @return string
     */
    public function getObjectName(): string
    {
        return self::NAME;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @return int
     */
    public function getModifiedAtTimestamp(): int
    {
        return $this->updatedAt->getTimestamp();
    }

    /**
     * @return ShippingAddress
     */
    public function getShippingAddress(): ShippingAddress
    {
        return $this->shippingAddress;
    }

    /**
     * @return BillingAddress
     */
    public function getBillingAddress(): BillingAddress
    {
        return $this->billingAddress;
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function isSystemField(string $key): bool
    {
        return CustomerSystemFieldsEnum::isValidValue($key);
    }

    /**
     * Populates object properties from provided array.
     *
     * @param array $customerData
     */
    private function populate(array $customerData): void
    {
        $this->id        = $customerData['id'];
        $this->updatedAt = new DateTimeImmutable($customerData['updated_at']);

        // @todo this array must be flattened
        $this->fields    = $customerData;

        if (!empty($customerData['addresses'])) {
            foreach ($customerData['addresses'] as $address) {
                if (!empty($address['default_shipping'])) {
                    $this->shippingAddress = new ShippingAddress($address);
                }
                if (!empty($address['default_billing'])) {
                    $this->billingAddress = new BillingAddress($address);
                }
            }
        }
    }
}
