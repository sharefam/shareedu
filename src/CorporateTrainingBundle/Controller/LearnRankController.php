<?php

namespace CorporateTrainingBundle\Controller;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\Admin\BaseController;
use CorporateTrainingBundle\Biz\User\Service\UserOrgService;
use CorporateTrainingBundle\Biz\User\Service\UserService;
use CorporateTrainingBundle\Common\DateToolkit;
use Symfony\Component\HttpFoundation\Request;

class LearnRankController extends BaseController
{
    public function userLearnRankListAction(Request $request, $orgIds = '', $sourceFrom = '')
    {
        $fields['orgIds'] = $request->request->get('orgIds', $orgIds);
        $fields['type'] = $request->query->get('type', 'week');
        list($startDateTime, $endDateTime) = DateToolkit::generateStartDateAndEndDate($fields['type']);
        $fields['startDateTime'] = $startDateTime;
        $fields['endDateTime'] = $endDateTime;
        $sourceFrom = $request->query->get('sourceFrom') ? $request->query->get('sourceFrom') : $sourceFrom;

        if ($sourceFrom) {
            $conditions = array(
                'startDateTime' => strtotime($fields['startDateTime']),
                'endDateTime' => strtotime($fields['endDateTime']),
            );
        } else {
            $conditions = $this->prepareSearchConditions($fields);
        }

        $ranks = $this->getDataStatisticsService()->statisticsPersonOnlineLearnTimeRankingList($conditions);

        return $this->render('data-report/widgets/user-learn-rank-list.html.twig', array(
            'studyRanks' => array('personStudyRanks' => $ranks, 'sourceFrom' => $sourceFrom),
        ));
    }

    public function orgLearnRankListAction(Request $request, $sourceFrom = '')
    {
        $fields = $request->query->all();
        $fields['type'] = empty($fields['type']) ? 'week' : $fields['type'];
        list($startDateTime, $endDateTime) = DateToolkit::generateStartDateAndEndDate($fields['type']);
        $fields['startDateTime'] = $startDateTime;
        $fields['endDateTime'] = $endDateTime;
        $conditions = $this->prepareSearchConditions($fields);
        $sourceFrom = $request->query->get('sourceFrom') ? $request->query->get('sourceFrom') : $sourceFrom;

        $ranks = $this->getDataStatisticsService()->statisticsOrgLearnTimeRankingList($conditions);

        $orgUsers = $this->getUserService()->statisticsOrgUserNumGroupByOrgId();
        $orgUsers = ArrayToolkit::index($orgUsers, 'orgId');
        if ('admin' == $sourceFrom) {
            $ranks = $this->buildAdminOrgLearnRankListData($ranks, $orgUsers);
        }

        return $this->render('data-report/widgets/org-learn-rank-list.html.twig', array(
            'studyRanks' => array('orgStudyRanks' => $ranks, 'orgUsers' => $orgUsers, 'sourceFrom' => $sourceFrom),
        ));
    }

    protected function buildAdminOrgLearnRankListData($ranks, $orgUsers)
    {
        foreach ($ranks as &$rank) {
            if (!empty($orgUsers[$rank['orgId']]) && !empty($orgUsers[$rank['orgId']]['count'])) {
                $rank['totalLearnTime'] = $rank['totalLearnTime'] / $orgUsers[$rank['orgId']]['count'];
                $rank['totalLearnTime'] = round($rank['totalLearnTime'], 1);
            }
        }
        if (!empty($ranks)) {
            foreach ($ranks as $key => &$array) {
                if ($array['totalLearnTime'] > 0) {
                    $key_arrays[] = $array['totalLearnTime'];
                } else {
                    unset($ranks[$key]);
                }
            }

            if (!empty($key_arrays)) {
                array_multisort($key_arrays, SORT_DESC, SORT_NUMERIC, $ranks);
            }
        }

        return $ranks;
    }

    protected function prepareOrgIds($conditions)
    {
        if (!isset($conditions['orgIds'])) {
            return $this->getCurrentUser()->getManageOrgIdsRecursively();
        }

        return explode(',', $conditions['orgIds']);
    }

    protected function prepareSearchConditions($conditions)
    {
        if (!empty($conditions['startDateTime'])) {
            $conditions['startDateTime'] = strtotime($conditions['startDateTime']);
        }

        if (!empty($conditions['endDateTime'])) {
            $conditions['endDateTime'] = strtotime($conditions['endDateTime']);
        }

        if (isset($conditions['orgIds']) && empty($conditions['orgIds'])) {
            $userIds = -1;
        } else {
            $orgIds = $this->prepareOrgIds($conditions);
            $userIds = $this->findUserIdsByOrgIds($orgIds);
            $userIds = empty($userIds) ? -1 : $userIds;
        }

        $keywordUserIds = array();
        if (isset($conditions['keywordType']) && !empty($conditions['keyword'])) {
            $users = $this->getUserService()->searchUsers(
                array(
                    $conditions['keywordType'] => $conditions['keyword'],
                ),
                array('id' => 'DESC'),
                0,
                PHP_INT_MAX
            );

            $keywordUserIds = empty($users) ? array(-1) : ArrayToolkit::column($users, 'id');
            unset($conditions['keywordType']);
            unset($conditions['keyword']);
        }

        if (-1 == $keywordUserIds || -1 == $userIds) {
            $conditions['userIds'] = array(-1);
        } elseif (empty($keywordUserIds)) {
            $conditions['userIds'] = $userIds;
        } else {
            $conditions['userIds'] = array_intersect($userIds, $keywordUserIds);
        }

        unset($conditions['orgIds']);

        return $conditions;
    }

    protected function findUserIdsByOrgIds($orgIds)
    {
        $usersOrg = $this->getUserOrgService()->findUserOrgsByOrgIds($orgIds);

        return ArrayToolkit::column($usersOrg, 'userId');
    }

    protected function getDataStatisticsService()
    {
        return $this->createService('CorporateTrainingBundle:DataStatistics:DataStatisticsService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('CorporateTrainingBundle:User:UserService');
    }

    /**
     * @return UserOrgService
     */
    protected function getUserOrgService()
    {
        return $this->createService('CorporateTrainingBundle:User:UserOrgService');
    }
}
