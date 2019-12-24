<?php

declare(strict_types=1);

/*
 * @copyright   2018 Mautic Inc. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://www.mautic.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Integration\Provider;

use Mautic\IntegrationsBundle\Integration\ConfigurationTrait;
use Mautic\IntegrationsBundle\Integration\Interfaces\SyncInterface;
use Mautic\IntegrationsBundle\Sync\DAO\Mapping\MappingManualDAO;
use Mautic\IntegrationsBundle\Sync\SyncDataExchange\SyncDataExchangeInterface;
use MauticPlugin\MauticVtigerCrmBundle\Integration\BasicTrait;
use MauticPlugin\MauticVtigerCrmBundle\Sync\DataExchange;

class VtigerSyncProvider implements SyncInterface
{
    use BasicTrait;
    use ConfigurationTrait;

    /**
     * @var DataExchange
     */
    private $dataExchange;

    /**
     * VtigerSyncProvider constructor.
     *
     * @param DataExchange $dataExchange
     */
    public function __construct(DataExchange $dataExchange)
    {
        $this->dataExchange = $dataExchange;
    }

    /**
     * @return MappingManualDAO
     *
     * @throws \Mautic\IntegrationsBundle\Sync\Exception\ObjectNotSupportedException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException
     */
    public function getMappingManual(): MappingManualDAO
    {
        return $this->dataExchange->getFieldMapper()->getObjectsMappingManual();
    }

    /**
     * @return SyncDataExchangeInterface
     */
    public function getSyncDataExchange(): SyncDataExchangeInterface
    {
        return $this->dataExchange;
    }
}
