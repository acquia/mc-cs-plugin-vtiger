<?php

declare(strict_types=1);

namespace MauticPlugin\MauticVtigerCrmBundle\Exceptions;

use Exception;
use Throwable;

class VtigerPluginException extends Exception
{
    public function __construct($message = '', $code = 0, ?Throwable $throwable = null)
    {
        $message = 'Vtiger: '.(string) $message;
        parent::__construct($message, $code, $throwable);
    }
}
