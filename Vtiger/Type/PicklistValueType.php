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
class PicklistValueType
{
    /** @var string */
    private $label;

    /** @var string */
    private $value;

    public function __construct(\stdClass $description)
    {
        $this->value = $description->value;
        $this->label = $description->label;
    }

    /**
     * @return string
     */
    public function getLabel()
    : string
    {
        return $this->label;
    }

    /**
     * @param string $label
     *
     * @return PicklistValueType
     */
    public function setLabel(string $label)
    : PicklistValueType
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    : string
    {
        return $this->value;
    }

    /**
     * @param string $value
     *
     * @return PicklistValueType
     */
    public function setValue(string $value)
    : PicklistValueType
    {
        $this->value = $value;

        return $this;
    }


}
