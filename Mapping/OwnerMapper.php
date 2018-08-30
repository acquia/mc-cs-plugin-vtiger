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

namespace MauticPlugin\MauticVtigerCrmBundle\Mapping;

use MauticPlugin\MauticVtigerCrmBundle\Exceptions\NoFieldException;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\AccountRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

class OwnerMapper
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var AccountRepository
     */
    private $accountRepository;

    /**
     * FieldMapping constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return array|mixed[]
     */
    public function getOwners(): array
    {
        $this->accountRepository = $this->container->get('mautic.vtiger_crm.repository.accounts');

        $accountsData = $this->accountRepository->findBy();

        $accounts = [];
        foreach ($accountsData as $account) {
            try {
                $accounts[$account->getId()] = $account->getAccountName();
            } catch (NoFieldException $e) {
            }
        }

        return $accounts;
    }
}
