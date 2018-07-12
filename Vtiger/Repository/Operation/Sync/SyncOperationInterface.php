<?php
declare(strict_types=1);

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Operation\Sync;

use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Ability\Update\UpdateAbilityInterface;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\Ability\FindBy\FindByAbilityInterface;


/**
 * Interface SyncRepositoryInterface
 * @package MauticPlugin\MauticVtigerCrmBundle\Vtiger
 */
interface SyncOperationInterface extends UpdateAbilityInterface, FindByAbilityInterface
{
    /**
     * @param ObjectDAO $objectDAO
     */
    public function updateModelBySyncObject(ObjectDAO $objectDAO);
}
