<?php

namespace CorporateTrainingBundle\Controller\OfflineExam;

use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;

class OfflineExamManageController extends BaseController
{
    public function offlineExamManageListAction(Request $request, $id)
    {
        $projectPlan = $this->getProjectPlanService()->getProjectPlan($id);

        $items = $this->getProjectPlanService()->findProjectPlanItemsByProjectPlanIdAndTargetType($id, 'offline_exam');
        $itemIds = empty($items) ? array(-1) : ArrayToolkit::column($items, 'targetId');
        $conditions = $request->query->all();
        if (!empty($conditions['startTime'])) {
            $conditions['startTime_GE'] = strtotime($conditions['startTime']);
        }

        if (!empty($conditions['endTime'])) {
            $conditions['endTime_LE'] = strtotime($conditions['endTime'].'23:59:59');
        }

        $paginator = new Paginator(
            $request,
            count($items),
            15
        );
        $conditions['status'] = 'published';
        $conditions['ids'] = $itemIds;
        $exams = $this->getOfflineExamService()->searchOfflineExams(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
            );
        foreach ($exams as &$exam) {
            $exam['passNum'] = $this->getOfflineExamMemberService()->countMembers(array('status' => 'passed', 'offlineExamId' => $exam['id']));
        }

        $projectPlanMemberCount = $this->getProjectPlanMemberService()->countProjectPlanMembers(array('projectPlanId' => $id));

        return $this->render(
            'project-plan/exam-manage/offline-exam/exam-list.html.twig',
            array(
                'exams' => $exams,
                'paginator' => $paginator,
                'projectPlan' => $projectPlan,
                'projectPlanMemberCount' => $projectPlanMemberCount,
                'type' => 'offlineExam',
            )
        );
    }

    public function offlineExamManageMemberListAction(Request $request, $id, $taskId)
    {
        list($projectPlan, $paginator, $members, $memberCount, $conditions) = $this->buildOfflineExamManageMemberListData($request, $id, $taskId);

        return $this->render(
            'project-plan/exam-manage/offline-exam/member.html.twig',
            array(
                'offlineExamId' => $taskId,
                'projectPlan' => $projectPlan,
                'paginator' => $paginator,
                'members' => $members,
                'projectPlanMemberCount' => $memberCount,
                'type' => 'offlineExam',
                'orgIds' => implode(',', $conditions['orgIds']),
            )
        );
    }

    public function offlineExamAjaxManageMemberListAction(Request $request, $id, $taskId)
    {
        list($projectPlan, $paginator, $members, $memberCount, $conditions) = $this->buildOfflineExamManageMemberListData($request, $id, $taskId, 'ajax');

        return $this->render(
            'project-plan/exam-manage/offline-exam/member-list.html.twig',
            array(
                'offlineExamId' => $taskId,
                'projectPlan' => $projectPlan,
                'paginator' => $paginator,
                'members' => $members,
                'projectPlanMemberCount' => $memberCount,
                'type' => 'offlineExam',
                'orgIds' => implode(',', $conditions['orgIds']),
            )
        );
    }

    public function offlineExamMarkAction(Request $request, $offlineExamId, $userId)
    {
        $offlineExamResult = $this->getOfflineExamMemberService()->getMemberByOfflineExamIdAndUserId($offlineExamId, $userId);
        $offlineExam = $this->getOfflineExamService()->getOfflineExam($offlineExamId);

        if ($request->isMethod('POST')) {
            $result = $request->request->all();
            $score = empty($result['score']) ? 0 : $result['score'];
            if ($offlineExam['passScore'] <= $score) {
                $result = $this->getOfflineExamMemberService()->markPass($offlineExamId, $userId, $score);
            } else {
                $result = $this->getOfflineExamMemberService()->markUnPass($offlineExamId, $userId, $score);
            }

            return $this->createJsonResponse($result);
        }

        return $this->render(
            'project-plan/exam-manage/offline-exam/review-modal.html.twig',
            array(
                'offlineExam' => $offlineExam,
                'offlineExamResult' => $offlineExamResult,
                'userId' => $userId,
            )
        );
    }

