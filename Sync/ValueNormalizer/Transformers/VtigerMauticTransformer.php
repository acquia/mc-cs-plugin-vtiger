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

namespace MauticPlugin\MauticVtigerCrmBundle\Sync\ValueNormalizer\Transformers;

use Mautic\LeadBundle\Entity\DoNotContact;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Value\NormalizedValueDAO;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidObjectValueException;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\ModuleFieldInfo;

/**
 * Class VtigerMauticTransformer
 *
 * @package MauticPlugin\MauticVtigerCrmBundle\Sync\ValueNormalizer\Transformers
 */
final class VtigerMauticTransformer implements TransformerInterface
{
    use TransformationsTrait;

    /**
     * @var ModuleFieldInfo
     */
    private $moduleFieldInfo;

    protected function transformDNC($mauticValue)
    {
        return $mauticValue ? DoNotContact::UNSUBSCRIBED : DoNotContact::IS_CONTACTABLE;
    }

    /**
     * @param $mauticValue
     *
     * @return null|string
     */
    protected function transformMultiPicklist($mauticValue)
    {
        if (is_null($mauticValue)) {
            return null;
        }
        $values = explode('|##|', $mauticValue);
        array_walk($values, function(&$element){
            $element = trim($element);
        });

        return $this->transformString(join('|', $values));
    }

    /**
     * @param \DateTimeInterface $value
     *
     * @return null|string
     */
    protected function transformDate($value): ?string
    {
        if (is_null($value) || $value === "" || $value === '0000-00-00') {
            return null;
        }

        $dateObject = \DateTime::createFromFormat('Y-m-d', $value);

        return $dateObject->format('Y-m-d');
    }

    /**
     * @param string $value
     *
     * @return null|string
     * @throws InvalidObjectValueException
     */
    protected function transformPicklist(string $value): ?string
    {
        if ($value === "" || is_null($value)) {
            return null;
        }

        $type = $this->moduleFieldInfo->getType();
        $dictionary = $type->getPicklistValuesArray();

        if(!isset($dictionary[$value])) {
            throw new InvalidObjectValueException(
                sprintf('Invalid picklist value. Available: [%s]', join(',', array_keys($dictionary))), (string)$value, $type->getName()
            );
        }

        return $this->transformString($value);
    }

    /**
     * @param ModuleFieldInfo $fieldInfo
     * @param                 $value
     *
     * @return NormalizedValueDAO
     * @throws InvalidObjectValueException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException
     */
    public function transformTyped(ModuleFieldInfo $fieldInfo, $value): NormalizedValueDAO {
        $this->moduleFieldInfo = $fieldInfo;

        return $this->transform($fieldInfo->getTypeName(), $value);
    }

}
