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

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\Direction;

class FieldDirectionFactory
{
    /**
     * @return LeadFieldDirection
     */
    public function getLeadFieldDirection(): LeadFieldDirection
    {
        return new LeadFieldDirection();
    }

    /**
     * @return ContactFieldDirection
     */
    public function getContactFieldDirection(): ContactFieldDirection
    {
        return new ContactFieldDirection();
    }

    /**
     * @return AccountFieldDirection
     */
    public function getAccountFieldDirection(): AccountFieldDirection
    {
        return new AccountFieldDirection();
    }
}
