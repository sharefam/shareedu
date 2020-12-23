<?php

namespace CorporateTrainingBundle\Biz\Exporter;

use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Biz\OfflineActivity\Service\MemberService;
use CorporateTrainingBundle\Biz\OfflineActivity\Service\OfflineActivityService;

abstract class BaseOfflineActivityExporter extends AbstractExporter
{
    public function canExport($parameters)
    {
        return $this->getActivityService()->hasActivityManageRole();
    }

    protected function getSortedHeadingRow($parameters)
    {
        return array(
            array('code' => 'truename', 'title' => $this->trans('student.profile.truename')),
            array('code' => 'nickname', 'title' => $this->trans('student.user_name')),
            array('code' => 'org', 'title' => $this->trans('student.profile.department')),
            array('code' => 'post', 'title' => $this->trans('student.profile.post')),
            array('code' => 'attendStatus', 'title' => $this->trans('offline_activity.attend_status')),
            array('code' => 'score', 'title' => $this->trans('offline_activity.examination.score')),
            array('code' => 'evaluate', 'title' => $this->trans('offline_activity.evaluate.set')),
            array('code' => 'passStatus', 'title' => $this->trans('offline_activity.examination.result')),
        );
    }

    protected function prepareSearchConditions($parameters)
    {
        if (isset($parameters['keywordType']) && !empty($parameters['keyword'])) {
            $users = $this->getUserService()->searchUsers(
                array($parameters['keywordType'] => $parameters['keyword']),
                array('id' => 'ASC'),
                0,
                PHP_INT_MAX
            );

            $userIds = ArrayToolkit::column($users, 'id');
            $parameters['userIds'] = empty($userIds) ? array(-1) : $userIds;

            unset($parameters['keywordType']);
            unset($parameters['keyword']);
        }

        return $parameters;
    }

    /**
     * @return OfflineActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineActivity:OfflineActivityService');
    }

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineActivity:MemberService');
    }
}
