<?php

namespace CorporateTrainingBundle\Extensions\DataTag;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Extensions\DataTag\DataTag;
use AppBundle\Extensions\DataTag\BaseDataTag;
use CorporateTrainingBundle\Common\DateToolkit;

class StudyRankDataTag extends BaseDataTag implements DataTag
{
    public function getData(array $arguments)
    {
        $type = empty($arguments['type']) ? 'week' : $arguments['type'];
        list($startDateTime, $endDateTime) = DateToolkit::generateStartDateAndEndDate($type);

        $conditions = array(
            'startDateTime' => strtotime($startDateTime),
            'endDateTime' => strtotime($endDateTime),
        );

        $orgUsers = $this->getUserService()->statisticsOrgUserNumGroupByOrgId();
        $orgUsers = ArrayToolkit::index($orgUsers, 'orgId');

        return array(
            'personStudyRanks' => $this->getDataStatisticsService()->statisticsPersonOnlineLearnTimeRankingList($conditions, 0, 5),
            'orgStudyRanks' => $this->getDataStatisticsService()->statisticsOrgLearnTimeRankingList($conditions, 0, 5),
            'orgUsers' => $orgUsers,
        );
    }

    protected function getDataStatisticsService()
    {
        return $this->getServiceKernel()->getBiz()->service('CorporateTrainingBundle:DataStatistics:DataStatisticsService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->getBiz()->service('CorporateTrainingBundle:User:UserService');
    }
}
