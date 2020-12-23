<?php

namespace CorporateTrainingBundle\Controller\StudyCenter;

use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Biz\Course\Service\MemberService;
use AppBundle\Controller\BaseController;

class MyTaskController extends BaseController
{
    public function myTaskHeaderAction(Request $request, $tab_nav)
    {
        $user = $this->getCurrentUser();
        $postCourses = $this->getPostCourseService()->findPostCoursesByPostId($user['postId']);

        if (!empty($postCourses)) {
            $courseIds = ArrayToolkit::column($postCourses, 'courseId');
            $learnedCoursesNum = $this->getPostCourseService()->countUserLearnedPostCourses($user['id'], $courseIds);
            $unfinishedPostCoursesNum = count($this->getPostCourseService()->findUserLearningPostCourses($user['id'], $courseIds));
            $learnTime = $this->sumLearnTime($user['id'], $user['postId']);
            $recentStartTask = $this->findRecentStartTask($user['id'], $courseIds);
        }

        $projectPlanIds = $this->getProjectPlanMemberService()->searchProjectPlanMembers(
            array('userId' => $user['id']),
            array(),
            0,
            PHP_INT_MAX
        );
        $projectPlanIds = ArrayToolkit::column($projectPlanIds, 'projectPlanId');
        if ($projectPlanIds) {
            $unfinishedProjectPlansNum = $this->getProjectPlanService()->countUnfinishedProjectPlansByCurrentUserId();
        }

        return $this->render(
            'study-center/my-task/header.html.twig',
            array(
                'courseIds' => empty($courseIds) ? array() : $courseIds,
                'learnTime' => empty($learnTime) ? 0 : $learnTime,
                'learnedCoursesNum' => empty($learnedCoursesNum) ? 0 : $learnedCoursesNum,
                'recentStartTask' => empty($recentStartTask) ? array() : $recentStartTask,
                'tab_nav' => $tab_nav,
                'trainingNum' => empty($unfinishedProjectPlansNum) ? null : $unfinishedProjectPlansNum,
                'postNum' => empty($unfinishedPostCoursesNum) ? null : $unfinishedPostCoursesNum,
            )
        );
    }

    public function postCourseListAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $postCourses = $this->getPostCourseService()->searchPostCourses(
            array('postId' => $user['postId']),
            array('seq' => 'ASC'),
            0,
            PHP_INT_MAX
        );

        if (!empty($postCourses)) {
            $courseIds = ArrayToolkit::column($postCourses, 'courseId');
            $courses = $this->getPostCourseService()->findUserLearningPostCourses($user['id'], $courseIds);
            $courses = $this->caculateProgress($user['id'], $courses);
            $courses = $this->caculateLearnTime($user['id'], $courses);
            $courses = $this->sortCourseByPostCourseIds(ArrayToolkit::index($courses, 'id'), $courseIds);
        }

