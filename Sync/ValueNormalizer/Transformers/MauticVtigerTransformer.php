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

use Mautic\LeadBundle\Entity\DoNotContact;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Value\NormalizedValueDAO;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\ModuleFieldInfo;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\AccountRepository;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\ContactRepository;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\LeadRepository;

final class MauticVtigerTransformer implements TransformerInterface
{
    use TransformationsTrait {
        transform as protected commonTransform;
    }

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
     * @var ModuleFieldInfo
     */
    private $currentFieldInfo;

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

    /**
     * @param $vtigerValue
     *
     * @return int
     */
    protected function transformDNC($vtigerValue)
    {
        return $vtigerValue ? DoNotContact::UNSUBSCRIBED : DoNotContact::IS_CONTACTABLE;
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    protected function transformMultiPicklist($value) {
        var_dump($value);
        var_dump($this->getCurrentFieldInfo());
        return $value;
    }

    /**
     * @param $fieldInfo
     * @param $value
     *
     * @return NormalizedValueDAO
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidObjectValueException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException
     */
    public function transform($fieldInfo, $value): NormalizedValueDAO
    {
        $this->setCurrentFieldInfo($fieldInfo);
        $normalizedValue = $this->commonTransform($this->getCurrentFieldInfo()->getType()->getName(), $value->getValue()->getOriginalValue());
        return $normalizedValue;
    }


    /**
     * @return ModuleFieldInfo
     */
    public function getCurrentFieldInfo(): ModuleFieldInfo
    {
        return $this->currentFieldInfo;
    }

    /**
     * @param ModuleFieldInfo $currentFieldInfo
     *
     * @return MauticVtigerTransformer
     */
    public function setCurrentFieldInfo(ModuleFieldInfo $currentFieldInfo): MauticVtigerTransformer
    {
        $this->currentFieldInfo = $currentFieldInfo;

        return $this;
    }

}
