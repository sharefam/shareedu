<?php

namespace AppBundle\Component\Export\Course;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Exception\AccessDeniedException;
use AppBundle\Component\Export\Exporter;
use CorporateTrainingBundle\Biz\Org\Service\Impl\OrgServiceImpl;
use CorporateTrainingBundle\Common\OrgToolkit;

class StudentExporter extends Exporter
{
    public function canExport()
    {
        $user = $this->getUser();
        if ($user->isAdmin()) {
            return true;
        }

        $courseSetting = $this->getSettingService()->get('course', array());
        if (empty($courseSetting['teacher_export_student']) && !$user->hasPermission('admin_course_manage')) {
            throw new AccessDeniedException();
        }

        return true;
    }

    public function getCount()
    {
        return $this->getCourseMemberService()->countMembers($this->conditions);
    }

    public function getTitles()
    {
        $userFields = $this->getUserFieldService()->getEnabledFieldsOrderBySeq();
        $userFieldsTitle = empty($userFields) ? array() : ArrayToolkit::column($userFields, 'title');
        $fields = array(
            'user.fields.truename_label',
            'user.fields.username_label',
            'student.profile.department',
            'student.profile.post',
            'task.learn_data_detail.createdTime',
            'course.plan_task.study_rate',
        );

        return array_merge($fields, $userFieldsTitle);
    }

    public function getContent($start, $limit)
    {
        $course = $this->getCourseService()->getCourse($this->parameter['courseId']);
        $translator = $this->container->get('translator');

        $courseMembers = $this->getCourseMemberService()->searchMembers(
            $this->conditions,
            array('createdTime' => 'DESC'),
            $start,
            $limit
        );

        $studentUserIds = ArrayToolkit::column($courseMembers, 'userId');
        list($users, $profiles, $orgs, $posts) = $this->buildUsersData($studentUserIds);

        foreach ($courseMembers as $key => $member) {
            $progress = $this->getLearningDataAnalysisService()->makeProgress($member['learnedCompulsoryTaskNum'], $course['compulsoryTaskNum']);
            $courseMembers[$key]['learningProgressPercent'] = $progress['percent'];
        }

        $fields = $this->getUserFieldService()->getEnabledFieldsOrderBySeq();
        $fields = ArrayToolkit::column($fields, 'fieldName');

        $datas = array();
        foreach ($courseMembers as $courseMember) {
            $member = array();
            $member[] = $profiles[$courseMember['userId']]['truename'] ? $profiles[$courseMember['userId']]['truename'] : '-';
            $member[] = $users[$courseMember['userId']]['nickname'];
            $member[] = OrgToolkit::buildOrgsNames($users[$courseMember['userId']]['orgIds'], $orgs);
            $member[] = empty($posts[$users[$courseMember['userId']]['postId']]) ? '-' : $posts[$users[$courseMember['userId']]['postId']]['name'];
            $member[] = date('Y-n-d H:i:s', $courseMember['createdTime']);
            $member[] = $courseMember['learningProgressPercent'].'%';
            foreach ($fields as $value) {
                $member[] = $profiles[$courseMember['userId']][$value] ? $profiles[$courseMember['userId']][$value] : '-';
            }

            $datas[] = $member;
        }

        return $datas;
    }

    protected function buildUsersData($userIds)
    {
        $userProfiles = $this->getUserService()->findUserProfilesByIds($userIds);
        $userProfiles = ArrayToolkit::index($userProfiles, 'id');

        $users = $this->getUserService()->findUsersByIds($userIds);
        $users = ArrayToolkit::index($users, 'id');

        $orgIds = array();

        foreach ($users as $user) {
            $orgIds = array_merge($orgIds, $user['orgIds']);
        }
        $orgIds = array_values(array_unique($orgIds));
        $orgs = $this->getOrgService()->findOrgsByIds($orgIds);
        $orgs = ArrayToolkit::index($orgs, 'id');

        $posts = $this->getPostService()->findPostsByIds(ArrayToolkit::column($users, 'postId'));
        $posts = ArrayToolkit::index($posts, 'id');

        return array($users, $userProfiles, $orgs, $posts);
    }

    public function buildParameter($conditions)
    {
        $parameter = parent::buildParameter($conditions);
        $parameter['courseId'] = $conditions['courseId'];
        $parameter['courseSetId'] = $conditions['courseSetId'];

        return $parameter;
    }

    public function buildCondition($conditions)
    {
        return array(
            'courseId' => $conditions['courseId'],
            'role' => 'student',
        );
    }

    /**
     * @return \CorporateTrainingBundle\Biz\Post\Service\Impl\PostServiceImpl
     */
    protected function getPostService()
    {
        return $this->getBiz()->service('CorporateTrainingBundle:Post:PostService');
    }

    /**
     * @return OrgServiceImpl
     */
    protected function getOrgService()
    {
        return $this->getBiz()->service('Org:OrgService');
    }

    /**
     * @return LearningDataAnalysisService
     */
    protected function getLearningDataAnalysisService()
    {
        return $this->getBiz()->service('Course:LearningDataAnalysisService');
    }

    /**
     * @return UserFieldService
     */
    protected function getUserFieldService()
    {
        return $this->getBiz()->service('User:UserFieldService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    protected function getCourseMemberService()
    {
        return $this->getBiz()->service('Course:MemberService');
    }

    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }
}
