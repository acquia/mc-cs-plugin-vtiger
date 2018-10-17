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

namespace MauticPlugin\MauticVtigerCrmBundle\Model;

/**
 * Class Credentials represents credentials needed for authentication.
 */
class Credentials
{
    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $accesskey;

    public function __construct(string $accesskey, string $username)
    {
        $this->accesskey = $accesskey;
        $this->username  = $username;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getAccesskey(): string
    {
        return $this->accesskey;
    }
}
