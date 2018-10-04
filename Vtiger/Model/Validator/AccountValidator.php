<?php

declare(strict_types=1);

/*
 * @copyright   2018 Mautic Inc. All rights reserved
 * @author      Mautic, Inc. Jan Kozak <galvani78@gmail.com>
 *
 * @link        http://mautic.com
 * @created     17.9.18
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Validator;

use InvalidArgumentException;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Account;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\BaseModel;

final class AccountValidator implements ObjectValidatorInterface
{
    use ObjectValidatorTrait;

    /**
     * @param Account $object
     */
    public function validate(BaseModel $baseModel): void
    {
        if (!$baseModel instanceof BaseRepository::$moduleClassMapping[$this->objectRepository->getModuleFromRepositoryName]) {
            throw new InvalidArgumentException('This validator supports only Contact object');
        }

        $description = $this->objectRepository->describe();
        var_dump($description);
        die();
    }
}
