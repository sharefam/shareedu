<?php

namespace CorporateTrainingBundle\Biz\Focus\Service;

interface FocusService
{
    public function findFocusAgo($type = 'my', $time);

    public function findFocusNow($type = 'my', $time);

    public function findFocusLater($type = 'my', $time);

    public function findFocusByStartTimeAndEndTime($type = 'my', $startTime, $endTime);
}
