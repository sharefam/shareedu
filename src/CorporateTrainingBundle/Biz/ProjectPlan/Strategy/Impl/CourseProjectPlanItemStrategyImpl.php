<?php

namespace CorporateTrainingBundle\Biz\ProjectPlan\Strategy\Impl;

use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Biz\ProjectPlan\Strategy\ProjectPlanItemStrategy;
use CorporateTrainingBundle\Biz\ProjectPlan\Strategy\BaseProjectPlanItemStrategy;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;

class CourseProjectPlanItemStrategyImpl extends BaseProjectPlanItemStrategy implements ProjectPlanItemStrategy
{
    public function createItems($projectPlanId, $items, $itemType = 'course')
    {
        $this->checkProjectPlanExist($projectPlanId);
        $items['courseIds'] = json_decode($items['courseIds']);

        $items = $this->buildItems($items);

        $this->createCourseItems($projectPlanId, $items);
    }

    public function updateItem($id, $item, $itemType = 'course')
    {
        $item = $this->buildItems($item, 'update');
        $this->getProjectPlanService()->updateProjectPlanItem($id, array('startTime' => $item['startTime'], 'endTime' => $item['endTime']));
    }

    public function deleteItem($item)
    {
        return null;
    }

    public function getItem($item)
    {
        $course = $this->getCourseService()->getCourse($item['targetId']);
        $item['detail'] = $course;
        $tasks = $this->getCourseTaskService()->findTasksByCourseId($course['id']);
        $item['tasks'] = $tasks;

        return $item;
    }

    public function getTaskReviewNum($taskId)
    {
        return null;
    }

    public function getStudyResult($item, $user)
    {
        $courses = $this->getCourseService()->findUserLearningCourses($user['id'], 0, PHP_INT_MAX);
        $learningCourseIds = ArrayToolkit::column($courses, 'id');
        $totalLeanTime = $this->getTaskResultService()->sumLearnTimeByCourseIdAndUserId($item['targetId'], $user['id']);
        $member = $this->getCourseMemberService()->getCourseMember($item['targetId'], $user['id']);
        $course = $this->getCourseService()->getCourse($item['targetId']);
        $learnedCompulsoryTaskNum = isset($member['learnedCompulsoryTaskNum']) ? $member['learnedCompulsoryTaskNum'] : 0;
        $progress = $this->getCourseProgress($learnedCompulsoryTaskNum, $course['compulsoryTaskNum']);

        $result = array(
            'status' => (in_array($item['targetId'], $learningCourseIds)) ? 'ongoing' : $this->getStatus($item, $user),
            'totalLearnTime' => empty($totalLeanTime) ? 0 : $totalLeanTime,
            'progress' => ($this->isFinished($item, $user)) ? 100 : $progress,
            'learnedTaskNum' => $learnedCompulsoryTaskNum,
            'taskNum' => $course['compulsoryTaskNum'],
        );

        return $result;
    }

    public function getItemInfoByUserId($item, $userId)
    {
        return array();
    }

    public function findItemsDetail($items)
    {
        $courseIds = ArrayToolkit::column($items, 'targetId');
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);

        foreach ($items as &$item) {
            if (isset($courses[$item['targetId']])) {
                $item['detail'] = $courses[$item['targetId']];
            }
        }

