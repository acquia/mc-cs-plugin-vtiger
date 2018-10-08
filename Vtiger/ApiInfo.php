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

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger;

class ApiInfo
{
    /** @var Connection */
    private $connection;

    /**
     * ApiInfo constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return array
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     */
    public function getListTypes() {
        return $this->get('listtypes');
    }

    /**
     * @param $elementType
     *
     * @return mixed
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     */
    public function describe($elementType) {
        return $this->post('describe', ['elementType'=>$elementType]);
    }

    /**
     * @param $operation
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     */
    private function get($operation)
    {
        $response = $this->connection->get($operation);
        return $response;
    }

    /**
     * @param $operation
     * @param $payload
     *
     * @return mixed
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     */
    private function post($operation, $payload)
    {
        $response = $this->connection->post($operation, $payload);
        return $response;
    }
}
