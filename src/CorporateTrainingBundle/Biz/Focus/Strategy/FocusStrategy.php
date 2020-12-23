<?php

namespace CorporateTrainingBundle\Biz\Focus\Strategy;

interface FocusStrategy
{
    public function findFocusAgo($type = 'my', $time);

    public function findFocusNow($type = 'my', $time);

    public function findFocusLater($type = 'my', $time);

    public function findFocusByStartTimeAndEndTime($type = 'my', $startTime, $endTime);
}
