<?php
/**
 * Created by PhpStorm.
 * User: jan
 * Date: 13.7.18
 * Time: 13:16
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Sync;


use Mautic\LeadBundle\Entity\LeadRepository;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\ContactRepository;

final class ContactSyncService
{
    /** @var ContactRepository  */
    private $remoteRepository;

    /** @var LeadRepository  */
    private $localRepository;

    /**
     * ContactSyncService constructor.
     *
     * @param ContactRepository $remoteRepository
     * @param LeadRepository    $localRepository
     */
    public function __construct(ContactRepository $remoteRepository, LeadRepository $localRepository) {
        $this->remoteRepository = $remoteRepository;
        $this->localRepository = $localRepository;
    }

    public function pushToRemote($contactId) {
        $this->localRepository->getContacts();
    }
}