<?php
/**
 * Created by PhpStorm.
 * User: jan
 * Date: 8.8.18
 * Time: 13:56
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Factory;


use MauticPlugin\MauticSocialBundle\Entity\Lead;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Contact;

class ContactModelFactory
{
    public function createFromContact(Lead $contact) {

    }

    public function createFromLead(Lead $contact) {

    }

    /** This won'r stay here */
    public function mergeAndDecide(Lead $mauticContact, Contact $contact) {

        return ['directions' => ['in'=>$contact,'out'=>$mauticContact]];
    }
}