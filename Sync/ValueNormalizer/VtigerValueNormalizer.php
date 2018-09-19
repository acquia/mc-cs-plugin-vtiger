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
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidObjectValueException;
use MauticPlugin\MauticVtigerCrmBundle\Sync\ValueNormalizer\Transformers\MauticVtigerTransformer;
use MauticPlugin\MauticVtigerCrmBundle\Sync\ValueNormalizer\Transformers\VtigerMauticTransformer;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Validator\ContactValidator;

/**
 * Class ValueNormalizer
 */
final class VtigerValueNormalizer implements ValueNormalizerInterface
{
    /**
     * @var VtigerMauticTransformer
     */
    private $v2mTransformer;

    /**
     * @var MauticVtigerTransformer
     */
    private $m2vTransformer;

    public function __construct(VtigerMauticTransformer $v2mTransformer, MauticVtigerTransformer $m2vTransformer) {
        $this->v2mTransformer = $v2mTransformer;
        $this->m2vTransformer = $m2vTransformer;
    }

    public function normalizeForMautic(string $type, $value): NormalizedValueDAO
    {
        return $this->v2mTransformer->transform($type, $value);
    }

    /**
     * @param NormalizedValueDAO $value
     *
     * @return mixed
     */
    public function normalizeForIntegration(NormalizedValueDAO $value)
    {
        return $this->m2vTransformer->transform($value->getType(), $value);
    }
}
