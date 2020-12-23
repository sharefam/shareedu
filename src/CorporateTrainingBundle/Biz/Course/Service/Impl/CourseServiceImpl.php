<?php

namespace CorporateTrainingBundle\Biz\Course\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\Impl\CourseServiceImpl as BaseService;
use Codeages\Biz\Framework\Event\Event;
use CorporateTrainingBundle\Biz\Course\Service\CourseService;
use CorporateTrainingBundle\Biz\PostCourse\Service\UserPostCourseService;
use CorporateTrainingBundle\Biz\ProjectPlan\Service\MemberService;

class CourseServiceImpl extends BaseService implements CourseService
{
    /**
     * @param $id
     * @param $fields
     *
     * @return mixed
     *
     * @throws \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     *                                                                            原营销设置中增加任务规则的修改，此处复写updateCourseMarketing中过滤方法增加enableFinish字段
     */
    public function updateCourseMarketing($id, $fields)
    {
        $oldCourse = $this->getCourse($id);

        $fields = ArrayToolkit::parts(
            $fields,
            array(
                'enableFinish',
                'isFree',
                'originPrice',
                'vipLevelId',
                'buyable',
                'tryLookable',
                'tryLookLength',
                'watchLimit',
                'buyExpiryTime',
                'showServices',
                'services',
                'approval',
                'coinPrice',
                'expiryMode', //days、end_date、date、forever
                'expiryDays',
                'expiryStartDate',
                'expiryEndDate',
                'taskRewardPoint',
                'rewardPoint',
            )
        );

        if ('published' == $oldCourse['status'] || 'closed' == $oldCourse['status']) {
            unset($fields['expiryMode']);
            unset($fields['expiryDays']);
            unset($fields['expiryStartDate']);
            unset($fields['expiryEndDate']);
        }

        $requireFields = array('isFree', 'buyable');
        $courseSet = $this->getCourseSetService()->getCourseSet($oldCourse['courseSetId']);
        if ('normal' == $courseSet['type'] && $this->isCloudStorage()) {
            array_push($requireFields, 'tryLookable');
        } else {
            $fields['tryLookable'] = 0;
        }

        if (!ArrayToolkit::requireds($fields, $requireFields)) {
            throw $this->createInvalidArgumentException('Lack of required fields');
        }

        $fields = $this->validateExpiryMode($fields);

        $fields = $this->processFields($id, $fields, $courseSet);

        $newCourse = $this->getCourseDao()->update($id, $fields);

        $this->dispatchEvent('course.update', new Event($newCourse));
        $this->dispatchEvent('course.marketing.update', array('oldCourse' => $oldCourse, 'newCourse' => $newCourse));

        return $newCourse;
    }

    // Refactor: 该函数方法名和逻辑表达的意思不一致,当用户为该课程的讲师，则不记入加入课程数
    public function countUserLearnCourse($userId)
    {
        return $this->getMemberService()->countMembers(array('userId' => $userId, 'role' => 'student'));
    }

    // 当用户为该课程的讲师，则不记入学习档案详细中
    public function findUserLearnCourses($userId)
    {
        $members = $this->getMemberService()->searchMembers(array('userId' => $userId, 'role' => 'student'), array(), 0, PHP_INT_MAX);
        $courseIds = ArrayToolkit::column($members, 'courseId');

        return $this->findCoursesByIds($courseIds);
    }

    public function getTopCategorySummaryDatas($userId, $num = 5)
    {
        $courses = $this->findUserLearnCourses($userId);
        $courses = $this->getCourseSetService()->findCourseSetsByCourseIds(ArrayToolkit::column($courses, 'id'));

        $coursesGroupByCategory = ArrayToolkit::group($courses, 'categoryId');
        $coursesGroupByCategoryCount = array();
        foreach ($coursesGroupByCategory as $key => $courses) {
            $coursesGroupByCategoryCount[$key] = count($courses);
        }
        arsort($coursesGroupByCategoryCount);

        $count = 1;
        $topCategoryDatas = array();
        foreach ($coursesGroupByCategoryCount as $key => $value) {
            if ($count++ > $num) {
                break;
            }
            $topCategoryDatas[$key]['courseNum'] = $value;
            $categoryIds = ArrayToolkit::column($coursesGroupByCategory[$key], 'categoryId');
            $courseIds = ArrayToolkit::column($coursesGroupByCategory[$key], 'id');
            $conditions['courseIds'] = !empty($courseIds) ? $courseIds : array(-1);
            $conditions['userId'] = $userId;
            $coursesWatchTime = $this->getTaskResultService()->sumWatchTimeByCategoryIdAndUserId($categoryIds[0],
                $userId);
            $topCategoryDatas[$key]['watchTime'] = $coursesWatchTime;
            $coursesLearnTime = $this->getTaskResultService()->sumLearnTimeByCategoryIdAndUserId($categoryIds[0],
                $userId);
            $topCategoryDatas[$key]['learnTime'] = $coursesLearnTime;
        }

        return $topCategoryDatas;
    }

