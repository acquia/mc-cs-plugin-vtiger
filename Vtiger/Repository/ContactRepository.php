<?php
/**
 * Created by PhpStorm.
 * User: jan
 * Date: 25.5.18
 * Time: 11:48
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository;

use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\BaseModel;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Contact;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\ModuleInterface;

class ContactRepository extends BaseRepository
{
    public function create($moduleData): ModuleInterface
    {
        $model = $moduleData instanceof BaseModel ? $moduleData : new Contact($moduleData);

        return parent::create($model);
    }

    public function retrieve(string $id): ModuleInterface
    {
        $record = $this->findOneBy(['id'=>$id]);

        var_dump($record); die();
    }

    public function update(ModuleInterface $module): ModuleInterface
    {
        // TODO: Implement update() method.
    }

    public function delete(ModuleInterface $module)
    {
        // TODO: Implement delete() method.
    }
}