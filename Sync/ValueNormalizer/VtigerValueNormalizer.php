<?php

declare(strict_types=1);

/*
 * @copyright   2018 Mautic Inc. All rights reserved
 * @author      Mautic, Inc. Jan Kozak <galvani78@gmail.com>
 *
 * @link        http://mautic.com
 * @created     ${DATE}
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Sync\ValueNormalizer;

use MauticPlugin\IntegrationsBundle\Sync\DAO\Value\NormalizedValueDAO;
use MauticPlugin\IntegrationsBundle\Sync\ValueNormalizer\ValueNormalizerInterface;
use MauticPlugin\MauticVtigerCrmBundle\Sync\ValueNormalizer\Transformers\MauticVtigerTransformer;
use MauticPlugin\MauticVtigerCrmBundle\Sync\ValueNormalizer\Transformers\VtigerMauticTransformer;

/**
 * Class ValueNormalizer.
 */
final class VtigerValueNormalizer implements ValueNormalizerInterface
{
    /**
     * @var VtigerMauticTransformer
     */
    private $vtigerMauticTransformer;

    /**
     * @var MauticVtigerTransformer
     */
    private $mauticVtigerTransformer;

    /**
     * VtigerValueNormalizer constructor.
     *
     * @param VtigerMauticTransformer $v2mTransformer
     * @param MauticVtigerTransformer $m2vTransformer
     */
    public function __construct(VtigerMauticTransformer $vtigerMauticTransformer, MauticVtigerTransformer $mauticVtigerTransformer)
    {
        $this->vtigerMauticTransformer = $vtigerMauticTransformer;
        $this->mauticVtigerTransformer = $mauticVtigerTransformer;
    }

    /**
     * @param string $type
     * @param        $value
     *
     * @return NormalizedValueDAO
     *
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidObjectValueException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException
     */
    public function normalizeForMautic(string $type, $value): NormalizedValueDAO
    {
        return $this->vtigerMauticTransformer->transform($type, $value);
    }

    /**
     * @param NormalizedValueDAO $value
     *
     * @return NormalizedValueDAO|mixed
     *
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidObjectValueException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException
     */
    public function normalizeForIntegration(NormalizedValueDAO $normalizedValueDAO)
    {
        return $this->mauticVtigerTransformer->transform($normalizedValueDAO->getType(), $normalizedValueDAO);
    }
}
