<?php

declare(strict_types=1);

/*
 * @copyright   2018 Mautic Inc. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Integration;

use MauticPlugin\IntegrationsBundle\Integration\BasicIntegration;
use MauticPlugin\IntegrationsBundle\Integration\Interfaces\BasicInterface;
use MauticPlugin\IntegrationsBundle\Integration\Interfaces\IntegrationInterface;

/**
 * Class VtigerCrmIntegration
 */
class VtigerCrmIntegration extends BasicIntegration implements BasicInterface, IntegrationInterface
{
    use BasicTrait;

    const NAME = 'VtigerCrm';

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return 'plugins/MauticVtigerCrmBundle/Assets/img/vtiger_crm.png';
    }
}
