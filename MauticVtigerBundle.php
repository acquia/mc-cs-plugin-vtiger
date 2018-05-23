<?php

/*
 * @copyright   2016 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticVtigerBundle;

use Mautic\PluginBundle\Bundle\PluginBundleBase;
use Mautic\SmsBundle\DependencyInjection\Compiler\SmsTransportPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class MauticVtigerBundle
 *
 * @package MauticPlugin\MauticVtigerBundle
 */
class MauticVtigerBundle extends PluginBundleBase
{
    public function build(ContainerBuilder $container)
    {

    }
}