        return ArrayToolkit::index($items, 'id');
    }

    public function findTasksByItemIdAndTimeRange($courseId, $timeRange)
    {
        $tasks = $this->getCourseTaskService()->findTaskByCourseIdAndTaskTypeAndTimeRange($courseId, 'live', $timeRange);

        return $tasks;
    }

    public function isFinished($item, $user)
    {
        $course = $this->getCourseService()->getCourse($item['targetId']);
        $member = $this->getCourseMemberService()->getCourseMember($course['id'], $user['id']);

        $result = false;

        if (0 != $course['compulsoryTaskNum'] && $member['learnedCompulsoryTaskNum'] >= $course['compulsoryTaskNum']) {
            $result = true;
        }

        return $result;
    }

    public function findFinishedMembers($item)
    {
        $course = $this->getCourseService()->getCourse($item['targetId']);
        $courseMembers = $this->getCourseMemberService()->searchMembers(
            array('courseId' => $item['targetId'], 'learnedNumGreaterThan' => $course['taskNum']),
            array('id' => 'ASC'),
            0,
            PHP_INT_MAX
        );

        if (empty($courseMembers)) {
            return array();
        }

        return $this->getProjectPlanMemberService()->searchProjectPlanMembers(
            array('userIds' => ArrayToolkit::column($courseMembers, 'userId'), 'projectPlanId' => $item['projectPlanId']),
            array('id' => 'ASC'),
            0,
            PHP_INT_MAX
        );
    }

    public function getFinishedMembersNum($item)
    {
        $course = $this->getCourseService()->getCourse($item['targetId']);
        $courseMembers = $this->getCourseMemberService()->searchMembers(
            array('courseId' => $item['targetId'], 'learnedNumGreaterThan' => $course['taskNum']),
            array('id' => 'ASC'),
            0,
            PHP_INT_MAX
        );

        if (empty($courseMembers)) {
            return 0;
        }

        return $this->getProjectPlanMemberService()->countProjectPlanMembers(array('userIds' => ArrayToolkit::column($courseMembers, 'userId'), 'projectPlanId' => $item['projectPlanId']));
    }

    public function findProjectPlanItemIds($projectPlan)
    {
        $projectPlanItems = $this->getProjectPlanService()->findProjectPlanItemsByProjectPlanIdAndTargetType($projectPlan['id'], 'course');
        $courseIds = ArrayToolkit::column($projectPlanItems, 'targetId');
        if (empty($courseIds)) {
            return array();
        }

        $courses = $this->getCourseService()->searchCourses(
            array('courseIds' => $courseIds, 'status' => 'published'),
            array(),
            0,
            PHP_INT_MAX
        );

        return ArrayToolkit::column($courses, 'id');
    }

    public function appendProgress($projectPlan, $members)
    {
        $courseIds = $this->findProjectPlanItemIds($projectPlan);

        foreach ($members as &$member) {
            if (empty($courseIds)) {
                $progress['percent'] = 0;
            } else {
                $progress = $this->getLearningDataAnalysisService()->getUserLearningProgressByCourseIds($courseIds, $member['userId']);
            }

            $member['progress'] = $progress['percent'];
        }

        return $members;
    }

    public function calculateMemberLearnProgress($projectPlan, $userId)
    {
        $courseIds = $this->findProjectPlanItemIds($projectPlan);

        if (empty($courseIds)) {
            $progress['percent'] = 0;
        } else {
            $progress = $this->getLearningDataAnalysisService()->getUserLearningProgressByCourseIds($courseIds, $userId);
        }

        return $progress['percent'];
    }

    public function calculateMembersTotalLearnTime($projectPlan, array $userIds)
    {
        $courseIds = $this->findProjectPlanItemIds($projectPlan);
        $courseIds = empty($courseIds) ? array(-1) : $courseIds;
        $usersTotalLearnTime = $this->getTaskResultService()->sumLearnTimeByCourseIdsAndUserIdsGroupByUserId($courseIds, ArrayToolkit::column($userIds, 'userId'));

        $usersTotalLearnTime = ArrayToolkit::index($usersTotalLearnTime, 'userId');
        $usersLearnTime = array();
        foreach ($userIds as $userId) {
            $totalLearnTime = empty($usersTotalLearnTime[$userId]) ? 0 : $usersTotalLearnTime[$userId]['totalLearnTime'];
            $usersLearnTime[$userId] = $totalLearnTime;
        }

        return $usersLearnTime;
    }

    public function getUserProjectPlanScoreResults($projectPlan, $userId)
    {
        $courseIds = $this->findProjectPlanItemIds($projectPlan);
        $conditions = array(
            'courseIds' => $courseIds,
            'userId' => $userId,
            'types' => array('homework', 'testpaper'),
        );

        return  $this->getTestPaperService()->searchTestpaperResults(
            $conditions,
            array('id' => 'ASC'),
            0,
            PHP_INT_MAX
        );
    }

    public function isProjectPlanFinished($projectPlanId, $userId)
    {
        $projectPlan = $this->getProjectPlanService()->getProjectPlan($projectPlanId);
        $memberProjectPlanProgress = $this->calculateMemberLearnProgress($projectPlan, $userId);

        $isFinished = false;
        if (100 === (int) $memberProjectPlanProgress) {
            $isFinished = true;
        }

        return $isFinished;
    }

    public function buildCoursesData($item, $userIds)
    {
        $defaultData = array(
            'itemCourseLearnTime' => 0,
            'courseProgress' => 0,
            'courseCompletionCount' => 0,
            'hasCompulsoryLearnRecords' => false,
            'countCompulsoryCourseTasks' => false,
            'reviewingHomeworkCount' => 0,
            'reviewingTestPaperCount' => 0,
        );

        $data['hasCompulsoryLearnRecords'] = empty($this->getTaskResultService()->countCompulsoryTaskResultsByCourseIdAndUserIds($item['targetId'], $userIds)) ? false : true;

        $conditions = array('courseId' => $item['targetId'], 'userIds' => $userIds, 'status' => 'reviewing');
        $data['reviewingHomeworkCount'] = $this->getTestpaperService()->searchTestpaperResultsCount(array_merge(array('type' => 'homework'), $conditions));
        $data['reviewingTestPaperCount'] = $this->getTestpaperService()->searchTestpaperResultsCount(array_merge(array('type' => 'testpaper'), $conditions));
        $data['itemCourseLearnTime'] = $defaultData['itemCourseLearnTime'];
        $data['courseProgress'] = $defaultData['courseProgress'];
        $data['courseCompletionCount'] = $defaultData['courseCompletionCount'];
        foreach ($userIds as $userId) {
            $learnTime = $this->getTaskResultService()->sumCompulsoryTasksLearnTimeByCourseIdAndUserId($item['targetId'], $userId);
            $data['itemCourseLearnTime'] += empty($learnTime) ? 0 : $learnTime;
            $countCompulsoryFinishedTasks = $this->getTaskResultService()->countFinishedCompulsoryTasksByUserIdAndCourseId($userId, $item['targetId']);
            $countCompulsoryCourseTasks = $this->getTaskService()->countTasks(array('courseId' => $item['targetId'], 'isOptional' => 0));
            $data['countCompulsoryCourseTasks'] = $countCompulsoryCourseTasks;
            $data['courseProgress'] += empty($countCompulsoryCourseTasks) ? 0 : $countCompulsoryFinishedTasks / $countCompulsoryCourseTasks;
            if (0 != $countCompulsoryCourseTasks && 1 == $countCompulsoryFinishedTasks / $countCompulsoryCourseTasks) {
                ++$data['courseCompletionCount'];
            }
        }

        $data = array_merge($defaultData, $data);

        return $data;
    }

    protected function createCourseItems($projectPlanId, $items)
    {
        $itemsNum = $this->getProjectPlanService()->countProjectPlanItems(
            array(
                'projectPlanId' => $projectPlanId,
            )
        );
        $seq = 1;
        try {
            $this->beginTransaction();
            foreach ($items['ids'] as $id) {
                $course = $this->getCourseService()->getCourse($id);
                if (empty($course)) {
                    throw new NotFoundException('Course Not Found');
                }
                $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);

                $hasPermission = $this->getManagePermissionService()->checkResourceUsePermission('courseSet', $courseSet['id'], $courseSet['orgId']);
                if (!$hasPermission) {
                    continue;
                }

                $item = $this->getProjectPlanService()->getProjectPlanItemByProjectPlanIdAndTargetIdAndTargetType($projectPlanId, $id, 'course');
                if (!empty($item)) {
                    continue;
                }
                $projectPlanItem = array(
                    'targetId' => $id,
                    'projectPlanId' => $projectPlanId,
                    'seq' => $itemsNum + $seq++,
                    'targetType' => 'course',
                    'startTime' => $items['startTime'],
                    'endTime' => $items['endTime'],
                );
                $this->getProjectPlanService()->createProjectPlanItem($projectPlanItem);
            }
            $this->commit();
        } catch (\Exception $e) {
            $this->getLogger()->error('projectPlanBatchCreateCourse:'.$e->getMessage());
            $this->rollback();
            throw $e;
        }
    }

    protected function buildItems($params, $action = '')
    {
        $params['startTime'] = empty($params['startTime']) ? 0 : strtotime($params['startTime']);
        $params['endTime'] = empty($params['endTime']) ? 0 : strtotime($params['endTime']);

        if ('update' == $action) {
            return $params;
        }
        $courseIds = array_unique($params['courseIds']);
        if (empty($courseIds)) {
            throw new \InvalidArgumentException('Empty Data');
        }
        $items = array(
            'ids' => $courseIds,
            'startTime' => $params['startTime'],
            'endTime' => $params['endTime'],
        );

        return $items;
    }

    protected function getCourseProgress($learnedCompulsoryTaskNum, $compulsoryTaskNum)
    {
        if (empty($compulsoryTaskNum)) {
            return 0;
        }

        return round(($learnedCompulsoryTaskNum / $compulsoryTaskNum) * 100, 0);
    }

    protected function getStatus($item, $user)
    {
        if ($this->isFinished($item, $user)) {
            return 'finished';
        }

        return 'notStart';
    }

    /**
     * @return \Biz\Course\Service\Impl\CourseServiceImpl
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return \Biz\Course\Service\Impl\MemberServiceImpl
     */
    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    /**
     * @return \Biz\Task\Service\Impl\TaskServiceImpl
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return \Biz\Course\Service\Impl\CourseSetServiceImpl
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\Task\Service\Impl\TaskResultServiceImpl
     */
    protected function getTaskResultService()
    {
        return $this->createService('CorporateTrainingBundle:Task:TaskResultService');
    }

    /**
     * @return \Biz\Activity\Service\Impl\ActivityServiceImpl
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    /**
     * @return \Biz\Testpaper\Service\Impl\TestpaperServiceImpl
     */
    protected function getTestPaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    /**
     * @return \Biz\Course\Service\Impl\LearningDataAnalysisServiceImpl
     */
    protected function getLearningDataAnalysisService()
    {
        return $this->createService('Course:LearningDataAnalysisService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\Task\Service\Impl\TaskServiceImpl
     */
    protected function getCourseTaskService()
    {
        return $this->createService('CorporateTrainingBundle:Task:TaskService');
    }
}
