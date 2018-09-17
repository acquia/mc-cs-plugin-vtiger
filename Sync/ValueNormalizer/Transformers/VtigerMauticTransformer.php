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
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidArgumentException;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidObjectValueException;

final class VtigerMauticTransformer implements TransformerInterface
{
    use TransformationsTrait;

    private $transformations = [
        NormalizedValueDAO::EMAIL_TYPE => [
            'func' => 'transformEmail'
        ],
        NormalizedValueDAO::STRING_TYPE => [
            'func' => 'transformString'
        ],
        NormalizedValueDAO::PHONE_TYPE => [
            'func' => 'transformPhone'
        ]
    ];
}