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

namespace MauticPlugin\MauticVtigerCrmBundle\Mapping;

use MauticPlugin\MagentoBundle\Magento\DTO\SyncableInterface;
use MauticPlugin\IntegrationsBundle\DAO\Sync\Report\FieldDAO;
use MauticPlugin\IntegrationsBundle\DAO\Sync\Report\ObjectDAO;
use MauticPlugin\IntegrationsBundle\Helpers\ValueNormalizer\ValueNormalizer;

class SyncObjectBuilder
{
    /**
     * @var FieldMapping
     */
    private $fieldMapping;

    /**
     * @var ValueNormalizer
     */
    private $valueNormalizer;

    /**
     * @param FieldMapping $fieldMapping
     */
    public function __construct(FieldMapping $fieldMapping)
    {
        $this->fieldMapping    = $fieldMapping;
        $this->valueNormalizer = new ValueNormalizer();
    }

    /**
     * @param SyncableInterface $objectDTO
     *
     * @return ReportDAO
     */
    public function buildSyncObject(SyncableInterface $syncable): ObjectDAO
    {
        $fields    = $this->fieldMapping->getFields();
        $objectDAO = new ObjectDAO($syncable->getObjectName(), $syncable->getId());
        $objectDAO->setChangeTimestamp($syncable->getModifiedAtTimestamp());

        // @todo add fields:
        foreach ($syncable->getFields() as $alias => $value) {
            // Normalize the value from the API to what Mautic needs
            $normalizedValue = $this->valueNormalizer->normalizeForMautic($fields->getTypeByAlias($alias), $value);
            $reportFieldDAO  = new FieldDAO($alias, $normalizedValue);

            $objectDAO->addField($reportFieldDAO);
        }

        return $objectDAO;
    }
}
