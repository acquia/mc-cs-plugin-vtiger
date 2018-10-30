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
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Contact;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\ContactRepository;

class ContactValidator implements ObjectValidatorInterface
{
    /**
     * @var ContactRepository
     */
    private $contactRepository;

    /**
     * @var GeneralValidator
     */
    private $generalValidator;

    /**
     * @param ContactRepository $contactRepository
     * @param GeneralValidator  $generalValidator
     */
    public function __construct(ContactRepository $contactRepository, GeneralValidator $generalValidator)
    {
        $this->contactRepository = $contactRepository;
        $this->generalValidator  = $generalValidator;
    }

    /**
     * @todo Get rid of a BaseModel typehint if possible
     *
     * @param BaseModel $object
     *
     * @throws \MauticPlugin\IntegrationsBundle\Exception\PluginNotConfiguredException
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
        if (!$object instanceof Contact) {
            throw new \InvalidArgumentException('$object must be instance of Contact');
        }

        $description = $this->contactRepository->describe()->getFields();
        $this->generalValidator->validateObject($object, $description);
    }
}
