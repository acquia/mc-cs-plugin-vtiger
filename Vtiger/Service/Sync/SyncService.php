<?php
declare(strict_types=1);

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Service\Sync;

use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\AccountRepository;

/**
 * Class SyncService
 * @package MauticPlugin\MauticVtigerCrmBundle\Vtiger\Service\Sync
 */
final class SyncService implements SyncServiceInterface
{
    const ACCOUNT = 'account';
    const CONTACT = 'contact';

    /**
     * @var AccountRepository
     */
    private $accountRepository;

    /**
     * SyncService constructor.
     *
     * @param AccountRepository $accountRepository
     */
    public function __construct(AccountRepository $accountRepository)
    {
        $this->accountRepository = $accountRepository;
    }

    /**
     * @param IntegrationMappingManual $integrationMappingManual
     * @param int|null                 $fromTimestamp
     *
     * @return ReportDAO
     */
    public function getSyncReport(IntegrationMappingManual $integrationMappingManual, int $fromTimestamp = null): ReportDAO
    {
        $objectsMapping = $integrationMappingManual->getObjects();
        $syncLog = $this->generalRepository->getSyncLog($fromTimestamp);
        $syncReport = new ReportDAO();
        foreach($objectsMapping as $objectMapping) {
            $object = $objectMapping->getObject();
            $updatedIds = $syncLog->getUpdatedIds($object);
            $repository = $this->getObjectRepository($object);
            $fields = $objectMapping->getFields();
            /** @var BaseModel[] $currentObjects */
            $currentObjects = $repository->findBy(['id' => $updatedIds], $fields);
            foreach($currentObjects as $currentObject) {
                $objectChange = new ObjectChangeDAO();
                foreach($fields as $field) {
                    $fieldValue = $currentObject->{'get'. ucwords($field)}();
                    $objectChange->addField(new FieldDAO($field, $fieldValue));
                }
                $objectChange->setChangeTimestamp($syncLog->getObjectChangeTimestamp($object, $currentObject->getId()));
                $syncReport->addObject($objectChange);
            }
        }
        return $syncReport;
    }

    /**
     * @param ObjectDAO $objectDAO
     */
    public function syncObject(ObjectDAO $objectDAO)
    {
        $object = $objectDAO->getObject();

        $repository = $this->getObjectRepository($object);
        $updateModel = $repository->getEmptyModel();
        $objectId = $objectDAO->getObjectId();
        $updateModel->setId($objectId);
        $fields = $objectDAO->getFields();
        foreach($fields as $field) {
            $fieldName = $field->getName();
            $fieldValue = $field->getValue();
            $updateModel->{'set' . ucwords($fieldName)}($fieldValue);
        }
        $repository->update($updateModel);
    }

    /**
     * @param string $object
     */
    private function getObjectRepository(string $object)
    {
        switch($object) {
            case self::ACCOUNT:
                return $this->accountRepository;
                break;
            case self::CONTACT:
                return $this->contactRepository;
                break;
            default:
                throw new \InvalidArgumentException();
        }
    }
}
