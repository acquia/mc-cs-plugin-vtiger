<?php
declare(strict_types=1);

/*
 * @copyright   2018 Mautic Inc. All rights reserved
 * @author      Mautic, Inc. Jan Kozak <galvani78@gmail.com>
 *
 * @link        http://mautic.com
 * @created     29.10.18
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Type;

/**
 * Class DateType
 * @package MauticPlugin\MauticVtigerCrmBundle\Vtiger\Type
 */
class DateType extends CommonType
{
    /** @var string */
    private $format;

    /**
     * DateType constructor.
     *
     * @param $data
     */
    public function __construct($data)
    {
        parent::__construct($data);

        $this->format = $data->format;
    }

    /**
     * @return string
     */
    public function getFormat()
    : string
    {
        return $this->format;
    }
}
