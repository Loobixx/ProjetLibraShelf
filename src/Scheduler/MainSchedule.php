<?php

namespace App\Scheduler;

use App\Scheduler\Message\CheckLateLoansMessage;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

#[AsSchedule('default')]
class MainSchedule implements ScheduleProviderInterface
{
    public function getSchedule(): Schedule
    {
        // CORRECTION ICI : On utilise (new Schedule()) au lieu de Schedule::with()
        return (new Schedule())
            ->add(
                RecurringMessage::cron('0 8 * * *', new CheckLateLoansMessage())
            )
            ;
    }
}
