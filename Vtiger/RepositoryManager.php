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

use MauticPlugin\MauticVtigerCrmBundle\Exceptions\VtigerSessionException;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\ContactRepository;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\RepositoryInterface;

class RepositoryManager
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return Connection
     */
    public function getConnection(): Connection
    {
        return $this->connection;
    }

    /**
     * @param Connection $connection
     * @return RepositoryManager
     */
    public function setConnection(Connection $connection): RepositoryManager
    {
        $this->connection = $connection;
        return $this;
    }

    /**
     * @param string $moduleName
     * @return RepositoryInterface
     * @throws VtigerSessionException
     */
    public function getRepository(string $moduleName): RepositoryInterface
    {
        if (is_null($this->getConnection())) {
            throw new VtigerSessionException('Repository is missing connection ');
        }
        $repository = new ContactRepository($this->getConnection(), 'Contacts');
        return $repository;
    }
}