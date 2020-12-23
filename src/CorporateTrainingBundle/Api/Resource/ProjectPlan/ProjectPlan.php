<?php

namespace CorporateTrainingBundle\Api\Resource\ProjectPlan;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Biz\ProjectPlan\Service\ProjectPlanService;
use CorporateTrainingBundle\Biz\ResourceScope\Service\ResourceVisibleScopeService;
use CorporateTrainingBundle\Biz\Taxonomy\Service\CategoryService;
use CorporateTrainingBundle\Biz\User\Service\UserService;
use CorporateTrainingBundle\Biz\ProjectPlan\Service\MemberService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProjectPlan extends AbstractResource
{
    public function get(ApiRequest $request, $projectPlanId)
    {
        $projectPlan = $this->getProjectPlanService()->getProjectPlan($projectPlanId);
        if (empty($projectPlan)) {
            throw new NotFoundHttpException('培训项目不存在', null, ErrorCode::RESOURCE_NOT_FOUND);
        }

        $projectPlanInfo = $this->buildProjectPlanInfo($projectPlan);

        return $projectPlanInfo;
    }

    public function search(ApiRequest $request)
    {
        $fields = $request->query->all();
        $conditions = array(
            'status' => 'published',
            'requireEnrollment' => isset($fields['requireEnrollment']) ? $fields['requireEnrollment'] : 1,
            'currentState' => isset($fields['currentState']) ? $fields['currentState'] : 'ongoing',
        );
        $conditions['ids'] = $this->getResourceVisibleScopeService()->findVisibleResourceIdsByResourceTypeAndUserId('projectPlan', $this->getCurrentUser()->getId());

        $total = $this->getProjectPlanService()->countProjectPlans($conditions);
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $projectPlans = $this->getProjectPlanService()->searchProjectPlans(
            $conditions,
            array('enrollmentEndDate' => 'ASC'),
            $offset,
            $limit
        );
        $projectPlans = $this->buildProjectPlansInfo($projectPlans);
        $projectPlans = $this->buildUserProjectPlans($projectPlans);

        return $this->makePagingObject($projectPlans, $total, $offset, $limit);
    }

    protected function buildProjectPlanInfo($projectPlan)
    {
        $user = $this->getCurrentUser();
        $member = $this->getProjectPlanMemberService()->getProjectPlanMemberByUserIdAndProjectPlanId($user['id'], $projectPlan['id']);
        $projectPlanItems = $this->getProjectPlanService()->findProjectPlanItemsByProjectPlanId($projectPlan['id']);
        $projectPlanItems = ArrayToolkit::index($projectPlanItems, 'id');
        foreach ($projectPlanItems as $key => $projectPlanItem) {
            if (!empty($projectPlanItem['detail']['teacherIds'][0])) {
                $userProfile = $this->getUserService()->getUserProfile($projectPlanItem['detail']['teacherIds'][0]);
                $teacherId = $projectPlanItem['detail']['teacherIds'][0];
                $teacher = $this->getUserService()->getUser($teacherId);
                $userName = !empty($userProfile['truename']) ? $userProfile['truename'] : $teacher['nickname'];
            }
            $projectPlanItemsDetail[] = array(
                'title' => isset($projectPlanItem['detail']['title']) ? $projectPlanItem['detail']['title'] : $projectPlanItem['detail']['name'],
                'place' => isset($projectPlanItem['detail']['place']) ? $projectPlanItem['detail']['place'] : '',
                'targetType' => $projectPlanItem['targetType'],
                'targetId' => $projectPlanItem['targetId'],
                'teacherName' => empty($userName) ? '' : $userName,
                'startTime' => $projectPlanItem['startTime'],
                'endTime' => $projectPlanItem['endTime'],
                'studyResult' => empty($member) ? array() : $this->getStudyResult($projectPlanItem, $user),
                'taskInfo' => empty($member) ? array() : $this->getItemInfoByUserId($projectPlanItem, $user['id']),
            );
        }

        $category = $this->getCategoryService()->getCategory($projectPlan['categoryId']);
        $projectPlanInfo = array(
            'name' => $projectPlan['name'],
            'cover' => $projectPlan['cover'],
            'summary' => $projectPlan['summary'],
            'startTime' => $projectPlan['startTime'],
            'endTime' => $projectPlan['endTime'],
            'enrollmentStartDate' => $projectPlan['enrollmentStartDate'],
            'enrollmentEndDate' => $projectPlan['enrollmentEndDate'],
            'maxStudentNum' => $projectPlan['maxStudentNum'],
            'studentNum' => (string) $this->getProjectPlanMemberService()->countProjectPlanMembers(array('projectPlanId' => $projectPlan['id'])),
            'categoryName' => $category['name'],
            'applyStatus' => $this->getUserApplyStatus($projectPlan['id'], $user['id']),
            'itemsDetail' => isset($projectPlanItemsDetail) ? $projectPlanItemsDetail : array(),
        );

        return $projectPlanInfo;
    }

    protected function getUserApplyStatus($projectPlanId, $userId)
    {
        $advancedOption = $this->getProjectPlanService()->getProjectPlanAdvancedOptionByProjectPlanId($projectPlanId);
        $applyStatus = $this->getProjectPlanService()->getUserApplyStatus($projectPlanId, $userId);

        if (('reset' == $applyStatus || 'enrollAble' == $applyStatus) && (!empty($advancedOption['requireRemark']) || !empty($advancedOption['requireMaterial']))) {
            return 'enrollInWeb';
        }

        return $applyStatus;
    }

    protected function getStudyResult($projectPlanItem, $user)
    {
        $strategy = $this->createProjectPlanStrategy($projectPlanItem['targetType']);
        $status = $strategy->getStudyResult($projectPlanItem, $user);

        return $status;
    }

    protected function getItemInfoByUserId($projectPlanItem, $userId)
    {
        $strategy = $this->createProjectPlanStrategy($projectPlanItem['targetType']);
        $itemInfo = $strategy->getItemInfoByUserId($projectPlanItem, $userId);

        return $itemInfo;
    }

    protected function buildProjectPlansInfo($projectPlans)
    {
        $categoryIds = ArrayToolkit::column($projectPlans, 'categoryId');
        $categories = $this->getCategoryService()->findCategoriesByIds($categoryIds);
        $categories = ArrayToolkit::index($categories, 'id');

        foreach ($projectPlans as &$projectPlan) {
            $projectPlan['currentState'] = $this->getProjectPlanCurrentState($projectPlan);
            $projectPlan['categoryName'] = empty($categories[$projectPlan['categoryId']]) ? '' : $categories[$projectPlan['categoryId']]['name'];
            $projectPlan['studentNum'] = (string) $this->getProjectPlanMemberService()->countProjectPlanMembers(array('projectPlanId' => $projectPlan['id']));
        }

        return $projectPlans;
    }

    protected function buildUserProjectPlans($projectPlans)
    {
        foreach ($projectPlans as &$projectPlan) {
            $projectPlan['applyStatus'] = $this->getUserApplyStatus($projectPlan['id'], $this->getCurrentUser()->getId());
        }

        return $projectPlans;
    }

    protected function getProjectPlanCurrentState($projectPlan)
    {
        if (time() > $projectPlan['endTime']) {
            return 'end';
        }

        if (time() > $projectPlan['startTime'] && time() < $projectPlan['endTime']) {
            return 'ongoing';
        }

        return 'notStart';
    }

    /**
     * @return ProjectPlanService
     */
    protected function getProjectPlanService()
    {
        return $this->service('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }

    protected function createProjectPlanStrategy($type)
    {
        return $this->biz->offsetGet('projectPlan_item_strategy_context')->createStrategy($type);
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->service('CorporateTrainingBundle:User:UserService');
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return $this->service('Taxonomy:CategoryService');
    }

    /**
     * @return MemberService
     */
    protected function getProjectPlanMemberService()
    {
        return $this->service('CorporateTrainingBundle:ProjectPlan:MemberService');
    }

    /**
     * @return ResourceVisibleScopeService
     */
    protected function getResourceVisibleScopeService()
    {
        return $this->service('ResourceScope:ResourceVisibleScopeService');
    }
}
