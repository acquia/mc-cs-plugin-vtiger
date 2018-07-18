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
     * @param RequestDAO $requestDAO
     *
     * @return ReportDAO
     */
    public function getSyncReport(RequestDAO $requestDAO): ReportDAO
    {
        $requestObjects = $requestDAO->getObjects();
        $syncLog = $this->generalRepository->getSyncLog($requestDAO->getFromTimestamp());
        $reportDAO = new ReportDAO();
        foreach($requestObjects as $requestObject) {
            $object = $requestObject->getObject();
            $updatedIds = $syncLog->getUpdatedIds($object);
            $repository = $this->getObjectRepository($object);
            $fields = $requestObject->getFields();
            /** @var BaseModel[] $currentObjects */
            $currentObjects = $repository->findBy(['id' => $updatedIds], $fields);
            foreach($currentObjects as $currentObject) {
                $reportObjectDAO = new ReportObjectDAO();
                foreach($fields as $field) {
                    $fieldValue = $currentObject->{'get'. ucwords($field)}();
                    $reportObjectDAO->addField(new ReportFieldDAO($field, $fieldValue));
                }
                $reportObjectDAO->setChangeTimestamp($syncLog->getObjectChangeTimestamp($object, $currentObject->getId()));
                $reportDAO->addObject($reportObjectDAO);
            }
        }
        return $reportDAO;
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
