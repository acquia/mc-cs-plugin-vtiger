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

namespace MauticPlugin\MauticVtigerCrmBundle\Exceptions;

class AccessDeniedException extends SessionException
{
    /**
     * @param string $message
     * @param string $apiUrl
     * @param array  $payload
     */
    public function __construct(string $message, string $apiUrl, $payload = [])
    {
        $message = sprintf("call to %s failed. with message '%s'. Payload %s",
            $apiUrl,
            $message,
            count($payload) ? json_encode($payload) : 'none'
        );
        parent::__construct($message);
    }
}
