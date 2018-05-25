<?php

namespace MauticPlugin\MauticVtigerCrmBundle\Exceptions;

use Throwable;

class VtigerPluginException extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = "Vtiger: " . (string)$message;
        parent::__construct($message, $code, $previous);
    }
}