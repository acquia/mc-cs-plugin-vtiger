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


class ApiInfo
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getListTypes() {
        return $this->get('listtypes');
    }

    public function describe($elementType) {
        return $this->post('describe', ['elementType'=>$elementType]);
    }

    private function get($operation)
    {
        $response = $this->connection->get($operation);
        return $response;
    }

    private function post($operation, $payload)
    {
        $response = $this->connection->post($operation, $payload);
        return $response;
    }

}