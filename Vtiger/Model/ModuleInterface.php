<?php
/**
 * Created by PhpStorm.
 * User: jan
 * Date: 25.5.18
 * Time: 11:39
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model;


interface ModuleInterface
{
    /**
     * @return mixed
     */
    public function getId();

    /**
     * @return string
     */
    public function getModuleName(): string;
}