<?php

namespace CorporateTrainingBundle\Extensions\DataTag;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Extensions\DataTag\DataTag;
use AppBundle\Extensions\DataTag\CourseBaseDataTag;

class FindOrgAndFullSiteLiveCourseDataTag extends CourseBaseDataTag implements DataTag
{
    //    找全站以及自己部门下正在直播的课程
    const  ROOT_ORG_CODE = '1.';

    public function getData(array $arguments)
    {
        $this->checkCount($arguments);
        $user = $this->getCurrentUser();

        $taskList = $this->getTaskService()->searchTasks(
            array('type' => 'live', 'endTime_GT' => time(), 'startTime_LE' => time()),
            array('startTime' => 'ASC'),
            0, PHP_INT_MAX
        );

        if (!empty($taskList)) {
            $courseIds = $this->findOrgCoursesAndFullSiteCourseIds($user, $taskList);
            $tasks = $this->findTask($courseIds, $taskList, $arguments['count']);
        }

        return empty($tasks) ? array() : $tasks;
    }

    private function findTask($courseIds, $tasks, $count)
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
                if (count($taskList) >= $count) {
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

    protected function getOrgService()
    {
        return $this->getServiceKernel()->getBiz()->service('Org:OrgService');
    }

    protected function getMemberService()
    {
        return $this->getServiceKernel()->getBiz()->service('Course:MemberService');
    }
}
