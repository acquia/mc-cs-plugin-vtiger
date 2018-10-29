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

namespace MauticPlugin\MauticVtigerCrmBundle\Sync\ValueNormalizer\Transformers;

use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Order\FieldDAO;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\AccountRepository;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\ContactRepository;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\LeadRepository;

final class MauticVtigerTransformer implements TransformerInterface
{
    use TransformationsTrait;

    /**
     * @var LeadRepository
     */
    private $leadRepository;
    /**
     * @var ContactRepository
     */
    private $contactRepository;
    /**
     * @var AccountRepository
     */
    private $accountRepository;

    /**
     * MauticVtigerTransformer constructor.
     *
     * @param LeadRepository    $leadRepository
     * @param ContactRepository $contactRepository
     * @param AccountRepository $accountRepository
     */
    public function __construct(LeadRepository $leadRepository, ContactRepository $contactRepository, AccountRepository $accountRepository)
    {

        $this->leadRepository    = $leadRepository;
        $this->contactRepository = $contactRepository;
        $this->accountRepository = $accountRepository;
    }

    protected function transformDNC($vtigerValue)
    {
        return $vtigerValue ? DoNotContact::UNSUBSCRIBED : DoNotContact::IS_CONTACTABLE;
    }

    /**
     * @param $value
     *
     * @return null|string
     */
    protected function transformString($value): ?string
    {
        var_dump($value); die();
    }


}
