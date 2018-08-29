<?php

/*
 * @copyright   2018 Mautic Inc. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://www.mautic.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Sync\ValueNormalizer;

use MauticPlugin\IntegrationsBundle\Sync\DAO\Value\NormalizedValueDAO;
use MauticPlugin\IntegrationsBundle\Sync\ValueNormalizer\ValueNormalizerInterface;

/**
 * Class ValueNormalizer
 */
final class VtigerValueNormalizer implements ValueNormalizerInterface
{
    /**
     * @param string $type
     * @param mixed  $value
     *
     * @return NormalizedValueDAO
     */
    public function normalizeForMautic(string $type, $value): NormalizedValueDAO
    {
        switch ($type) {
            case NormalizedValueDAO::STRING_TYPE:
                return new NormalizedValueDAO($type, $value, (string) $value);
            case NormalizedValueDAO::INT_TYPE:
                return new NormalizedValueDAO($type, $value, (int) $value);
            case NormalizedValueDAO::FLOAT_TYPE:
                return new NormalizedValueDAO($type, $value, (float) $value);
            case NormalizedValueDAO::DOUBLE_TYPE:
                return new NormalizedValueDAO($type, $value, (double) $value);
            case NormalizedValueDAO::DATETIME_TYPE:
                return new NormalizedValueDAO($type, $value, new \DateTime($value));
            case NormalizedValueDAO::BOOLEAN_TYPE:
                return new NormalizedValueDAO($type, $value, (bool) $value);
            case 'reference':
            case 'phone':
            case 'picklist':
            case 'date':
            case 'email':
            case 'owner':
            case 'text':
                $normalized = new NormalizedValueDAO($type, $value, (string) $value);
                printf("normalizing unknown type '%s', '%s'=>'%s'\n", $type, $value, $normalized->getNormalizedValue());
                return $normalized;
            default:
                throw new \InvalidArgumentException('Variable type, '.$type.', not supported');
        }
    }

    /**
     * @param NormalizedValueDAO $value
     *
     * @return mixed
     */
    public function normalizeForIntegration(NormalizedValueDAO $value)
    {
        return $value->getNormalizedValue();
    }
}
