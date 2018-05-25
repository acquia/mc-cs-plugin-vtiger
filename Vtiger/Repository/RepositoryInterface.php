<?php

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository;

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author      Jan Kozak <galvani78@gmail.com>
 */

use MauticPlugin\MauticVtigerCrmBundle\Module\ModuleInterface;

interface RepositoryInterface
{
    public function create(ModuleInterface $module): ModuleInterface;

    public function retrieve($id): ModuleInterface;

    public function update(ModuleInterface $module): ModuleInterface;

    public function delete(ModuleInterface $module);

}