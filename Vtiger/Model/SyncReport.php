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
