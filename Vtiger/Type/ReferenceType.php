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
 * Class ReferenceType
 * @package MauticPlugin\MauticVtigerCrmBundle\Vtiger\Type
 */
class ReferenceType extends CommonType
{
    /**
     * @var string
     */
    private $refersTo;

    public function __construct($data)
    {
        parent::__construct($data);

        $this->refersTo = $data->refersTo;
    }

    /**
     * @return string
     */
    public function getRefersTo()
    : string
    {
        return $this->refersTo;
    }
}
