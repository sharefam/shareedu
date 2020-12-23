<?php

namespace CorporateTrainingBundle\Controller\StudyCenter;

use AppBundle\Common\ArrayToolkit;
use Biz\Group\Service\GroupService;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\BaseController;

class SideBarController extends BaseController
{
    const  ROOT_ORG_CODE = '1.';

    public function liveCourseAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $taskList = $this->getTaskService()->searchTasks(
            array('type' => 'live', 'endTime_GT' => time(), 'status' => 'published', 'copyId' => 0),
            array('startTime' => 'ASC'),
            0, PHP_INT_MAX
        );

        if (!empty($taskList)) {
            $courseIds = $this->findOrgCoursesAndFullSiteCourseIds($user, $taskList);
            $tasks = $this->findTaskTopFive($courseIds, $taskList);
            foreach ($tasks as &$task) {
                $task['isMember'] = $this->getMemberService()->isCourseMember($task['courseId'], $user['id']);
            }
        }

        return $this->render(
            'study-center/side-bar/live-course.html.twig',
            array(
            'tasks' => empty($tasks) ? array() : $tasks,
            'currentTime' => time(),
            )
        );
    }

    public function groupListAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if ($user['id']) {
            $members = $this->getGroupService()->searchMembers(array('userId' => $user['id']), array(), 0, PHP_INT_MAX);

            $groupIds = ArrayToolkit::column($members, 'groupId');

            $groupIds = empty($groupIds) ? array(-1) : $groupIds;
            $hotThreads = $this->getGroupThreadService()->searchThreads(
                array(
                    'status' => 'open',
                    'groupIds' => $groupIds,
                ),
                array(
                    'updatedTime' => 'DESC',
                ),
                0,
                PHP_INT_MAX
            );

            $threadGroupIds = ArrayToolkit::column($hotThreads, 'groupId');
            $threadGroupIds = array_unique($threadGroupIds);
            $newestThreadGroupIds = array_slice($threadGroupIds, 0, 3);

            $groups = $this->getGroupService()->getGroupsByIds($newestThreadGroupIds);

            $myJoinGroups = array();
            foreach ($newestThreadGroupIds as $groupId) {
                foreach ($groups as $id => $group) {
                    if ($groupId == $id) {
                        array_push($myJoinGroups, $group);
                    }
                }
            }
        }

        return $this->render('study-center/side-bar/group-list.html.twig', array(
                'user' => $user,
                'myJoinGroups' => $myJoinGroups,
            )
        );
    }

    public function rankListAction(Request $request)
    {
        return $this->render(
            'study-center/side-bar/study-rank-list.html.twig'
        );
    }

    public function newsAction(Request $request)
    {
        $articles = $this->getArticleService()->searchArticles(
            array('status' => 'published'),
            array('publishedTime' => 'DESC'),
            0, 3
        );

        return $this->render(
            'study-center/side-bar/news.html.twig',
            array(
            'articles' => $articles,
            )
        );
    }

    public function hotCoursesAction(Request $request)
    {
        return $this->render(
            'study-center/side-bar/hot-courses.html.twig'
        );
    }

    public function hotTopicsAction(Request $request)
    {
        return $this->render(
            'study-center/side-bar/hot-topics.html.twig'
        );
    }

    public function discussesAction(Request $request, $postId)
    {
        $userId = $this->getCurrentUser()->getId();
        $learnCourses = $this->getCourseService()->findLearnCoursesByUserId($userId);
        $threads = array();

        if (!empty($learnCourses)) {
            $threads = $this->getThreadService()->searchThreads(
                array('courseIds' => ArrayToolkit::column($learnCourses, 'id')),
                array('latestPostTime' => 'DESC'),
                0, 5
            );
        }

        foreach ($threads as $key => $thread) {
            $threads[$key]['sticky'] = $thread['isStick'];
            $threads[$key]['nice'] = $thread['isElite'];
            $threads[$key]['lastPostTime'] = $thread['latestPostTime'];
            $threads[$key]['lastPostUserId'] = $thread['latestPostUserId'];
        }

        $userIds = array_merge(
            ArrayToolkit::column($threads, 'userId'),
            ArrayToolkit::column($threads, 'latestPostUserId')
        );
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render(
            'study-center/side-bar/discusses.html.twig',
            array(
            'threads' => $threads,
            'users' => $users,
            'postId' => $postId,
            )
        );
    }

    private function findTaskTopFive($courseIds, $tasks)
    {
        if (empty($courseIds)) {
            return array();
        }

        $taskList = array();
        if (!empty($courseIds)) {
            foreach ($tasks as $key => $task) {
                if (in_array($task['courseId'], $courseIds)) {
                    array_push($taskList, $task);
                }
                if (count($taskList) >= 5) {
                    break;
                }
            }
        }

        return $taskList;
    }

    private function findOrgCoursesAndFullSiteCourseIds($user, $task)
    {
        $orgs = $this->getOrgService()->findOrgsByPrefixOrgCodes($user['orgCodes']);
        $orgIds = ArrayToolkit::column($orgs, 'id');
        $courseIds = ArrayToolkit::column($task, 'courseId');
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);
        $courseSetIds = ArrayToolkit::column($courses, 'courseSetId');

        $courseSets = $this->getCourseSetService()->searchCourseSets(
            array('ids' => $courseSetIds, 'orgIds' => $orgIds),
            array('id' => 'ASC'),
            0, PHP_INT_MAX
        );
        $fullSiteOrg = $this->getOrgService()->getOrgByOrgCode(self::ROOT_ORG_CODE);
        $fullSiteCourseSets = $this->getCourseSetService()->searchCourseSets(
            array('ids' => $courseSetIds, 'orgId' => $fullSiteOrg['id']),
            array('id' => 'ASC'),
            0, PHP_INT_MAX
        );

        $courseSetIds = array_merge(ArrayToolkit::column($courseSets, 'id'), ArrayToolkit::column($fullSiteCourseSets, 'id'));

        if (empty($courseSetIds)) {
            return array();
        }

        $courses = $this->getCourseService()->searchCourses(
            array('courseSetIds' => $courseSetIds),
            array('id' => 'ASC'),
            0, PHP_INT_MAX
        );

        return ArrayToolkit::column($courses, 'id');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    protected function getMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }

    protected function getArticleService()
    {
        return $this->createService('Article:ArticleService');
    }

    protected function getThreadService()
    {
        return $this->createService('Course:ThreadService');
    }

    protected function getOrgService()
    {
        return $this->createService('Org:OrgService');
    }

    protected function getPostService()
    {
        return $this->createService('CorporateTrainingBundle:Post:PostService');
    }

    protected function getPostCourseService()
    {
        return $this->createService('CorporateTrainingBundle:PostCourse:PostCourseService');
    }

    protected function getActivityLearnLogService()
    {
        return $this->createService('Activity:ActivityLearnLogService');
    }

    protected function getGroupThreadService()
    {
        return $this->createService('Group:ThreadService');
    }

    /**
     * @return GroupService
     */
    protected function getGroupService()
    {
        return $this->getBiz()->service('Group:GroupService');
    }
}
