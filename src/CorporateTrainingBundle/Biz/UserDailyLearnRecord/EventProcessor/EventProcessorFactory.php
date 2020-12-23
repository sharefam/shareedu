<?php

namespace CorporateTrainingBundle\Biz\UserDailyLearnRecord\EventProcessor;

use AppBundle\Common\Exception\InvalidArgumentException;
use Topxia\Service\Common\ServiceKernel;

class EventProcessorFactory
{
    private static $map = array(
        'course.join' => 'CorporateTrainingBundle\\Biz\\UserDailyLearnRecord\\EventProcessor\\CourseJoinProcessor',
        'course.view' => 'CorporateTrainingBundle\\Biz\\UserDailyLearnRecord\\EventProcessor\\CourseViewProcessor',
        'task.view' => 'CorporateTrainingBundle\\Biz\\UserDailyLearnRecord\\EventProcessor\\TaskViewProcessor',
        'task.finish' => 'CorporateTrainingBundle\\Biz\\UserDailyLearnRecord\\EventProcessor\\TaskFinishProcessor',
        'wave.learn.time' => 'CorporateTrainingBundle\\Biz\\UserDailyLearnRecord\\EventProcessor\\WaveLearnTimeProcessor',
    );

    public static function create($method)
    {
        if (!array_key_exists($method, self::$map)) {
            throw new InvalidArgumentException(sprintf('Unknown method type: %s', $method));
        }

        $processor = self::$map[$method];

        return new $processor(ServiceKernel::instance()->getBiz());
    }
}
