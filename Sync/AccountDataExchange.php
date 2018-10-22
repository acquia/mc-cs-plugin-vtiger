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

namespace MauticPlugin\MauticVtigerCrmBundle\Sync;

use Mautic\LeadBundle\Model\CompanyModel;
use Mautic\LeadBundle\Model\LeadModel;
use MauticPlugin\IntegrationsBundle\Entity\ObjectMapping;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Mapping\UpdatedObjectMappingDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Order\ObjectChangeDAO;
use MauticPlugin\IntegrationsBundle\Sync\Logger\DebugLogger;
use MauticPlugin\IntegrationsBundle\Sync\SyncDataExchange\MauticSyncDataExchange;
use MauticPlugin\IntegrationsBundle\Sync\ValueNormalizer\ValueNormalizerInterface;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException;
use MauticPlugin\MauticVtigerCrmBundle\Integration\Provider\VtigerSettingProvider;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration;
use MauticPlugin\MauticVtigerCrmBundle\Sync\Helpers\DataExchangeOperationsTrait;
use MauticPlugin\MauticVtigerCrmBundle\Sync\Helpers\DataExchangeReportTrait;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Validator\AccountValidator;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\AccountRepository;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\Mapping\ModelFactory;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

final class AccountDataExchange implements ObjectSyncDataExchangeInterface
{
    use DataExchangeOperationsTrait;
    use DataExchangeReportTrait;

    const OBJECT_NAME = 'Accounts';

    /** @var AccountRepository */
    private $objectRepository;

    /** @var ValueNormalizerInterface */
    private $valueNormalizer;

    /** @var LeadModel */
    private $model;

    /** @var VtigerSettingProvider */
    private $settings;

    /**
     * @var AccountValidator
     */
    private $objectValidator;

    /**
     * @var ModelFactory
     */
    private $modelFactory;

    /**
     * @param AccountRepository        $accountRepository
     * @param VtigerSettingProvider    $settingProvider
     * @param CompanyModel             $companyModel
     * @param ValueNormalizerInterface $valueNormalizer
     * @param AccountValidator         $accountValidator
     * @param ModelFactory             $modelFactory
     */
    public function __construct(
        AccountRepository $accountRepository,
        VtigerSettingProvider $settingProvider,
        CompanyModel $companyModel,
        ValueNormalizerInterface $valueNormalizer,
        AccountValidator $accountValidator,
        ModelFactory $modelFactory
    )
    {
        $this->objectRepository = $accountRepository;
        $this->settings         = $settingProvider;
        $this->model            = $companyModel;
        $this->valueNormalizer  = $valueNormalizer;
        $this->objectValidator  = $accountValidator;
        $this->modelFactory     = $modelFactory;
    }

    /**
     * @param \DateTimeImmutable $fromDate
     * @param array              $mappedFields
     *
     * @return array|mixed
     *
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     */
    protected function getReportPayload(\DateTimeImmutable $fromDate, array $mappedFields)
    {
        $fullReport = [];
        $iteration = 0;
        // We must iterate while there is still some result left

        do {
            $report = $this->objectRepository->query('SELECT * FROM '.self::OBJECT_NAME
                .' LIMIT '.($iteration * 100).',100');

            ++$iteration;

            $fullReport = array_merge($fullReport, $report);
        } while (count($report));

        return $fullReport;
    }
}
