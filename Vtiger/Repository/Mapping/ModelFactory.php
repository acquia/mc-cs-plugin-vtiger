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

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\Mapping;

use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Account;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\BaseModel;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Contact;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Event;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Lead;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\User;

class ModelFactory
{
    private static $moduleClassMapping = [
        'Contacts'       => Contact::class,
        'Accounts'       => Account::class,
        'Events'         => Event::class,
        'Leads'          => Lead::class,
        'Users'          => User::class,
    ];

    /**
     * @param array $data
     *
     * @return Account
     */
    public function createAccount(array $data): Account
    {
        return new Account($data);
    }

    /**
     * @param array $data
     *
     * @return Event
     */
    public function createEvent(array $data): Event
    {
        return new Event($data);
    }

    /**
     * @todo Refactor all things so we do not need to use this static method
     *
     * @param string $key
     * @param array  $data
     *
     * @return BaseModel|Contact|Account|Event|Lead|User
     */
    public static function getModel(string $key, array $data): BaseModel
    {
        return new self::$moduleClassMapping[$key]($data);
    }

    /**
     * @todo Refactor all things so we do not need to use this static method
     *
     * @param string $key
     *
     * @return bool
     */
    public static function isObjectSupported(string $key): bool
    {
        return isset(self::$moduleClassMapping[$key]);
    }
}
