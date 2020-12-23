<?php

namespace CorporateTrainingBundle\Extensions\DataTag;

use Topxia\Service\Common\ServiceKernel;
use AppBundle\Extensions\DataTag\CourseBaseDataTag;
use AppBundle\Extensions\DataTag\DataTag;

class RecommendPublicClassroomsDataTag extends CourseBaseDataTag implements DataTag
{
    /**
     * 获取推荐班级列表.
     *
     * 可传入的参数：
     *   count    必需 班级数量，取值不能超过100
     *
     * @param array $arguments 参数
     *
     * @return array 班级推荐列表
     */
    public function getData(array $arguments)
    {
        $this->checkCount($arguments);

        $conditions = array(
            'status' => 'published',
            'showable' => 1,
            'recommended' => 1,
        );
        if (isset($arguments['orgCode'])) {
            $conditions['orgCode'] = $arguments['orgCode'];
        }
        if (isset($arguments['orgIds'])) {
            $conditions['orgIds'] = $this->getOrgIds($arguments['orgIds']);
        }
        if (isset($arguments['categoryId'])) {
            $conditions['categoryId'] = $arguments['categoryId'];
        }
        $conditions['classroomIds'] = $this->getResourceVisibleScopeService()->findPublicVisibleResourceIdsByResourceTypeAndUserId('classroom', $this->getCurrentUser()->getId());

        $classrooms = $this->getClassroomService()->searchClassrooms(
            $conditions,
            array('recommendedSeq' => 'ASC', 'recommendedTime' => 'DESC', 'createdTime' => 'DESC'),
            0,
            $arguments['count']
        );
        $classroomCount = count($classrooms);

        if ($classroomCount < $arguments['count']) {
            $conditions['recommended'] = 0;

            $classroomTemp = $this->getClassroomService()->searchClassrooms(
                $conditions,
                array('createdTime' => 'DESC'),
                0,
                $arguments['count'] - $classroomCount
            );
            $classrooms = array_merge($classrooms, $classroomTemp);
        }

        foreach ($classrooms as &$classroom) {
            $teacherIds = $this->getClassroomService()->findTeachers($classroom['id']);
            $teachers = $this->getUserService()->findUsersByIds($teacherIds);
            $classroom['users'] = $teachers;
        }

        return $classrooms;
    }

    protected function getOrgIds($orgIds)
    {
        if (count($orgIds) > 1 && in_array('1.', $orgIds)) {
            foreach ($orgIds as $key => $orgId) {
                if ('1.' == $orgId) {
                    unset($orgIds[$key]);
                    break;
                }
            }
        }

        return $orgIds;
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:ClassroomService');
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User:UserService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course:CourseService');
    }
}
