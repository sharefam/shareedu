<?php

namespace CorporateTrainingBundle\Controller\ProjectPlan;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\BaseController;
use AppBundle\Common\Exception\AccessDeniedException;
use Biz\Org\Service\OrgService;
use CorporateTrainingBundle\Biz\Post\Service\PostService;
use CorporateTrainingBundle\Biz\ProjectPlan\Service\MemberService;
use CorporateTrainingBundle\Biz\ProjectPlan\Service\ProjectPlanService;
use CorporateTrainingBundle\Biz\User\Service\UserOrgService;
use CorporateTrainingBundle\Biz\UserAttribute\Service\UserAttributeService;
use Symfony\Component\HttpFoundation\Request;

class MemberManageController extends BaseController
{
    public function memberManageListAction(Request $request, $id)
    {
        if (!$this->getProjectPlanService()->canManageProjectPlan($id)) {
            return $this->createMessageResponse('error', 'project_plan.message.can_not_manage_message');
        }

        $projectPlan = $this->getProjectPlanService()->getProjectPlan($id);
        if (empty($projectPlan)) {
            throw $this->createNotFoundException('Project Plan is not exist');
        }

        $conditions = $request->query->all();
        $conditions['projectPlanId'] = $projectPlan['id'];

        $conditions = $this->prepareMemberListSearchConditions($conditions);

        $count = $this->getProjectPlanMemberService()->countProjectPlanMembers(
            $conditions
        );

        $paginator = new Paginator(
            $request,
            $count,
            20
        );

        $members = $this->getProjectPlanMemberService()->searchProjectPlanMembers(
            $conditions,
            array('id' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $members = $this->getProjectPlanService()->appendProgress($projectPlan['id'], $members);

        $userIds = ArrayToolkit::column($members, 'userId');
        $userProfiles = $this->getUserService()->findUserProfilesByIds($userIds);
        $userProfiles = ArrayToolkit::index($userProfiles, 'id');
        $users = $this->getUserService()->findUsersByIds($userIds);
        $users = ArrayToolkit::index($users, 'id');

        $posts = $this->getPostService()->findPostsByIds(ArrayToolkit::column($users, 'postId'));
        $posts = ArrayToolkit::index($posts, 'id');

        return $this->render(
            '@App/project-plan/member/member-manage-list.html.twig',
            array(
                'members' => $members,
                'users' => $users,
                'userProfiles' => $userProfiles,
                'posts' => $posts,
                'paginator' => $paginator,
                'projectPlan' => $projectPlan,
            )
        );
    }

    public function memberMatchAction(Request $request)
    {
        $orgIds = $this->getCurrentUser()->getManageOrgIdsRecursively();
        $queryString = $request->query->get('q');
        $attributes = array('user', 'org', 'post', 'userGroup');
        $name = $this->getUserAttributeService()->searchAttributesName($attributes, $queryString, $orgIds);

        return $this->createJsonResponse($name);
    }

    public function createMemberAction(Request $request, $id)
    {
        if (!$this->getProjectPlanService()->canManageProjectPlan($id)) {
            return $this->createMessageResponse('error', 'project_plan.message.can_not_manage_message');
        }

        if ('POST' == $request->getMethod()) {
            $data = $request->request->all();

            $trainingMembers = json_decode($data['trainingMembers'], true);

            if (empty($trainingMembers)) {
                return $this->createMessageResponse('error', 'project_plan.member_manage.member_empty');
            }
            $orgIds = $this->getCurrentUser()->getManageOrgIdsRecursively();
            $userIds = $this->getUserAttributeService()->findDistinctUserIdsByAttributes($trainingMembers, $orgIds);

            if (count($userIds) > 2000) {
                return $this->createJsonResponse(array('success' => false, 'message' => '您不能一次添加超过2000个用户'));
            }
            $this->getProjectPlanMemberService()->batchBecomeMember($id, $userIds);

            return $this->createJsonResponse(array('success' => true));
        }

        return $this->render('project-plan/member/member-create-modal.html.twig',
            array(
                'id' => $id,
            )
        );
    }

    public function ajaxRemoveProjectPlanMemberAction(Request $request, $id)
    {
        if ('POST' == $request->getMethod()) {
            $result = $this->getProjectPlanMemberService()->deleteProjectPlanMember($id);

            if ($result) {
                return $this->createJsonResponse(true);
            }

            return $this->createJsonResponse(false);
        }

        $projectMember = $this->getProjectPlanMemberService()->getProjectPlanMember($id);
        $user = $this->getUserService()->getUser($projectMember['userId']);
        $userProfile = $this->getUserService()->getUserProfile($projectMember['userId']);
        $post = $this->getPostService()->getPost($user['postId']);
        $orgs = $this->getOrgService()->findOrgsByIds($user['orgIds']);

        return $this->render('project-plan/member/member-delete-modal.html.twig', array(
            'user' => $user,
            'post' => $post,
            'orgs' => $orgs,
            'userProfile' => $userProfile,
            'projectMember' => $projectMember,
        ));
    }

    public function membersRemoveAction(Request $request, $projectPlanId)
    {
        $userIds = $request->request->get('ids');
        $this->getProjectPlanMemberService()->batchDeleteMembers($projectPlanId, $userIds);

        return $this->createJsonResponse(true);
    }

    public function memberScoreModalShowAction(Request $request, $projectPlanId, $userId)
    {
        $projectPlan = $this->getProjectPlanService()->getProjectPlan($projectPlanId);
        if (empty($projectPlan)) {
            throw $this->createNotFoundException('Project Plan is not exist');
        }

        $results = $this->createProjectPlanStrategy()->getUserProjectPlanScoreResults($projectPlan, $userId);

        return $this->render(
            'project-plan/member/member-score-modal.html.twig',
            array(
                'results' => $results,
            )
        );
    }

    public function recordAction(Request $request, $source = null)
    {
        $user = $this->getCurrentUser();
        $conditions = array(
            'userId' => $user['id'],
        );
        $memberCount = $this->getProjectPlanMemberService()->countProjectPlanMembers($conditions);

        $paginator = new Paginator(
            $request,
            $memberCount,
            20
        );

        $members = $this->getProjectPlanMemberService()->searchProjectPlanMembers(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $projectPlans = $this->buildMemberProjectPlan($members);

        $view = 'study-center/department-manage/project-plan/record.html.twig';
        if ('user_learn' == $source) {
            $view = 'data-report/course-statistics/projectPlan-record.html.twig';
        } elseif ('offline_activity' == $source) {
            $view = 'offline-activity-manage/study-record/projectPlan-record.html.twig';
        }

        return $this->render(
            $view,
            array(
                'userId' => $request->get('userId'),
                'projectPlans' => $projectPlans,
                'paginator' => $paginator,
                'tab_types' => $request->get('tab_types', 'projectPlanRecord'),
            )
        );
    }

    public function verifyListAction(Request $request, $id)
    {
        if (!$this->getProjectPlanService()->canManageProjectPlan($id)) {
            throw new AccessDeniedException('project_plan.message.can_not_manage_message');
        }

        $conditions = $request->query->all();
        $projectPlan = $this->getProjectPlanService()->getProjectPlan($id);
        $conditions['projectPlanId'] = $id;
        $conditions['status'] = $request->query->get('status', 'all');
        $conditions = $this->prepareConditions($conditions);

        $recordCounts = $this->getProjectPlanMemberService()->countEnrollmentRecords($conditions);
        $paginator = new Paginator(
            $request,
            $recordCounts,
            20
        );
        $records = $this->getProjectPlanMemberService()->searchEnrollmentRecords(
            $conditions,
            array('submittedTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $userIds = ArrayToolkit::column($records, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);
        $profiles = $this->getUserService()->findUserProfilesByIds($userIds);
        $advancedOption = $this->getProjectPlanService()->getProjectPlanAdvancedOptionByProjectPlanId($id);

        return $this->render(
            '@CorporateTraining/project-plan/verify/verify-list.html.twig', array(
                'recordCounts' => $recordCounts,
                'projectPlan' => $projectPlan,
                'records' => $records,
                'users' => $users,
                'profiles' => $profiles,
                'advancedOption' => $advancedOption,
                'paginator' => $paginator,
            )
        );
    }

    public function batchAuditAction(Request $request)
    {
        return $this->render(
            'project-plan/batch-audit-modal.html.twig'
        );
    }

    public function batchPassAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $recordIds = $request->request->get('recordIds');
            $this->getProjectPlanMemberService()->passProjectPlansApply(explode(',', $recordIds));

            return $this->createJsonResponse(true);
        }
    }

    public function batchRejectAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $fields = $request->request->all();
            $this->getProjectPlanMemberService()->rejectProjectPlansApply(explode(',', $fields['recordIds']), $fields);

            return $this->createJsonResponse(true);
        }
    }

    public function verifyApplyAction(Request $request, $id)
    {
        $record = $this->getProjectPlanMemberService()->getEnrollmentRecord($id);

        if (!$this->getProjectPlanService()->canManageProjectPlan($record['projectPlanId'])) {
            throw new AccessDeniedException('project_plan.message.can_not_manage_message');
        }
        if ('POST' === $request->getMethod()) {
            $fields = $request->request->all();
            if ('rejected' == $fields['verifyStatus']) {
                $this->getProjectPlanMemberService()->rejectProjectPlansApply(array($record['id']), $fields);
            } elseif ('approved' == $fields['verifyStatus']) {
                $this->getProjectPlanMemberService()->passProjectPlansApply(array($record['id']));
            }

            return $this->createJsonResponse(true);
        }

        return $this->render('@CorporateTraining/project-plan/verify/verify-enrollment-apply.html.twig', array(
            'record' => $record,
        ));
    }

    public function attachmentShowAction(Request $request, $id)
    {
        $record = $this->getProjectPlanMemberService()->getEnrollmentRecord($id);
        $advancedOption = $this->getProjectPlanService()->getProjectPlanAdvancedOptionByProjectPlanId($record['projectPlanId']);

        return $this->render('@CorporateTraining/project-plan/material-attachment-show-modal.html.twig', array(
            'record' => $record,
            'advancedOption' => $advancedOption,
        ));
    }

    protected function prepareConditions($conditions)
    {
        if (!empty($conditions['username'])) {
            $userIds = $this->getUserService()->findUserIdsByNickNameOrTrueName($conditions['username']);
            if (empty($userIds)) {
                $conditions['userIds'] = array(-1);
            } else {
                $conditions['userIds'] = $userIds;
            }
            unset($conditions['username']);
        }

        if ('all' == $conditions['status']) {
            $conditions['excludeStatus'] = array('none');
            $conditions['status'] = '';
        }

        return $conditions;
    }

    private function buildMemberProjectPlan($members)
    {
        foreach ($members as &$member) {
            $projectPlan = $this->getProjectPlanService()->getProjectPlan($member['projectPlanId']);
            $member['name'] = $projectPlan['name'];
            $member['endTime'] = $projectPlan['endTime'];
            $learnTime = $this->createProjectPlanStrategy()->calculateMembersTotalLearnTime($projectPlan, array($member['userId']));
            $member['learnTime'] = empty($learnTime) ? 0 : $learnTime[$member['userId']];
            $member['progress'] = $this->createProjectPlanStrategy($projectPlan['id'])->calculateMemberLearnProgress($projectPlan, $member['userId']);
        }

        return $members;
    }

    protected function prepareMemberListSearchConditions($conditions)
    {
        if (!empty($conditions['username'])) {
            $userIds = $this->getUserService()->findUserIdsByNickNameOrTrueName($conditions['username']);
            if (empty($userIds)) {
                $conditions['userIds'] = array(-1);
            } else {
                $conditions['userIds'] = $userIds;
            }
            unset($conditions['username']);
        }

        return $conditions;
    }

    protected function getProjectPlanOrgUserIds($projectPlanId, $orgIds)
    {
        $members = $this->getProjectPlanMemberService()->findMembersByProjectPlanId($projectPlanId);

        $userIds = ArrayToolkit::column($members, 'userId');

        $users = $this->getUserOrgService()->searchUserOrgs(
            array('orgIds' => $orgIds, 'userIds' => $userIds),
            array(),
            0,
            PHP_INT_MAX
        );

        return ArrayToolkit::column($users, 'userId');
    }

    protected function createProjectPlanStrategy()
    {
        return $this->getBiz()->offsetGet('projectPlan_item_strategy_context')->createStrategy('course');
    }

    /**
     * @return ProjectPlanService
     */
    protected function getProjectPlanService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }

    /**
     * @return MemberService
     */
    protected function getProjectPlanMemberService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:MemberService');
    }

    /**
     * @return OrgService
     */
    protected function getOrgService()
    {
        return $this->createService('Org:OrgService');
    }

    /**
     * @return PostService
     */
    protected function getPostService()
    {
        return $this->createService('CorporateTrainingBundle:Post:PostService');
    }

    /**
     * @return UserOrgService
     */
    protected function getUserOrgService()
    {
        return $this->createService('CorporateTrainingBundle:User:UserOrgService');
    }

    /**
     * @return UserAttributeService
     */
    protected function getUserAttributeService()
    {
        return $this->createService('CorporateTrainingBundle:UserAttribute:UserAttributeService');
    }
}
