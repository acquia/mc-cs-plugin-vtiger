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

interface TransformerInterface
{
    const PICKLIST_TYPE = 'picklist';
    const REFERENCE_TYPE = 'reference';
    const DNC_TYPE = 'dnc';

    public function transform($type, $value);
}
