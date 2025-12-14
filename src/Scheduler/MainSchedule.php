<?php

namespace App\Scheduler;

use App\Scheduler\Message\PurgeOldDataMessage;
use App\Scheduler\Message\SendRemindersMessage;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

#[AsSchedule('default')]
class MainSchedule implements ScheduleProviderInterface
{
    public function getSchedule(): Schedule
    {
        return (new Schedule())->add(

            RecurringMessage::cron('0 8 * * *', new SendRemindersMessage()),

            RecurringMessage::cron('0 2 * * *', new PurgeOldDataMessage())
        );
    }
}
