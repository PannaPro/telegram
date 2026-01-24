<?php

namespace App\Scheduler;

use App\Message\ManageEventStatusMessage;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

#[AsSchedule('event_status')]
class EventStatusScheduleProvider implements ScheduleProviderInterface
{
    public function getSchedule(): Schedule
    {
        return (new Schedule())
            ->add(
                RecurringMessage::every(
                    '10 minutes',
                    new ManageEventStatusMessage(new \DateTimeImmutable())
                )
            );
    }
}
