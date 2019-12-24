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

namespace MauticPlugin\MauticVtigerCrmBundle\Sync\ValueNormalizer;

use Mautic\IntegrationsBundle\Sync\DAO\Sync\Order\FieldDAO;
use Mautic\IntegrationsBundle\Sync\DAO\Value\NormalizedValueDAO;
use Mautic\IntegrationsBundle\Sync\ValueNormalizer\ValueNormalizerInterface;
use MauticPlugin\MauticVtigerCrmBundle\Sync\ValueNormalizer\Transformers\MauticVtigerTransformer;
use MauticPlugin\MauticVtigerCrmBundle\Sync\ValueNormalizer\Transformers\VtigerMauticTransformer;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\ModuleFieldInfo;

/**
 * Class ValueNormalizer.
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

    /**
     * VtigerValueNormalizer constructor.
     *
     * @param VtigerMauticTransformer $v2mTransformer
     * @param MauticVtigerTransformer $m2vTransformer
     */
    public function __construct(VtigerMauticTransformer $v2mTransformer, MauticVtigerTransformer $m2vTransformer)
    {
        $this->v2mTransformer = $v2mTransformer;
        $this->m2vTransformer = $m2vTransformer;
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
        return $this->v2mTransformer->transform($type, $value);
    }

    /**
     * @param ModuleFieldInfo $fieldInfo
     * @param                 $value
     *
     * @return NormalizedValueDAO
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidObjectValueException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException
     */
    public function normalizeForMauticTyped(ModuleFieldInfo $fieldInfo, $value): NormalizedValueDAO
    {
        return $this->v2mTransformer->transformTyped($fieldInfo, $value);
    }

    /**
     * @inheritdoc
     */
    public function normalizeForIntegration(NormalizedValueDAO $value)
    {
        throw new \Exception('Use normalizeForVtiger instead');
    }

    /**
     * @param ModuleFieldInfo $fieldInfo
     * @param FieldDAO        $fieldDAO
     *
     * @return mixed
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidObjectValueException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException
     */
    public function normalizeForVtiger(ModuleFieldInfo $fieldInfo, FieldDAO $fieldDAO)
    {
        return $this->m2vTransformer->transform($fieldInfo, $fieldDAO->getValue()->getOriginalValue())->getNormalizedValue();
    }
}
