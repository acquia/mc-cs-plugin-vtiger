<?php
declare(strict_types=1);

/*
 * @copyright   2018 Mautic Inc. All rights reserved
 * @author      Mautic, Inc. Jan Kozak <galvani78@gmail.com>
 *
 * @link        http://mautic.com
 * @created     7.9.18
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository;

use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Contact;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\Helper\RepositoryHelper;

/**
 * Class ContactRepository
 *
 * @package MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository
 */
class ContactRepository extends BaseRepository
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
     */
    public function retrieve(string $id): Contact
    {
        $record = $this->findOneBy(['id'=>$id]);

        return $record;
    }
}