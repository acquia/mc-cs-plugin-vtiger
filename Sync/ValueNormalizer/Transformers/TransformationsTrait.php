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

use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Order\FieldDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Value\NormalizedValueDAO;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidObjectValueException;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException;

/**
 * Trait TransformationsTrait.
 */
trait TransformationsTrait
{
    private $transformations = [
        NormalizedValueDAO::EMAIL_TYPE       => [
            'func' => 'transformEmail',
        ],
        NormalizedValueDAO::STRING_TYPE      => [
            'func' => 'transformString',
        ],
        NormalizedValueDAO::PHONE_TYPE       => [
            'func' => 'transformPhone',
        ],
        NormalizedValueDAO::BOOLEAN_TYPE     => [
            'func' => 'transformBoolean',
        ],
        TransformerInterface::PICKLIST_TYPE  => [
            'func' => 'transformPicklist',
        ],
        TransformerInterface::REFERENCE_TYPE => [
            'func' => 'transformReference',
        ],
        TransformerInterface::DNC_TYPE       => [
            'func' => 'transformDNC',
        ],
        NormalizedValueDAO::INT_TYPE         => [
            'func' => 'transformInt',
        ],
        NormalizedValueDAO::DATE_TYPE        => [
            'func' => 'transformDate',
        ],
        TransformerInterface::CURRENCY_TYPE  => [
            'func' => 'transformCurrency',
        ],
        NormalizedValueDAO::DOUBLE_TYPE      => [
            'func' => 'transformDouble'
        ],
        NormalizedValueDAO::TEXT_TYPE      => [
            'func' => 'transformString'
        ],
        TransformerInterface::INTEGER_TYPE      => [
            'func' => 'transformInt'
        ]
    ];

    /**
     * @param $type
     * @param $value
     *
     * @return NormalizedValueDAO
     * @throws InvalidObjectValueException
     * @throws InvalidQueryArgumentException
     */
    public function transform($type, $value): NormalizedValueDAO
    {
        if (!isset($this->transformations[$type])) {
            throw new InvalidQueryArgumentException(sprintf('Unknown type "%s", cannot transform. Value type: "%s"', $type, var_export($value, true)));
        }

        $transformationMethod = $this->transformations[$type]['func'];
        $transformedValue     = $this->$transformationMethod($value);

        if (
            is_null($transformedValue)
            && isset($this->transformations['func']['required'])
            && $this->transformations['func']['required']
        ) {
            throw new InvalidObjectValueException('Required property has null value', $transformedValue, $type);
        }

        return new NormalizedValueDAO($type, $value, $transformedValue);
    }

    /**
     * @param $value
     *
     * @return null|string
     */
    protected function transformEmail($value): ?string
    {
        if (is_null($value) || 0 === strlen(trim($value))) {
            return null;
        }
        $value = $this->transformString($value);

        return $value;
    }

    /**
     * @param $value
     *
     * @return null|string
     */
    protected function transformString($value): ?string
    {
        if (is_null($value)) {
            return $value;
        }

        return (string)$value;
    }

    /**
     * @param $value
     *
     * @return int|null
     */
    protected function transformBoolean($value): ?int
    {
        if (is_null($value)) {
            return $value;
        }

        return intval((bool)$value);
    }

    /**
     * @param $value
     *
     * @return null|string
     */
    protected function transformPhone($value): ?string
    {
        return $this->transformString($value);
    }

    /**
     * @param $value
     *
     * @return null|string
     */
    protected function transformPicklist($value): ?string
    {
        return $this->transformString($value);
    }

    /**
     * @param $value
     *
     * @return null|string
     */
    protected function transformReference($value): ?string
    {
        return $this->transformString($value);
    }

    /**
     * @param $value
     *
     * @return null|string
     */
    protected function transformDate($value): ?string
    {
        return $this->transformString($value);
    }

    /**
     * @param $value
     *
     * @return int|null
     */
    protected function transformInt($value): ?int
    {
        return intval($value);
    }

    /**
     * @param $value
     *
     * @return string
     */
    protected function transformCurrency($value): string
    {
        return number_format(floatval($value), 2);
    }

    /**
     * @param $value
     *
     * @return float
     */
    protected function transformDouble($value): float
    {
        return doubleval($value);
    }
}
