<?php
declare(strict_types=1);

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\Ability\FindBy;

use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\BaseModel;

/**
 * Interface FindByAbilityInterface
 * @package MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\Ability\FindBy
 */
interface FindByAbilityInterface
{
    /**
     * @param array  $where
     * @param string $columns
     *
     * @return BaseModel[]
     */
    public function findBy(array $where = [], string $columns = '*'): array;
}
