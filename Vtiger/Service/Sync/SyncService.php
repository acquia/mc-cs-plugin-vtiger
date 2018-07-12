<?php
declare(strict_types=1);

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Service\Sync;

use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Account;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Contact;
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
     * @param string $object
     * @param int[]  $ids
     * @param int[]  $columns
     *
     * @return ObjectDAO[]
     */
    public function getSyncObjects(string $object, array $ids, array $columns): array
    {

    }

    /**
     * @param ObjectDAO $objectDAO
     */
    public function syncObject(ObjectDAO $objectDAO)
    {
        $object = $objectDAO->getObject();

        $objectId = $objectDAO->getObjectId();
        $updateObject->setId($objectId);
        $fields = $objectDAO->getFields();
        foreach($fields as $field) {
            $fieldName = $field->getName();
            $fieldValue = $field->getValue();
            $updateObject->{'set' . ucwords($fieldName)}($fieldValue);
        }
        $repository->update($updateObject);
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
