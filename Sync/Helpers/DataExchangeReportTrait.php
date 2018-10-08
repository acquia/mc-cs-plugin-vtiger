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

namespace MauticPlugin\MauticVtigerCrmBundle\Sync\Helpers;

use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Report\FieldDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Report\ObjectDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Report\ReportDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Value\NormalizedValueDAO;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\BaseModel;

/**
 * Trait DataExchangeReportTrait
 * @package MauticPlugin\MauticVtigerCrmBundle\Sync\Helpers
 */
trait DataExchangeReportTrait
{
    /**
     * @param \MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Request\ObjectDAO $requestedObject
     * @param ReportDAO                                                        $syncReport
     *
     * @return ReportDAO|mixed
     * @throws \Exception
     */
    public function getObjectSyncReport(\MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Request\ObjectDAO $requestedObject, ReportDAO &$syncReport)
    {
        $fromDateTime = $requestedObject->getFromDateTime();
        $mappedFields = $requestedObject->getFields();
        $objectFields = $this->objectRepository->describe()->getFields();

        $updated = $this->getReportPayload($fromDateTime, $mappedFields);

        /** @var BaseModel $object */
        foreach ($updated as $object) {
            $objectDAO = new ObjectDAO(self::OBJECT_NAME, $object->getId(), new \DateTimeImmutable($object->getModifiedTime()->format('r')));

            foreach ($object->dehydrate($mappedFields) as $field => $value) {

                if (!isset($objectFields[$field])) {
                    // If the present value is not described it should be processed as string
                    $normalizedValue = $this->valueNormalizer->normalizeForMautic(NormalizedValueDAO::STRING_TYPE, $value);
                } else {
                    // Normalize the value from the API to what Mautic needs
                    $normalizedValue = $this->valueNormalizer->normalizeForMautic($objectFields[$field]->getType(), $value);
                }


                $reportFieldDAO = new FieldDAO($field, $normalizedValue);

                $objectDAO->addField($reportFieldDAO);
            }

            $syncReport->addObject($objectDAO);
        }

        return $syncReport;
    }

    /**
     * @param \DateTimeImmutable $fromDate
     * @param array              $mappedFields
     *
     * @return array|mixed
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     */
    protected function getReportPayload(\DateTimeImmutable $fromDate, array $mappedFields)
    {
        $fullReport = []; $iteration = 0;
        // We must iterate while there is still some result left

        do {
            $report = $this->objectRepository->query('SELECT id,modifiedtime,assigned_user_id,' . join(',', $mappedFields)
                . ' FROM ' . self::OBJECT_NAME .  ' WHERE modifiedtime>' . $fromDate->getTimestamp()
                . ' LIMIT ' . ($iteration*100) . ',100');

            $iteration++;

            $fullReport = array_merge($fullReport, $report);
        } while (count($report));

        return $report;
    }
}