        return $this->render(
            'study-center/my-task/post-courses.html.twig',
            array(
            'courses' => empty($courses) ? array() : $courses,
            'currentTime' => time(),
            )
        );
    }

    public function projectPlanListAction()
    {
        $user = $this->getCurrentUser();
        $projectPlanMembers = $this->getProjectPlanMemberService()->searchProjectPlanMembers(
            array('userId' => $user['id']),
            array(),
            0,
            PHP_INT_MAX
        );
        $projectPlanIds = ArrayToolkit::column($projectPlanMembers, 'projectPlanId');

        $projectPlans = array();

        if ($projectPlanIds) {
            $projectPlans = $this->getProjectPlanService()->findUnfinishedProjectPlansByCurrentUserId(0, PHP_INT_MAX);
        }

        return $this->render('study-center/my-task/project-plan/project-plan-list.html.twig',
            array(
                'projectPlans' => empty($projectPlans) ? array() : $projectPlans,
                'total' => empty($projectPlans) ? 0 : count($projectPlans),
            )
        );
    }

    public function taskListAction(Request $request, $courseId, $tab_type, $userId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        return $this->forward('CorporateTrainingBundle:StudyCenter/Task:list', array(
            'course' => $course,
            'tab_type' => $tab_type,
            'userId' => $userId,
        ));
    }

    public function itemListAction($id)
    {
        $projectPlan = $this->getProjectPlanService()->getProjectPlan($id);

        $projectPlanItems = $this->getProjectPlanService()->searchProjectPlanItems(
            array('projectPlanId' => $id),
            array('seq' => 'ASC'),
            0,
            PHP_INT_MAX
        );

        return $this->render('study-center/widget/project-plan-item-list.html.twig', array(
            'projectPlanItems' => $projectPlanItems,
            'projectPlan' => $projectPlan,
        ));
    }

    public function courseTaskListAction($projectPlanItem, $projectPlanId, $tab_type, $userId)
    {
        $projectPlanItem = $this->getProjectPlanService()->getProjectPlanItem($projectPlanItem['id']);

        $user = $this->getUserService()->getUser($userId);
        if (empty($user)) {
            $user = $this->getCurrentUser();
        }

        $members = $this->getProjectPlanMemberService()->findMembersByProjectPlanId($projectPlanId);
        $memberUserIds = ArrayToolkit::column($members, 'userId');

        if ($projectPlanItem['targetType'] == 'exam' && $this->isPluginInstalled('Exam')) {
            $member = $this->getExamService()->getMemberByExamIdIdAndUserId($projectPlanItem['targetId'], $userId);
            if (empty($member)) {
                $member = $this->getExamService()->createMember(array('examId' => $projectPlanItem['targetId'], 'userId' => $userId));
            }
        }

        if ($projectPlanItem['targetType'] == 'course') {
            $course = $this->getCourseService()->getCourse($projectPlanItem['targetId']);
            $courseItems = $this->getCourseService()->findCourseItems($course['id']);
            $courseMember = $this->getMemberService()->getCourseMember($course['id'], $userId);
        }

        return $this->render(
            'study-center/task-list/list.html.twig',
            array(
                'projectPlanId' => $projectPlanId,
                'projectPlanItem' => $projectPlanItem,
                'course' => empty($course) ?: $course,
                'courseItems' => empty($courseItems) ?: $courseItems,
                'courseMember' => empty($courseMember) ?: $courseMember,
                'member' => empty($member) ? $memberUserIds : $member,
                'tab_type' => $tab_type,
                'userId' => $userId,
                'currentTime' => time(),
            )
        );
    }

    public function ajaxGetProjectPlanRowHtmlAction(Request $request)
    {
        $start = $request->query->get('start', 0);

        $projectPlans = $this->getProjectPlanService()->findUnfinishedProjectPlansByCurrentUserId($start, 20);
        $projectPlans = $this->appendProjectPlanProgress($projectPlans);

        return $this->render('study-center/my-task/project-plan/project-plan-item.html.twig', array(
            'projectPlans' => $projectPlans,
            'user' => $this->getCurrentUser(),
        ));
    }

    private function sumLearnTime($userId, $postId)
    {
        $learnTime = $this->getTaskResultService()->sumLearnTimeByPostIdAndUserId($postId, $userId);

        return $learnTime;
    }

    private function sortCourseByPostCourseIds($courses, $courseIds)
    {
        $coursesOrderByIds = array();
        foreach ($courseIds as $key => $courseId) {
            if (in_array($courseId, array_keys($courses))) {
                array_push($coursesOrderByIds, $courses[$courseId]);
            }
            continue;
        }

        return ArrayToolkit::index($coursesOrderByIds, 'id');
    }

    private function findRecentStartTask($userId, $courseIds)
    {
        $taskResults = $this->getTaskResultService()->searchTaskResults(
            array('userId' => $userId, 'courseIds' => $courseIds),
            array('createdTime' => 'DESC'),
            0, 1
        );

        $taskResult = array_shift($taskResults);

        if (empty($taskResult)) {
            return array();
        }

        $task = $this->getTaskService()->getTask($taskResult['courseTaskId']);

        return $task;
    }

    private function caculateProgress($userId, $courses)
    {
        foreach ($courses as &$course) {
            $course['finishedTaskNum'] = $this->getTaskResultService()->countTaskResults(array(
                'courseId' => $course['id'],
                'userId' => $userId,
                'status' => 'finish',
            ));

            $progress = empty($course['taskNum']) ? 0 : round($course['finishedTaskNum'] / $course['taskNum'], 2) * 100;
            $course['progress'] = $progress > 100 ? 100 : $progress;

            $course['toLearnTask'] = $this->findToLearnTaskByCourseId($userId, $course['id']);
        }

        return $courses;
    }

    private function caculateLearnTime($userId, $courses)
    {
        foreach ($courses as &$course) {
            $course['learnTime'] = $this->getTaskResultService()->sumLearnTimeByCourseIdAndUserId(
                $course['id'],
                $userId
            );
        }

        return $courses;
    }

    private function findToLearnTaskByCourseId($userId, $courseId)
    {
        if ($this->getMemberService()->isCourseMember($courseId, $userId)) {
            $toLearnTasks = $this->getTaskService()->findToLearnTasksByCourseId($courseId);
        } else {
            $toLearnTasks = $this->getTaskService()->searchTasks(
                array('courseId' => $courseId, 'status' => 'published'),
                array('seq' => 'ASC'),
                0, 1
            );
        }

        return empty($toLearnTasks) ? array() : $toLearnTasks[0];
    }

    protected function appendProjectPlanProgress($projectPlans)
    {
        $user = $this->getCurrentUser();

        foreach ($projectPlans as &$projectPlan) {
            $projectPlan['progress'] = $this->getProjectPlanService()->getPersonalProjectPlanProgress($projectPlan['id'], $user['id']);
        }

        return $projectPlans;
    }

    protected function getPostCourseService()
    {
        return $this->createService('CorporateTrainingBundle:PostCourse:PostCourseService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    protected function getActivityLearnLogService()
    {
        return $this->createService('Activity:ActivityLearnLogService');
    }

    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }

    protected function getProjectPlanMemberService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:MemberService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\ProjectPlanServiceImpl
     */
    protected function getProjectPlanService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }

    protected function createProjectPlanStrategy()
    {
        $strategy = $this->getBiz()->offsetGet('projectPlan_item_strategy_context');

        return $strategy->createStrategy('course');
    }

    protected function getExamService()
    {
        return $this->createService('ExamPlugin:Exam:ExamService');
    }

    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }
}
