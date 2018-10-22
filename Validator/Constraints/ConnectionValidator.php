<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Validator\Constraints;

use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Connection;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ConnectionValidator extends ConstraintValidator
{
    /**
     * @var Connection
     */

    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Check if connection credentials are valid
     *
     * @param mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        $formData = $this->context->getRoot()->getData()->getApiKeys();

        $url = $formData['url'];
        $username = $formData['username'];
        $accessKey = $formData['accessKey'];

        $this->connection = clone($this->connection);
        $connected = $this->connection->test($url, $username, $accessKey);

        if (!$connected) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}