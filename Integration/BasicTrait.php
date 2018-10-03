<?php

/*
 * @copyright   2018 Mautic Inc. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://www.mautic.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Integration;


trait BasicTrait
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return VtigerCrmIntegration::NAME;
    }

    /**
     * @return string
     */
    public function getDisplayName(): string
    {
        return 'vTiger';
    }

}