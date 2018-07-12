<?php
declare(strict_types=1);

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Ability\Update;

use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\BaseModel;

/**
 * Interface UpdateAbilityInterface
 * @package MauticPlugin\MauticVtigerCrmBundle\Vtiger\Ability\Update
 */
interface UpdateAbilityInterface
{
    /**
     * @param BaseModel $model
     *
     * @return BaseModel
     */
    public function update(BaseModel $model): BaseModel;
}
