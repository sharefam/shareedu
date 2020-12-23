<?php

namespace CorporateTrainingBundle\Biz\Crontab;

use Biz\Crontab\SystemCrontabInitializer as BaseCrontabInitializer;

class SystemCrontabInitializer extends BaseCrontabInitializer
{
    public static function init()
    {
        parent::init();
        self::registerCTDefaultJobs();
    }

    protected static function registerCTDefaultJobs()
    {
        $count = self::getSchedulerService()->countJobs(
            array('name' => 'CheckConvertStatusJob', 'source' => 'MAIN')
        );
        if ($count == 0) {
            self::getSchedulerService()->register(
                array(
                    'name' => 'CheckConvertStatusJob',
                    'source' => 'MAIN',
                    'expression' => '*/15 * * * *',
                    'class' => 'Biz\File\Job\VideoMediaStatusUpdateJob',
                    'args' => array(),
                    'misfire_policy' => 'missed',
                )
            );
        }
    }
}
