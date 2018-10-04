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

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Factory;

use MauticPlugin\MauticSocialBundle\Entity\Lead;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Contact;

class ContactModelFactory
{
    public function createFromContact(Lead $lead): void
    {
    }

    public function createFromLead(Lead $lead): void
    {
    }

    /** This won'r stay here */
    public function mergeAndDecide(Lead $lead, Contact $contact)
    {
        return ['directions' => [
            'in' => $contact,
            'out'=> $lead,
        ]];
    }
}
