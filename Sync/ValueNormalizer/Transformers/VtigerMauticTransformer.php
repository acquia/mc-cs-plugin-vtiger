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

final class VtigerMauticTransformer implements TransformerInterface
{
    use TransformationsTrait;

    protected function transformDNC($mauticValue)
    {
        return $mauticValue ? DoNotContact::UNSUBSCRIBED : DoNotContact::IS_CONTACTABLE;
    }

    protected function transformMultiPicklist($mauticValue) {
        var_dump($mauticValue);
        return $this->transformString($mauticValue);
    }
}
