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
use MauticPlugin\MauticVtigerCrmBundle\Model\Credentials;

class Connection
{
    /** @var bool */
    private $authenticateOnDemand = true;

    /** @var Credentials */
    private $credentials;

    /**
     * @return bool
     */
    public function isAuthenticateOnDemand(): bool
    {
        return $this->authenticateOnDemand;
    }

    /**
     * @param bool $authenticateOnDemand
     *
     * @return Connector
     */
    public function setAuthenticateOnDemand(bool $authenticateOnDemand): Connector
    {
        $this->authenticateOnDemand = $authenticateOnDemand;
        return $this;
    }


    public function authenticate(Credentials $credentials = null): Connector
    {
        $credentials = $credentials ?: $this->credentials;

        if (is_null($credentials)) {
            throw new VtigerSessionException('No authentication credentials supplied');
        }


    }
}