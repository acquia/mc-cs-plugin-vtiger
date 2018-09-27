<?php
declare(strict_types=1);

/*
 * @copyright   2018 Mautic Inc. All rights reserved
 * @author      Mautic, Inc. Jan Kozak <galvani78@gmail.com>
 *
 * @link        http://mautic.com
 * @created     7.9.18
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
namespace MauticPlugin\MauticVtigerCrmBundle\Service;

use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Model\LeadModel;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerSettingProvider;
use MauticPlugin\MauticVtigerCrmBundle\Service\Transformer\EventTransformer;

class LeadEventSupplier
{
    /**
     * @var LeadModel
     */
    private $leadModel;
    /**
     * @var VtigerSettingProvider
     */
    private $settingProvider;

    /** @var EventTransformer  */
    private $eventTransformer;
    /**
     * LeadEventSupplier constructor.
     *
     * @param LeadModel $leadModel
     */
    public function __construct(LeadModel $leadModel, VtigerSettingProvider $settingProvider) {
        $this->leadModel = $leadModel;
        $this->settingProvider = $settingProvider;
        $this->eventTransformer = new EventTransformer();
    }

    public function getByLeadId(Lead $lead) {
        $eventList = $this->leadModel->getEngagements($lead, []);

        return $eventList;
    }

    public function getTypes() {
        $types = $this->leadModel->getEngagementTypes();
        $types = array_flip($types);
        return array_flip($types);
    }
}