    public function offlineExamDeleteAction(Request $request, $id, $offlineExamId)
    {
        $offlineExam = $this->getOfflineExamService()->getOfflineExam($offlineExamId);

        if (empty($offlineExam)) {
            return $this->createNotFoundException('线下考试不存在');
        }

        $item = $this->getProjectPlanService()->getProjectPlanItemByProjectPlanIdAndTargetIdAndTargetType($id, $offlineExamId, 'offline_exam');

        return $this->redirect($this->generateUrl('project_plan_item_delete', array('id' => $item['id'])));
    }

    protected function prepareConditions($conditions)
    {
        if (isset($conditions['status']) && 'all' == $conditions['status'] || !isset($conditions['status'])) {
            unset($conditions['status']);
        } else {
            $offlineExamResult = $this->getOfflineExamMemberService()->searchMembers($conditions, array(), 0, PHP_INT_MAX);
            $conditions['userIds'] = !empty($offlineExamResult) ? ArrayToolkit::column($offlineExamResult, 'userId') : array(-1);

            unset($conditions['status']);
        }

        if (isset($conditions['username']) && !empty($conditions['username'])) {
            $userIds = $this->getUserService()->findUserIdsByNickNameOrTrueName($conditions['username']);
            $userIds = !empty($conditions['userIds']) ? array_intersect($userIds, $conditions['userIds']) : $userIds;
            $conditions['userIds'] = !empty($userIds) ? $userIds : array(-1);

            unset($conditions['username']);
        }

        if (isset($conditions['orgIds']) && empty($conditions['orgIds'])) {
            $conditions['userIds'] = ($conditions['userIds'] = array(-1) || empty($conditions['userIds'])) ? array(-1) : $conditions['userIds'];
            $conditions['orgIds'] = $this->prepareOrgIds($conditions);
        } else {
            $conditions['orgIds'] = $this->prepareOrgIds($conditions);
            $userIds = $this->findUserIdsByOrgIds($conditions['orgIds']);
            $userIds = !empty($conditions['userIds']) ? array_intersect($userIds, $conditions['userIds']) : $userIds;
            $conditions['userIds'] = !empty($userIds) ? $userIds : array(-1);
        }

        return $conditions;
    }

    protected function buildOfflineExamManageMemberListData($request, $id, $taskId, $type = '')
    {
        $projectPlan = $this->getProjectPlanService()->getProjectPlan($id);

        $conditions = $request->request->all();
        $conditions = $this->prepareConditions($conditions);
        $conditions['projectPlanId'] = $id;

        $memberCount = $this->getProjectPlanMemberService()->countProjectPlanMembers($conditions);

        $paginator = new Paginator(
            $request,
            $memberCount,
            20
        );
        if ('ajax' != $type) {
            $paginator->setBaseUrl($this->generateUrl('project_plan_offline_exam_ajax_manage_member_list', array('id' => $id, 'taskId' => $taskId)));
        }

        $members = $this->getProjectPlanMemberService()->searchProjectPlanMembers(
            $conditions,
            array('id' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        foreach ($members as $key => &$member) {
            $member['examResult'] = $this->getOfflineExamMemberService()->getMemberByOfflineExamIdAndUserId($taskId, $member['userId']);
            $member['user'] = array(
                'truename' => $this->getUserService()->getUserProfile($member['userId'])['truename'],
                'userInfo' => $this->getUserService()->getUser($member['userId']),
            );
        }

        return array($projectPlan, $paginator, $members, $memberCount, $conditions);
    }

    protected function findUserIdsByOrgIds($orgIds)
    {
        $usersOrg = $this->getUserOrgService()->findUserOrgsByOrgIds($orgIds);

        return ArrayToolkit::column($usersOrg, 'userId');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\ProjectPlanServiceImpl
     */
    protected function getProjectPlanService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }

    protected function getProjectPlanMemberService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:MemberService');
    }

    protected function getCourseTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineExam\Service\Impl\OfflineExamServiceImpl
     */
    protected function getOfflineExamService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineExam:OfflineExamService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineExam\Service\Impl\MemberServiceImpl
     */
    protected function getOfflineExamMemberService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineExam:MemberService');
    }

    protected function getOrgService()
    {
        return $this->createService('Org:OrgService');
    }

    protected function getUserOrgService()
    {
        return $this->createService('CorporateTrainingBundle:User:UserOrgService');
    }
}
