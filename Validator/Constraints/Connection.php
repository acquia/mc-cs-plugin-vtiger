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

use Symfony\Component\Validator\Constraint;

class Connection extends Constraint
{
    /**
     * @var string
     */
    public $message = 'mautic.plugin.vtiger.connection.invalid';

    /**
     * @var string
     */
    protected $defaultValue = '';

    /**
     * @inheritdoc
     */
    public function validatedBy(): string
    {
        return \get_class($this).'Validator';
    }

    /**
     * @inheritdoc
     */
    public function getDefaultOption(): string
    {
        // @todo because of bug or bad usage of Constraint class it returns self::defaultValue property name and then value
        // @see Constraint::__construct() exceptions
        return 'defaultValue';
    }
}
