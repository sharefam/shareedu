<?php

namespace CorporateTrainingBundle\Controller\Course;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\Course\StudentManageController as BaseStudentManageController;

class StudentManageController extends BaseStudentManageController
{
    public function studentsAction(Request $request, $courseSetId, $courseId)
    {
        $fields = $request->query->all();
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($courseSetId);
        $course = $this->getCourseService()->getCourse($courseId);
        $followings = $this->findCurrentUserFollowings();
        $conditions = $this->prepareConditions($fields);
        $conditions['courseId'] = $course['id'];
        $conditions['role'] = 'student';

        $paginator = new Paginator(
            $request,
            $this->getCourseMemberService()->countMembers($conditions),
            20
        );

        $members = $this->getCourseMemberService()->searchMembers(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $this->appendLearningProgress($members);

        $userIds = ArrayToolkit::column($members, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('course-manage/student/index.html.twig', array(
            'courseSet' => $courseSet,
            'course' => $course,
            'students' => $members,
            'followings' => $followings,
            'users' => $users,
            'paginator' => $paginator,
        ));
    }

    private function prepareConditions($fields)
    {
        $conditions = array();
        if (empty($fields['postId']) && empty($fields['keyword'])) {
            return $conditions;
        }

        if (!empty($fields['postId'])) {
            $conditions['postId'] = $fields['postId'];
        }

        if (!empty($fields['keyword'])) {
            $conditions['keyword'] = $fields['keyword'];
            $conditions['keywordType'] = $fields['keywordType'];
        }

        $users = $this->getUserService()->searchUsers(
            $conditions,
            array('id' => 'DESC'),
            0,
            PHP_INT_MAX
        );
        $userIds = ArrayToolkit::column($users, 'id');
        $conditions['userIds'] = empty($userIds) ? array(-1) : $userIds;

        return $conditions;
    }

    private function appendLearningProgress(&$members)
    {
        foreach ($members as &$member) {
            $progress = $this->getLearningDataAnalysisService()->getUserLearningProgress($member['courseId'], $member['userId']);
            $member['learningProgressPercent'] = $progress['percent'];
        }
    }

    /**
     * @return \CorporateTrainingBundle\Biz\User\Service\Impl\UserServiceImpl
     */
    protected function getUserService()
    {
        return $this->createService('CorporateTrainingBundle:User:UserService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\Post\Service\Impl\PostServiceImpl
     */
    protected function getPostService()
    {
        return $this->createService('CorporateTrainingBundle:Post:PostService');
    }
}
