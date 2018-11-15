<?php
declare(strict_types=1);

/*
 * @copyright   2018 Mautic Inc. All rights reserved
 * @author      Mautic, Inc. Jan Kozak <galvani78@gmail.com>
 *
 * @link        http://mautic.com
 * @created     26.10.18
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Tests\TestDataProvider;


use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Contact;

class VtigerContactTestDataProvider
{
    private static $updatedVtiger = [
        [
            'id'                  => "12x5799",
            'modifiedtime'        => "2018-11-09 12:40:14",
            'assigned_user_id'    => "19x1",
            'lastname'            => "Picklister C 2 Updated",
            'email'               => "info@mautic.com",
            'firstname'           => "Matching",
            'mobile'              => "1978986453",
            'cf_892'              => "a",
            'cf_896'              => "a |##| c |##| e",
            'homephone'           => "1123123245",
            'mailingcity'         => "Boston",
            'mailingcountry'      => "USA",
            'mailingstate'        => "MA",
            'mailingstreet'       => "Road",
            'mailingzip'          => "B2345",
            'title'               => "Ing.",
            'emailoptout'         => "0",
            'isconvertedfromlead' => "0",
            'leadsource'          => "",
            'reference'           => "0",
            'source'              => "CRM",
            'contact_id'          => "",
            'donotcall'           => "0",
        ],
    ];

    private static $updatedNormalized = [
        '12x5799' => [
            'lastname' => "Picklister C 2 Updated",
            'email' => "",
            'firstname' => "Matching",
            'mobile' => "",
            'cf_892' => "a",
            'cf_896' => "a|c|e",
            'homephone' => "",
            'mailingcity' => "",
            'mailingcountry' => "",
            'mailingstate' => "",
            'mailingstreet' => "",
            'mailingzip' => "",
            'title' => "",
            'emailoptout' => "0",
            'isconvertedfromlead' => "0",
            'leadsource' => "",
            'reference' => "0",
            'source' => "CRM",
            'contact_id' => "",
            'donotcall' => "0",
        ]
    ];

    private static $mappedFields = [
        "lastname",
        "email",
        "firstname",
        "mobile",
        "cf_892",
        "cf_896",
        "homephone",
        "mailingcity",
        "mailingcountry",
        "mailingstate",
        "mailingstreet",
        "mailingzip",
        "title",
        "emailoptout",
    ];

    /**
     * @return Contact[]
     */
    public static function getVtigerContacts()
    {
        $contacts = [];
        foreach (self::$updatedVtiger as $contactData) {
            $contacts[] = new Contact($contactData);
        }

        return $contacts;
    }

    /**
     * @return array
     */
    public static function getMappedFields() {
        return self::$mappedFields;
    }

    public static function getUpdatedVtiger() {
        return self::$updatedVtiger;
    }

    public static function getUpdatedNormalized() {
        return self::$updatedNormalized;
    }
}