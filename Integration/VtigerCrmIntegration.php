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

namespace MauticPlugin\MauticVtigerCrmBundle\Integration;

use Mautic\IntegrationsBundle\Integration\BasicIntegration;
use Mautic\IntegrationsBundle\Integration\Interfaces\BasicInterface;
use Mautic\IntegrationsBundle\Integration\Interfaces\IntegrationInterface;

/**
 * Class VtigerCrmIntegration.
 */
class VtigerCrmIntegration extends BasicIntegration implements BasicInterface, IntegrationInterface
{
    use BasicTrait;

    const NAME = 'VtigerCrm';
    const DISPLAY_NAME = 'Vtiger CRM';
    /**
     * @return string
     */
    public function getIcon(): string
    {
        return 'plugins/MauticVtigerCrmBundle/Assets/img/vtiger_crm.png';
    }
}
