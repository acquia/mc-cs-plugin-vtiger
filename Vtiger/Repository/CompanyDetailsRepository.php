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

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository;

use MauticPlugin\MauticVtigerCrmBundle\Enum\CacheEnum;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Contact;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\Helper\RepositoryHelper;

/**
 * Class CompanyDetailsRepository.
 */
class CompanyDetailsRepository extends BaseRepository
{
    use RepositoryHelper;

    /**
     * @param Contact $module
     *
     * @return Contact
     */
    public function create(Contact $module): Contact
    {
        return $this->createUnified($module);
    }

    /**
     * @param string $id
     *
     * @return Contact
     *
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException
     */
    public function retrieve(string $id): Contact
    {
        return $this->findOneBy(['id' =>$id]);
    }

    /**
     * @return string
     */
    public function getModuleFromRepositoryName(): string
    {
        return CacheEnum::COMPANY_DETAILS;
    }
}
