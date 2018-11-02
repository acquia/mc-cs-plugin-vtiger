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

use MauticPlugin\MauticVtigerCrmBundle\Sync\AccountDataExchange;
use MauticPlugin\MauticVtigerCrmBundle\Sync\ContactDataExchange;
use MauticPlugin\MauticVtigerCrmBundle\Sync\LeadDataExchange;

/**
 * @todo Do not rename items - that one with Uppercase first letter (e.g. Contacts) are used when calling an API query too - need to rewrite and also check others
 */
final class CacheEnum
{
    /**
     * @var string
     */
    public const CACHE_NAMESPACE = 'vtigercrm_repo';

    /**
     * @var string
     */
    public const ACCOUNT = AccountDataExchange::OBJECT_NAME;

    /**
     * @var string
     */
    public const COMPANY_DETAILS = 'company_details';

    /**
     * @var string
     */
    public const CONTACT = ContactDataExchange::OBJECT_NAME;

    /**
     * @var string
     */
    public const EVENT = 'Events';

    /**
     * @var string
     */
    public const LEAD = LeadDataExchange::OBJECT_NAME;

    /**
     * @var string
     */
    public const USER = 'Users';
}
