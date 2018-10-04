<?php

declare(strict_types=1);

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger;

use Psr\Http\Message\ResponseInterface;

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
    /**
     * @var Connection
     */
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
     *
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     */
    public function getListTypes(): array
    {
        return $this->get('listtypes');
    }

    /**
     * @param $elementType
     *
     * @return mixed
     *
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     */
    public function describe($elementType)
    {
        return $this->post('describe', ['elementType'=>$elementType]);
    }

    /**
     * @param $operation
     *
     * @return ResponseInterface
     *
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     */
    private function get($operation): ResponseInterface
    {
        return $this->connection->get($operation);
    }

    /**
     * @param $operation
     * @param $payload
     *
     * @return mixed
     *
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     */
    private function post($operation, $payload)
    {
        return $this->connection->post($operation, $payload);
    }
}
