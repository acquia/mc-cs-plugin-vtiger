<?php
declare(strict_types=1);

/*
 * @copyright   2018 Mautic Inc. All rights reserved
 * @author      Mautic, Inc. Jan Kozak <galvani78@gmail.com>
 *
 * @link        http://mautic.com
 * @created     29.10.18
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Type;


/**
 * Class MultipicklistType
 *
 * @see
 *
 *
 *
 * @package MauticPlugin\MauticVtigerCrmBundle\Vtiger\Type
 */
class MultipicklistType
{
    /**
     * @var array
     */
    private $picklistValues;

    /**
     * @var string
     */
    private $defaultValue;

    /**
     * @var string
     */
    private $name = 'multipicklist';

    public function __construct(\stdClass $description)
    {
        foreach ($description->picklistValues as $picklistValueData) {
            $this->picklistValues[] = new PicklistValueType($picklistValueData);
        }
        var_dump($this->picklistValues);
        die();
    }
}
