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

use BadMethodCallException;
use DateTimeImmutable;
use MauticPlugin\MagentoBundle\Exception\ObjectNotSupportedException;
use MauticPlugin\MagentoBundle\Integration\MagentoIntegration;
use MauticPlugin\MagentoBundle\Magento\DTO\Customer;
use MauticPlugin\MagentoBundle\Magento\Repository\CustomerRepository;
use MauticPlugin\MauticIntegrationsBundle\DAO\Sync\Order\OrderDAO;
use MauticPlugin\MauticIntegrationsBundle\DAO\Sync\Report\ReportDAO;
use MauticPlugin\MauticIntegrationsBundle\DAO\Sync\Request\RequestDAO;
use MauticPlugin\MauticIntegrationsBundle\Facade\SyncDataExchange\SyncDataExchangeInterface;

final class SyncDataExchange implements SyncDataExchangeInterface
{
    /**
     * @var SyncObjectBuilder
     */
    private $syncObjectBuilder;

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @param SyncObjectBuilder  $syncObjectBuilder
     * @param CustomerRepository $customerRepository
     */
    public function __construct(SyncObjectBuilder $syncObjectBuilder, CustomerRepository $customerRepository)
    {
        $this->syncObjectBuilder  = $syncObjectBuilder;
        $this->customerRepository = $customerRepository;
    }

    /**
     * This pushes to the integration objects that were updated/created in Mautic. The "sync order" is
     * created by the SyncProcess service.
     *
     * @param OrderDAO $syncOrderDAO
     *
     * @throws BadMethodCallException
     */
    public function executeSyncOrder(OrderDAO $syncOrderDAO): void
    {
        throw new BadMethodCallException('Mautic to Magento sync is not supported yet');
    }

    /**
     * This fetches objects from the integration that needs to be updated or created in Mautic.
     * A "sync report" is created that will be processed by the SyncProcess service.
     *
     * @param RequestDAO $requestDAO
     *
     * @return ReportDAO
     *
     * @throws ObjectNotSupportedException
     */
    public function getSyncReport(RequestDAO $requestDAO): ReportDAO
    {
        // Build a report of objects that have been modified
        $syncReport = new ReportDAO(MagentoIntegration::NAME);

        $requestedObjects = $requestDAO->getObjects();
        foreach ($requestedObjects as $requestedObject) {
            if (Customer::NAME === $requestedObject->getObject()) {
                $customers = $this->customerRepository->getCustomersSince(
                    new DateTimeImmutable($requestDAO->getFromTimestamp())
                );

                foreach ($customers as $customer) {
                    $syncReport->addObject($this->syncObjectBuilder->buildSyncObject($customer));
                }
            } else {
                throw new ObjectNotSupportedException(
                    "Magento integration does not support syncing '${objectName}' objects."
                );
            }
        }

        return $syncReport;
    }
}
