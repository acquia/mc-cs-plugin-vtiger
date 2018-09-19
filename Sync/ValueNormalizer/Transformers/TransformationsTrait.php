<?php
declare(strict_types=1);

/*
 * @copyright   2018 Mautic Inc. All rights reserved
 * @author      Mautic, Inc. Jan Kozak <galvani78@gmail.com>
 *
 * @link        http://mautic.com
 * @created     17.9.18
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Sync\ValueNormalizer\Transformers;


use MauticPlugin\IntegrationsBundle\Sync\DAO\Value\NormalizedValueDAO;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidObjectValueException;

trait TransformationsTrait
{
    private $transformations = [
        NormalizedValueDAO::EMAIL_TYPE      => [
            'func' => 'transformEmail',
        ],
        NormalizedValueDAO::STRING_TYPE     => [
            'func' => 'transformString',
        ],
        NormalizedValueDAO::PHONE_TYPE      => [
            'func' => 'transformPhone',
        ],
        NormalizedValueDAO::BOOLEAN_TYPE    => [
            'func' => 'transformBoolean',
        ],
        TransformerInterface::PICKLIST_TYPE => [
            'func' => 'transformPicklist',
        ],
    ];

    public function transform($type, $value)
    {
        if (!isset($this->transformations[$type])) {
            throw new InvalidQueryArgumentException(sprintf('Unknown type "%s", cannot transform.', $type));
        }

        $transformationMethod = $this->transformations[$type]['func'];
        $transformedValue     = $this->$transformationMethod($value);
        printf("transforming '%s' of type %s to '%s'.\n", $value, $type, $transformedValue);

        if (
            is_null($transformedValue)
            && isset($this->transformations['func']['required'])
            && $this->transformations['func']['required']
        ) {
            throw new InvalidObjectValueException("Required property has null value", $transformedValue, $type);
        }

        return new NormalizedValueDAO($type, $value, $transformedValue);
    }

    protected function transformEmail($value)
    {
        if (is_null($value) || strlen(trim($value)) === 0) {
            return null;
        }
        $value = $this->transformString($value);

        return $value;
    }

    protected function transformString($value)
    {
        if (is_null($value)) {
            return $value;
        }

        return (string)$value;
    }

    protected function transformBoolean($value)
    {
        if (is_null($value)) {
            return $value;
        }

        return intval((bool)$value);
    }

    protected function transformPhone($value)
    {
        return $this->transformString($value);
    }

    protected function transformPicklist($value)
    {
        return $this->transformString($value);
    }
}