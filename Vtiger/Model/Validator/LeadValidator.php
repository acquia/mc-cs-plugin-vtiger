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

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Validator;

use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\BaseModel;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Lead;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\LeadRepository;

class LeadValidator implements ObjectValidatorInterface
{
    /**
     * @var LeadRepository
     */
    private $leadRepository;

    /**
     * @var GeneralValidator
     */
    private $generalValidator;

    /**
     * @param LeadRepository   $leadRepository
     * @param GeneralValidator $generalValidator
     */
    public function __construct(LeadRepository $leadRepository, GeneralValidator $generalValidator)
    {
        $this->leadRepository   = $leadRepository;
        $this->generalValidator = $generalValidator;
    }

    /**
     * @param BaseModel $object
     *
     * @throws \Mautic\IntegrationsBundle\Exception\PluginNotConfiguredException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\AccessDeniedException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\DatabaseQueryException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidObjectException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\Validation\InvalidObject
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\VtigerPluginException
     */
    public function validate(BaseModel $object): void
    {
        if (!$object instanceof Lead) {
            throw new \InvalidArgumentException('$object must be instance of Lead');
        }

        $description = $this->leadRepository->describe()->getFields();
        $this->generalValidator->validateObject($object, $description);
    }
}
