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
class PicklistType extends CommonType
{
    /**
     * @var PicklistValueType[]
     */
    private $picklistValues;

    /**
     * @var string
     */
    private $defaultValue;

    /**
     * PicklistType constructor.
     *
     * @param \stdClass $description
     */
    public function __construct(\stdClass $description)
    {
        parent::__construct($description);

        foreach ($description->picklistValues as $picklistValueData) {
            $this->picklistValues[$picklistValueData->value] = new PicklistValueType($picklistValueData);
        }
        $this->defaultValue = $description->defaultValue;
    }

    /**
     * @param $value
     *
     * @return bool
     */
    public function valueExists($value) {
        return array_key_exists($value, $this->picklistValues);
    }

    /**
     * @return string
     */
    public function getDefaultValue()
    : string
    {
        return $this->defaultValue;
    }

    /**
     * @return PicklistValueType[]
     */
    public function getPicklistValues()
    : array
    {
        return $this->picklistValues;
    }

    /**
     * @return array
     */
    public function getPicklistValuesArray(): array {
        $response = [];
        foreach ($this->getPicklistValues() as $key=>$picklistValue) {
            $response[$picklistValue->getValue()] = $picklistValue->getLabel();
        }
        return $response;
    }
}
