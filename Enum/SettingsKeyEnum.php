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

namespace MauticPlugin\MauticVtigerCrmBundle\Enum;

use Mautic\IntegrationsBundle\Integration\Interfaces\ConfigFormFeaturesInterface;

final class SettingsKeyEnum
{
    /**
     * @var string
     */
    public const PUSH_ACTIVITY_IS_ENABLED = ConfigFormFeaturesInterface::FEATURE_PUSH_ACTIVITY;

    /**
     * @var string
     */
    public const ACTIVITY_EVENTS = 'activityEvents';

    /**
     * @var string
     */
    public const OWNER = 'owner';

    /**
     * @var string
     */
    public const OWNER_UPDATE_IS_ENABLED = 'updateOwner';

    /**
     * @var string
     */
    public const UPDATE_DNC_BY_DATE = 'updateDncByDate';

    /**
     * @var string
     */
    public const PUSH_MAUTIC_CONTACT_AS = 'push_mautic_contact_as';

    /**
     * @var string
     */
    public const PUSH_MAUTIC_CONTACT_AS_LEAD = 'push_mautic_contact_as_lead';

    /**
     * @var string
     */
    public const PUSH_MAUTIC_CONTACT_AS_CONTACT = 'push_mautic_contact_as_contact';
}
