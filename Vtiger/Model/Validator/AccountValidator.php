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

use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Account;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\BaseModel;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\AccountRepository;

class AccountValidator implements ObjectValidatorInterface
{
    /**
     * @var AccountRepository
     */
    private $accountRepository;

    /**
     * @var GeneralValidator
     */
    private $generalValidator;

    /**
     * @param AccountRepository $accountRepository
     * @param GeneralValidator  $generalValidator
     */
    public function __construct(AccountRepository $accountRepository, GeneralValidator $generalValidator)
    {
        $this->accountRepository = $accountRepository;
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
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function validate(BaseModel $object): void
    {
        if (!$object instanceof Account) {
            throw new \InvalidArgumentException('$object must be instance of Account');
        }

        $description = $this->accountRepository->describe()->getFields();
        $this->generalValidator->validateObject($object, $description);
    }
}
