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


class Connector
{
    /** @var Connection */
    private $connection;

    /** @var RepositoryManager */
    private $repositoryManager;

    public function __construct(Connection $connection, RepositoryManager $repositoryManager)
    {
        $this->connection = $connection;
        $this->repositoryManager = $repositoryManager;
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
     * @return Connector
     */
    public function setRepositoryManager(RepositoryManager $repositoryManager): Connector
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
}