<?php
declare(strict_types=1);

/*
 * @copyright   2018 Mautic Inc. All rights reserved
 * @author      Mautic, Inc. Jan Kozak <galvani78@gmail.com>
 *
 * @link        http://mautic.com
 * @created     26.10.18
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Tests\TestDataProvider;


class ModulesDescriptionProvider
{
    public static function getLead() {
        $serialized = file_get_contents(__DIR__ . '/data/serializedLeadDescriptionObject.txt');
        return unserialize($serialized);
    }
}