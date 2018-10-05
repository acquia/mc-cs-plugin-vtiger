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

use Doctrine\ORM\EntityManager;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\LeadBundle\Model\LeadModel;
use MauticPlugin\MauticVtigerCrmBundle\Integration\Provider\VtigerSettingProvider;
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

    /** @var EventTransformer */
    private $eventTransformer;
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * LeadEventSupplier constructor.
     *
     * @param LeadModel $leadModel
     */
    public function __construct(LeadModel $leadModel, VtigerSettingProvider $settingProvider, EntityManager $em)
    {
        $this->leadModel       = $leadModel;
        $this->settingProvider = $settingProvider;
        $this->em              = $em;
    }

    public function getMappedLeadIds()
    {
        $connection = $this->em->getConnection();

        $statement = $connection->prepare("select group_concat(l.id) as ids from " . MAUTIC_TABLE_PREFIX . "sync_object_mapping map
          inner join " . MAUTIC_TABLE_PREFIX . "leads l on map.internal_object_id = l.id 
          where map.integration = 'VtigerCrm' and map.internal_object_name = 'lead' and map.is_deleted = 0");

        $statement->execute();
        $results = $statement->fetch();

        if (!isset($results['ids'])) {
            return [];
        }

        return explode(',', $results['ids']);
    }

    public function getLeadsMapping() {
        $connection = $this->em->getConnection();

        $statement = $connection->prepare("select map.internal_object_id, map.integration_object_id from " . MAUTIC_TABLE_PREFIX . "sync_object_mapping map
          inner join " . MAUTIC_TABLE_PREFIX . "leads l on map.internal_object_id = l.id 
          where map.integration = 'VtigerCrm' and map.internal_object_name = 'lead' and map.is_deleted = 0");

        $statement->execute();

        $results = [];
        while ($record = $statement->fetch()) {
            $results[$record['internal_object_id']] = $record['integration_object_id'];
        }

        return $results;
    }

    /**
     * @param \DateTime|null $startDate
     * @param \DateTime|null $endDate
     * @param                $leadId
     *
     * @return array
     */
    public function getLeadEvents($leadIds, $eventsRequested = [], \DateTime $startDate = null, \DateTime $endDate = null)
    {
        $filters = [
            'search'        => '',
            'includeEvents' => $eventsRequested,
            'excludeEvents' => [],
        ];

        if ($startDate) {
            $filters['dateFrom'] = $startDate;
            $filters['dateTo']   = $endDate;
        }

        foreach ($leadIds as $leadId) {
            $activity = [];
            $lead     = $this->leadModel->getEntity($leadId);
            $page     = 1;
            while (true) {
                $engagements = $this->leadModel->getEngagements($lead, $filters, null, $page, 100, true);

                $events      = $engagements['events'];
                if (empty($events)) {
                    break;
                }

                // inject lead into events
                foreach ($events as $event) {
                    if (
                        (isset($filters['dateFrom']) && ($filters['dateFrom'] > $event['timestamp'])) ||
                        (isset($filters['dateTo']) && ($event['timestamp'] > $filters['dateTo'])) ||
                        (isset($filters['includeEvents']) && count($filters['includeEvents']) && !in_array($event['event'], $filters['includeEvents'])) ||
                        (isset($filters['excludeEvents']) && count($filters['excludeEvents']) && in_array($event['event'], $filters['excludeEvents']))
                    ) {
                        continue;
                    }
                    $checkEvent = [
                        'timestamp' => $event['timestamp']->getTimestamp(),
                        'leadId'    => $lead->getId(),
                        'event'     => $event['event'],
                        'priority'  => $event['eventPriority'],
                    ];

                    $vtigerCheck[$leadId][$event['timestamp']->getTimestamp()][] = $checkEvent;
                }
                ++$page;
                // Lots of entities will be loaded into memory while compiling these events so let's prevent memory overload by clearing the EM
                $this->em->clear();
            }
        }

        return $vtigerCheck;
    }

    public function getTypes()
    {
        $types = $this->leadModel->getEngagementTypes();
        $types = array_flip($types);

        return array_flip($types);
    }
}