    public function canManageCourse($courseId, $courseSetId = 0)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            return false;
        }

        $course = $this->getCourseDao()->get($courseId);

        if (empty($course)) {
            return false;
        }

        $courseSet = $this->getCourseSetDao()->get($course['courseSetId']);

        $teachers = $this->getMemberService()->findCourseTeachers($courseId);
        $course['teacherIds'] = ArrayToolkit::column($teachers, 'userId');

        if (!$this->getCurrentUser()->hasManagePermissionWithOrgCode($courseSet['orgCode']) && !in_array($user['id'], $course['teacherIds'])) {
            return false;
        }

        if ($courseSetId > 0 && $course['courseSetId'] !== $courseSetId) {
            return false;
        }

        if (!$this->hasCourseManagerRole($courseId)) {
            return false;
        }

        return true;
    }

    /**
     * @Custom 权限改造，删除tryManageCourse方法调用
     */
    public function tryManageCourse($courseId, $courseSetId = 0)
    {
        if (!$this->canManageCourse($courseId, $courseSetId)) {
            throw $this->createAccessDeniedException($this->trans('admin.manage.course_no_manage_permission'));
        }

        return $this->getCourseDao()->get($courseId);
    }

    public function hasCourseManagerRole($courseId = 0)
    {
        $user = $this->getCurrentUser();
        //未登录，无权限管理
        if (!$user->isLogin()) {
            return false;
        }

        //不是管理员，无权限管理
        if ($this->hasAdminRole()) {
            return true;
        }

        if ($user->isTrainingAdmin()) {
            return true;
        }

        $course = $this->getCourse($courseId);
        //课程不存在，无权限管理
        if (empty($course)) {
            return false;
        }

        if ($course['creator'] == $user->getId()) {
            return true;
        }

        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        if ($user->getId() == $courseSet['creator']) {
            return true;
        }

        $teacher = $this->getMemberService()->isCourseTeacher($courseId, $user->getId());
        //不是课程讲师，无权限管理
        if ($teacher) {
            return true;
        }

        return false;
    }

    public function findCourseItemsByUserId($courseId, $userId, $limitNum = 0)
    {
        $course = $this->getCourse($courseId);
        if (empty($course)) {
            throw $this->createNotFoundException("Course#{$courseId} Not Found");
        }
        $tasks = $this->findTasksByCourseIdAndUserId($courseId, $userId);

        return $this->createCourseStrategy($course)->prepareCourseItems($courseId, $tasks, $limitNum);
    }

    public function findStudentsByCourseIds($courseIds)
    {
        $students = $this->getMemberDao()->findMembersByCourseIdsAndRole($courseIds, 'student');

        return $this->fillMembersWithUserInfo($students);
    }

    public function findTeachersByCourseIds($courseIds)
    {
        $teachers = $this->getMemberDao()->findMembersByCourseIdsAndRole($courseIds, 'teacher');

        return $this->fillMembersWithUserInfo($teachers);
    }

    /*
     * 内训版：岗位课程或培训项目或专题直接加入
     */
    public function canUserAutoJoinCourse($user, $courseId)
    {
        $member = $this->getMemberService()->getCourseMember($courseId, $user['id']);
        if (!empty($member)) {
            return false;
        }

        $isBelongPostCourse = $this->getUserPostCourseService()->isCourseBelongToUserPostCourse($courseId, $user);

        $isBelongProjectPlan = $this->getProjectPlanMemberService()->isBelongToUserProjectPlan($user['id'], $courseId, 'course');

        $isBelongClassroom = $this->getClassroomService()->isCourseBelongToUserClassroom($courseId, $user);

        return $isBelongPostCourse || $isBelongProjectPlan || $isBelongClassroom;
    }

    protected function findTasksByCourseIdAndUserId($courseId, $userId)
    {
        $user = $this->getUserService()->getUser($userId);
        if (empty($user)) {
            throw $this->createNotFoundException("User#{$userId} Not Found");
        }

        return $this->getTaskService()->findTasksFetchActivityAndResultByCourseIdAndUserId($courseId, $userId);
    }

    private function processFields($id, $fields, $courseSet)
    {
        if (isset($fields['originPrice'])) {
            list($fields['price'], $fields['coinPrice']) = $this->calculateCoursePrice($id, $fields['originPrice']);
        }

        if (1 == $fields['isFree']) {
            $fields['price'] = 0;
        }

        if ('normal' == $courseSet['type'] && 0 == $fields['tryLookable']) {
            $fields['tryLookLength'] = 0;
        }

        if (!empty($fields['buyExpiryTime'])) {
            if (is_numeric($fields['buyExpiryTime'])) {
                $fields['buyExpiryTime'] = date('Y-m-d', $fields['buyExpiryTime']);
            }

            $fields['buyExpiryTime'] = strtotime($fields['buyExpiryTime'].' 23:59:59');
        } else {
            $fields['buyExpiryTime'] = 0;
        }

        return $fields;
    }

    /**
     * @return 训版所有课程都是免费暂无试看功能
     */
    protected function isCloudStorage()
    {
        return false;
    }

    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }

    /**
     * @return UserPostCourseService
     */
    protected function getUserPostCourseService()
    {
        return $this->createService('CorporateTrainingBundle:PostCourse:UserPostCourseService');
    }

    /**
     * @return MemberService
     */
    protected function getProjectPlanMemberService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:MemberService');
    }
}
