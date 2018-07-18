<?php
/**
 * Created by PhpStorm.
 * User: jan
 * Date: 28.5.18
 * Time: 18:30
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model;

use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\BaseRepository;

/**
 * Class SyncReport
 * @package MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model
 */
class SyncReport
{
    private $moduleName;

    /**
     * @var array
     */
    private $updated;

    /**
     * @var array
     */
    private $deleted;

    /**
     * SyncReport constructor.
     *
     * @param \stdClass $data
     * @param string    $moduleName
     */
    public function __construct(\stdClass $data, string $moduleName) {
        $moduleClass = BaseRepository::$moduleClassMapping[$moduleName];

        foreach ($data->updated as $moduleData) {
            $this->updated[] = new $moduleClass((array)$moduleData);
        }

        foreach ($data->deleted as $moduleData) {
            $this->deleted[] = new $moduleClass((array)$moduleData);
        }
    }

    /**
     * @return mixed
     */
    public function getModuleName()
    {
        return $this->moduleName;
    }

    /**
     * @param mixed $moduleName
     *
     * @return SyncReport
     */
    public function setModuleName($moduleName)
    {
        $this->moduleName = $moduleName;

        return $this;
    }

    /**
     * @return array
     */
    public function getUpdated(): array
    {
        return $this->updated;
    }

    /**
     * @return array
     */
    public function getDeleted(): array
    {
        return $this->deleted;
    }
}