<?php

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger;

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author      Jan Kozak <galvani78@gmail.com>
 */


use Codeception\Template\Api;

class Client
{
    /** @var Connection */
    private $connection;

    /** @var RepositoryManager */
    private $repositoryManager;

    private $repositories = [];

    /**
     * @var ApiInfo
     */
    private $infoService;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->repositoryManager = new RepositoryManager();
        $this->repositoryManager->setConnection($connection);
    }

    /**
     * @return RepositoryManager
     */
    public function getRepositoryManager(): RepositoryManager
    {
        return $this->repositoryManager;
    }

    /**
     * @param RepositoryManager $repositoryManager
     * @return Client
     */
    public function setRepositoryManager(RepositoryManager $repositoryManager): Client
    {
        $this->repositoryManager = $repositoryManager;

        if (is_null($repositoryManager->getConnection()) && !is_null($connection = $this->getConnection())) {
            $this->repositoryManager->setConnection($connection);
        }

        return $this;
    }

    /**
     * @return Connection
     */
    public function getConnection(): Connection
    {
        return $this->connection;
    }

    /**
     * @return ApiInfo
     */
    public function getInfoService(): ApiInfo {
        if (is_null($this->infoService)) {
            $this->infoService = new ApiInfo($this->getConnection());
        }

        return $this->infoService;
    }
}