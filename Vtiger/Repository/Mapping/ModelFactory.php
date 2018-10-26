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
     * @return Lead
     */
    public function createLead(array $data): Lead
    {
        return new Lead($data);
    }

    /**
     * @param array $data
     *
     * @return Contact
     */
    public function createContact(array $data): Contact
    {
        return new Contact($data);
    }

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
     * @param array $data
     *
     * @return User
     */
    public function createUser(array $data): User
    {
        return new User($data);
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
