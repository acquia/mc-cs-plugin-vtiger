<?php
/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic inc.
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author      Jan Kozak <galvani78@gmail.com>
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Model;

/**
 * Class Credentials represents credentials needed for authentication
 *
 * @package MauticPlugin\MauticVtigerCrmBundle\Model
 */
class Credentials
{
    /** @var string */
    private $username;

    /** @var string */
    private $accesskey;

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return Credentials
     */
    public function setUsername(string $username): Credentials
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return string
     */
    public function getAccesskey(): string
    {
        return $this->accesskey;
    }

    /**
     * @param string $accesskey
     * @return Credentials
     */
    public function setAccesskey(string $accesskey): Credentials
    {
        $this->accesskey = $accesskey;
        return $this;
    }
}