<?php
/*
 * @copyright   2018 Mautic Inc. All rights reserved
 * @author      Mautic, Inc. Jan Kozak <galvani78@gmail.com>
 *
 * @link        http://mautic.com
 * @created     7.9.18
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Sync\Helpers;

use MauticPlugin\IntegrationsBundle\Sync\DAO\Mapping\UpdatedObjectMappingDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Order\ObjectChangeDAO;
use MauticPlugin\IntegrationsBundle\Sync\Logger\DebugLogger;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidArgumentException;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\BaseModel;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\BaseRepository;

trait DataExchangeOperationsTrait
{
    /**
     * @param array             $ids
     * @param ObjectChangeDAO[] $objects
     *
     * @return UpdatedObjectMappingDAO[]
     */
    public function update(array $ids, array $objects)
    {
        DebugLogger::log(
            self::OBJECT_NAME,
            sprintf(
                "Found %d objects to update to integration with ids %s",

                count($objects),
                implode(", ", $ids)
            ),
            __CLASS__ . ':' . __FUNCTION__
        );

        $updatedMappedObjects = [];
        /** @var ObjectChangeDAO $changedObject */
        foreach ($objects as $integrationObjectId => $changedObject) {
            $fields = $changedObject->getFields();

            $objectData = ['id'=>$changedObject->getMappedObjectId()];

            foreach ($fields as $field) {
                /** @var \MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Order\FieldDAO $field */
                $objectData[$field->getName()] = $field->getValue()->getNormalizedValue();
            }

            $modelClass = BaseRepository::$moduleClassMapping[$changedObject->getMappedObject()];

            $vtigerModel = new $modelClass($objectData);

            if ($this->settings->getSetting('updateOwner') || !$vtigerModel->getAssignedUserId()) {
                $vtigerModel->setAssignedUserId($this->settings->getSetting('owner'));
            }

            var_dump($changedObject);
            throw new \Exception('dddd');
            die();
            try {
                /** @var BaseModel $returnedModel */
                $returnedModel = $this->objectRepository->update($vtigerModel);

                $newChange = new ObjectChangeDAO(
                    VtigerCrmIntegration::NAME,
                    $changedObject->getMappedObject(),
                    $changedObject->getMappedObjectId(),
                    $changedObject->getObject(),
                    $changedObject->getObjectId()
                );

                //$newChange->setChangeDateTime($returnedModel->getModifiedTime());
                $updatedMappedObjects[] = $newChange;

//                var_dump($changedObject);
//                var_dump($updatedMappedObjects);
//                die();


//                var_dump($updatedMappedObjects); die();
                // Integration name and ID are stored in the change's mappedObject/mappedObjectId
//                $updatedMappedObjects[] = new UpdatedObjectMappingDAO(
//                    $changedObject,
//                    $changedObject->getObjectId(),
//                    $changedObject->getObject(),
//                    $returnedModel->getModifiedTime()
//                );

                DebugLogger::log(
                    VtigerCrmIntegration::NAME,
                    sprintf(
                        "Updated to %s ID %s",
                        self::OBJECT_NAME,
                        $integrationObjectId
                    ),
                    __CLASS__ . ':' . __FUNCTION__
                );
            } catch (InvalidArgumentException $e) {
                DebugLogger::log(
                    VtigerCrmIntegration::NAME,
                    sprintf(
                        "Update to %s ID %s failed: %s",
                        self::OBJECT_NAME,
                        $integrationObjectId,
                        $e->getMessage()
                    ),
                    __CLASS__ . ':' . __FUNCTION__
                );
            }
        }

        return $updatedMappedObjects;
    }

}