<?php

namespace CorporateTrainingBundle\Controller\StudyCenter;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\BaseController;
use CorporateTrainingBundle\Common\OrgToolkit;
use Topxia\Service\Common\ServiceKernel;

class DepartmentManageController extends BaseController
{
    public function indexAction(Request $request)
    {
        $fields = $request->query->all();
        $conditions = $this->getConditions($fields);

        if (!empty($conditions['error'])) {
            return $this->createMessageResponse('error', $conditions['error']);
        }

        $count = $this->getUserService()->countUsers($conditions);
        $paginator = new Paginator($request, $count, 20);
        $users = $this->getUserService()->searchUsers(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $allPosts = $this->getPostService()->getAllPosts();

        if (!empty($users)) {
            $userIds = ArrayToolkit::column($users, 'id');
            $userProfiles = $this->getUserService()->findUserProfilesByIds($userIds);
            $userProfiles = ArrayToolkit::index($userProfiles, 'id');

            foreach ($users as &$user) {
                $user['learnTime'] = 0;
                if (!empty($userProfiles[$user['id']])) {
                    $user['truename'] = $userProfiles[$user['id']]['truename'];
                }

                $postInfo = $this->getPostService()->getPost($user['postId']);
                $user['post'] = $postInfo;

                $postCourses = $this->getPostCourseService()->findPostCoursesByPostId($user['postId']);

                $user['taskCount'] = count($postCourses);

                if (empty($postCourses)) {
                    $user['finishedTaskCount'] = 0;
                } else {
                    $courseIds = ArrayToolkit::column($postCourses, 'courseId');
                    $user['finishedTaskCount'] = $this->getPostCourseService()->countUserLearnedPostCourses($user['id'], $courseIds);

                    $user['learnTime'] = $this->getTaskResultService()->sumLearnTimeByPostIdAndUserId($user['postId'], $user['id']);
                }

                $user['totalLearnTime'] = $this->getTaskResultService()->sumLearnTimeByUserId($user['id']);

                $courseMemberCount = $this->getMemberService()->countMembers(array('userId' => $user['id']));
                $user['courseCount'] = $courseMemberCount;

                $org = $this->getOrgService()->getOrgByOrgCode($user['orgCode']);
                $user['org'] = $org;
            }
        }

        return $this->render(
            'study-center/department-manage/index.html.twig',
            array(
            'posts' => $allPosts,
            'users' => $users,
            'count' => $count,
            'paginator' => $paginator,
            )
        );
    }

    public function learnRecordHeaderAction(Request $request, $userId)
    {
        $user = $this->getUserService()->getUser($userId);
        $user['profile'] = $this->getUserService()->getUserProfile($userId);
        $org = $this->getOrgService()->getOrg($user['orgId']);
        $post = $this->getPostService()->getPost($user['postId']);

        $user['learnTime'] = $this->getTaskResultService()->sumLearnTimeByPostIdAndUserId($user['postId'], $user['id']);
        $user['watchTime'] = $this->getTaskResultService()->sumWatchTimeByPostIdAndUserId($user['postId'], $user['id']);

        return $this->render(
            'study-center/department-manage/user-study-record-header.html.twig',
            array(
            'user' => $user,
            'org' => $org,
            'post' => $post,
            )
        );
    }

    public function learnRecordAction(Request $request, $userId)
    {
        $user = $this->getUserService()->getUser($userId);
        $user['profile'] = $this->getUserService()->getUserProfile($userId);
        $postCourses = $this->getPostCourseService()->findPostCoursesByPostId($user['postId']);
        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($postCourses, 'courseId'));
        foreach ($courses as &$course) {
            $course['finishedTaskNum'] = $this->getTaskResultService()->countTaskResults(array(
                'courseId' => $course['id'],
                'userId' => $user['id'],
                'status' => 'finish',
            ));

            $course['learnTime'] = $this->getTaskResultService()->sumLearnTimeByCourseIdAndUserId(
                $course['id'],
                $user['id']
            );
        }

        return $this->render(
            'study-center/department-manage/user-study-record.html.twig',
            array(
            'courses' => $courses,
            'user' => $user,
            )
        );
    }

    public function userShowAction($userId)
    {
        $user = $this->getUserService()->getUser($userId);
        $profile = $this->getUserService()->getUserProfile($userId);
        $profile['title'] = $user['title'];
        $userGroups = $this->getUserGroupMemberService()->findUserGroupsByUserId($userId);

        $fields = $this->getFields();
        $orgs = $this->getOrgService()->findOrgsByIds($user['orgIds']);
        $orgNames = OrgToolkit::buildOrgsNames($user['orgIds'], $orgs);

        return $this->render(
            'study-center/department-manage/user-show-modal.html.twig',
            array(
            'user' => $user,
            'profile' => $profile,
            'fields' => $fields,
            'orgNames' => $orgNames,
            'userGroups' => $userGroups,
            )
        );
    }

    private function getConditions($fields)
    {
        $conditions = array();
        $currentUser = $this->getCurrentUser();

        if (!empty($fields['orgCode'])) {
            if (substr($fields['orgCode'], 0, strlen($currentUser['orgCode'])) != $currentUser['orgCode']) {
                $conditions['error'] = ServiceKernel::instance()->trans('study_center.department_courses.message.no_permission_message');

                return $conditions;
            } else {
                $conditions['likeOrgCode'] = $fields['orgCode'];
            }
        } else {
            $conditions['likeOrgCode'] = $currentUser['orgCode'];
        }

        if (!empty($fields['postId'])) {
            $conditions['postId'] = $fields['postId'];
        }

        if (!empty($fields['keywordType']) && !empty($fields['keyword'])) {
            $conditions[$fields['keywordType']] = $fields['keyword'];
        }

        return $conditions;
    }

    protected function getFields()
    {
        $fields = $this->getUserFieldService()->getEnabledFieldsOrderBySeq();

        for ($i = 0; $i < count($fields); ++$i) {
            if (strstr($fields[$i]['fieldName'], 'textField')) {
                $fields[$i]['type'] = 'text';
            }

            if (strstr($fields[$i]['fieldName'], 'varcharField')) {
                $fields[$i]['type'] = 'varchar';
            }

            if (strstr($fields[$i]['fieldName'], 'intField')) {
                $fields[$i]['type'] = 'int';
            }

            if (strstr($fields[$i]['fieldName'], 'floatField')) {
                $fields[$i]['type'] = 'float';
            }

            if (strstr($fields[$i]['fieldName'], 'dateField')) {
                $fields[$i]['type'] = 'date';
            }
        }

        return $fields;
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getPostService()
    {
        return $this->createService('CorporateTrainingBundle:Post:PostService');
    }

    protected function getPostCourseService()
    {
        return $this->createService('CorporateTrainingBundle:PostCourse:PostCourseService');
    }

    protected function getOrgService()
    {
        return $this->createService('Org:OrgService');
    }

    protected function getUserFieldService()
    {
        return $this->createService('User:UserFieldService');
    }

    protected function getActivityLearnLogService()
    {
        return $this->createService('Activity:ActivityLearnLogService');
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

    protected function getUserGroupMemberService()
    {
        return $this->createService('CorporateTrainingBundle:UserGroup:MemberService');
    }
}
