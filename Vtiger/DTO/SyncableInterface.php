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

/**
 * Used for DTO classes that can be synchronized with Mautic's sync mechanism.
 */
interface SyncableInterface
{
    /**
     * Name of the object type.
     *
     * @return string
     */
    public function getObjectName(): string;

    /**
     * @return int
     */
    public function getId(): int;

    /**
     * Modified at timestamp in Unix epoch.
     *
     * @return int
     */
    public function getModifiedAtTimestamp(): int;

    /**
     * Flattened fields of the object in format [alias => value].
     *
     * @return array
     */
    public function getFields(): array;
}
