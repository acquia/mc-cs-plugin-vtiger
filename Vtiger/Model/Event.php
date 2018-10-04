<?php

declare(strict_types=1);

/*
 * @copyright   2018 Mautic Inc. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://www.mautic.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model;

use DateTimeImmutable;
use DateTimeInterface;

class Event extends BaseModel
{
    /**
     * @return null|string
     */
    public function getSubject(): ?string
    {
        return isset($this->data['subject']) ? $this->data['subject'] : null;
    }

    /**
     * @return null|string
     */
    public function getTaskPriority(): ?int
    {
        return isset($this->data['taskpriority']) ? (int) $this->data['taskpriority'] : null;
    }

    /**
     * @return \DateTimeImmutable
     *
     * @throws \Exception
     */
    public function getDateTimeStart(): DateTimeImmutable
    {
        return new DateTimeImmutable($this->data['date_start'].' '.$this->data['time_start']);
    }

    /**
     * @return \DateTimeImmutable
     *
     * @throws \Exception
     */
    public function getDateTimeEnd(): DateTimeImmutable
    {
        return new DateTimeImmutable($this->data['due_date'].' '.$this->data['time_end']);
    }

    public function setDateTimeStart(DateTimeInterface $dateTime): self
    {
        $day  = $dateTime->format('Y-m-d');
        $time = $dateTime->format('H:i:s');
        $this->set('date_start', $day);
        $this->set('time_start', $time);

        return $this;
    }

    public function setDateTimeEnd(DateTimeInterface $dateTime): self
    {
        $day  = $dateTime->format('Y-m-d');
        $time = $dateTime->format('H:i:s');
        $this->set('due_date', $day);
        $this->set('time_end', $time);

        return $this;
    }

    public function setSubject(?string $subject): self
    {
        $this->set('subject', $subject);

        return $this;
    }

    public function setAssignedUserId(?string $userId): self
    {
        $this->set('assigned_user_id', $userId);

        return $this;
    }

    public function setModifiedBy(?string $userId): self
    {
        $this->set('modifiedby', $userId);

        return $this;
    }

    public function setTaskPriority(?string $priority): self
    {
        $this->set('taskpriority', $priority);

        return $this;
    }

    public function getContactId(): ?string
    {
        return $this->data['contact_id'] ?? null;
    }
}
