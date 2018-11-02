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
use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Order\FieldDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Value\NormalizedValueDAO;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\ModuleFieldInfo;

final class MauticVtigerTransformer implements TransformerInterface
{
    use TransformationsTrait {
        transform as protected commonTransform;
    }

    /**
     * @var ModuleFieldInfo
     */
    private $currentFieldInfo;

    /**
     * @param $vtigerValue
     *
     * @return int
     */
    protected function transformDNC($vtigerValue)
    {
        return $vtigerValue ? DoNotContact::UNSUBSCRIBED : DoNotContact::IS_CONTACTABLE;
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    protected function transformMultiPicklist($value)
    {
        return $value;
    }

    /**
     * @param $fieldInfo
     * @param mixed $value
     *
     * @return NormalizedValueDAO
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidObjectValueException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException
     */
    public function transform($fieldInfo, $value): NormalizedValueDAO
    {
        $this->setCurrentFieldInfo($fieldInfo);

        $normalizedValue = $this->commonTransform($this->getCurrentFieldInfo()->getType()->getName(), $value);

        return $normalizedValue;
    }


    /**
     * @return ModuleFieldInfo
     */
    public function getCurrentFieldInfo(): ModuleFieldInfo
    {
        return $this->currentFieldInfo;
    }

    /**
     * @param ModuleFieldInfo $currentFieldInfo
     *
     * @return MauticVtigerTransformer
     */
    public function setCurrentFieldInfo(ModuleFieldInfo $currentFieldInfo): MauticVtigerTransformer
    {
        $this->currentFieldInfo = $currentFieldInfo;

        return $this;
    }

    /**
     * @param \DateTimeInterface $value
     *
     * @return string
     */
    protected function transformDate(\DateTimeInterface $value): string
    {
        $format = $this->getCurrentFieldInfo()->getType()->getFormat();

        $formatDictionary = [
            'dd'   => 'd',
            'mm'   => 'm',
            'yyyy' => 'Y',
        ];

        $formatString = str_replace(array_keys($formatDictionary), array_values($formatDictionary), $format);

        return $value->format($formatString);
    }

}
