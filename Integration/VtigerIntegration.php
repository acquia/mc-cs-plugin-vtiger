<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticSlooceTransportBundle\Integration;

use Ivory\OrderedForm\Builder\OrderedFormBuilder;
use Mautic\IntegrationBundle\Integration\BasicIntegration;
use Mautic\LeadBundle\Model\FieldModel;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class VtigerIntegration extends BasicIntegration
{
    /**
     * @var bool
     */
    protected $coreIntegration = false;

    /**
     * @var FieldModel
     */
    protected $fieldModel;

    /**
     * SlooceIntegration constructor.
     *
     * @param FieldModel $fieldModel
     */
    public function __construct(FieldModel $fieldModel)
    {
        $this->fieldModel = $fieldModel;
    }

    public function getIcon()
    {
        return 'app/bundles/SmsBundle/Assets/img/Vtiger.png';
    }
}
