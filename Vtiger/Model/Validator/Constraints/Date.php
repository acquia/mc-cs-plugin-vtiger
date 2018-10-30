<?php
declare(strict_types=1);

/*
 * @copyright   2018 Mautic Inc. All rights reserved
 * @author      Mautic, Inc. Jan Kozak <galvani78@gmail.com>
 *
 * @link        http://mautic.com
 * @created     30.10.18
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\MissingOptionsException;

class Date extends Constraint
{
    public $message = 'The value you selected is not a valid date.';

    /**
     * {@inheritdoc}
     */
    public function getDefaultOption()
    {
        return 'format';
    }

    /**
     * @var string
     */
    protected $format;

    /**
     * Date constructor.
     *
     * @param $options
     */
    public function __construct($options)
    {
        if($options['format'])
        {
            $this->format = $options['format'];
        }
        else
        {
            throw new MissingOptionsException("You must specify format for the validator", $options);
        }
        parent::__construct($options);
    }

    /**
     * @return string
     */
    public function getFormat(): string
    {
        return $this->format;
    }
}
