<?php

declare(strict_types=1);

/*
 * @copyright   2018 Mautic Inc. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticVtigerCrmBundle\EventListener;

use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\LeadBundle\Entity\LeadEventLog;
use Mautic\LeadBundle\Event\LeadTimelineEvent;
use Mautic\LeadBundle\LeadEvents;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration;

final class MauticTimelineServiceSubscriber extends CommonSubscriber
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            LeadEvents::TIMELINE_ON_GENERATE => ['onGenerate', 0]
        ];
    }

    public function onGenerate(LeadTimelineEvent $event) {
        $eventLogRepository = $this->em->getRepository(LeadEventLog::class);

        $filters = [
            'lead' => $event->getLead(),
            'bundle'  => VtigerCrmIntegration::NAME,
        ];

        $events = $eventLogRepository->findBy($filters);

        if (!count($events)) {
            return;
        }


    }
}
