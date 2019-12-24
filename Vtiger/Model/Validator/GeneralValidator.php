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

use MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidObjectException;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\Validation\InvalidObject;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\BaseModel;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\ModuleFieldInfo;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Validator\Constraints\Date;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Validator\Constraints\MultiChoice;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\UserRepository;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Type\CommonType;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Type\DateType;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Type\PicklistType;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Time;
use Symfony\Component\Validator\Validation;

class GeneralValidator
{
    /** @var UserRepository */
    private $userRepository;

    /** @var \Symfony\Component\Validator\ValidatorInterface */
    private $validator;

    /** @var array */
    private $existingUsersIds = [];

    /**
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        $this->validator      = Validation::createValidator();
    }

    /**
     * @param BaseModel $object
     * @param array     $description
     *
     * @throws InvalidObject
     * @throws InvalidObjectException
     * @throws \Mautic\IntegrationsBundle\Exception\PluginNotConfiguredException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\AccessDeniedException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\DatabaseQueryException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\VtigerPluginException
     */
    public function validateObject(BaseModel $object, array $description): void
    {
        foreach ($object->dehydrate() as $fieldName => $fieldValue) {
            $fieldDescription = $description[$fieldName];
            $this->validateField($fieldDescription, $fieldValue);
        }
    }

    /**
     * @param ModuleFieldInfo $fieldInfo
     * @param                 $fieldValue
     *
     * @throws InvalidObject
     * @throws InvalidObjectException
     * @throws \Mautic\IntegrationsBundle\Exception\PluginNotConfiguredException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\AccessDeniedException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\DatabaseQueryException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\VtigerPluginException
     */
    private function validateField(ModuleFieldInfo $fieldInfo, $fieldValue): void
    {
        $validators = [];
        if (!$fieldInfo->isNullable() && $fieldInfo->isRequired() && null === $fieldValue) {
            $validators[] = new NotNull();
        }

        //  Validate by data type
        $validators = array_merge($validators, $this->getValidatorsForType($fieldInfo->getType(), $fieldValue));

        if (!count($validators)) {
            return;
        }

        //  Validate for required fields
        $violations = $this->validator->validate($fieldValue, $validators);
        if (!count($violations)) {
            return;
        }

        throw new InvalidObject($violations, $fieldInfo, $fieldValue);
    }

    /**
     * @param CommonType $typeObject
     * @param            $fieldValue
     *
     * @return array
     * @throws InvalidObjectException
     * @throws \Mautic\IntegrationsBundle\Exception\PluginNotConfiguredException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\AccessDeniedException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\DatabaseQueryException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\VtigerPluginException
     */
    private function getValidatorsForType(CommonType $typeObject, $fieldValue): array
    {
        $validators = [];

        switch ($typeObject->getName()) {
            case 'autogenerated':
            case 'string':
            case 'phone':
            case 'text':
            case 'double':
            case 'integer':
            case 'skype':
            case 'url':
                break;
            case 'email':
                $validators[] = new Email();
                break;
            case 'owner':
                if (!count($this->existingUsersIds)) {
                    $users                  = $this->userRepository->findBy();
                    $this->existingUsersIds = array_map(function (BaseModel $o) { return $o->getId(); }, $users);
                }

                $validators[] = new Choice(['choices' => $this->existingUsersIds]);
                break;
            case 'reference':
                break;
            case 'boolean':
                break;
            case 'picklist':
                /** @var PicklistType $typeObject */
                $validators[] = new Choice(['choices' => $typeObject->getPicklistValuesArray()]);
                break;
            case 'multipicklist':
                /** @var PicklistType $typeObject */
                $validators[] = new MultiChoice(['choices' => $typeObject->getPicklistValuesArray(), 'multiple' => true]);
                break;
            case 'date':
                /** @var DateType $typeObject */
                $validators[] = new Date(['format'=>$typeObject->getFormat()]);
                break;
            case 'currency':
                break;
            case 'time':
                $validators[] = new Time();
                break;
            default:
                throw new InvalidObjectException('Validation: Unknown field type '. $typeObject->getName());
        }

        return $validators;
    }
}
