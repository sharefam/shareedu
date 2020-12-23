<?php

namespace CorporateTrainingBundle\Extensions\DataTag;

use Topxia\Service\Common\ServiceKernel;
use AppBundle\Extensions\DataTag\CourseBaseDataTag;
use AppBundle\Extensions\DataTag\DataTag;

class HomeDepartmentClassroomDataTag extends CourseBaseDataTag implements DataTag
{
    /**
     * 获取首页部门班级列表.
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
        );
        if (isset($arguments['categoryId'])) {
            $conditions['categoryId'] = $arguments['categoryId'];
        }
        $conditions['classroomIds'] = $this->getResourceVisibleScopeService()->findDepartmentVisibleResourceIdsByResourceTypeAndUserId('classroom', $this->getCurrentUser()->getId());

        $classrooms = $this->getClassroomService()->searchClassrooms(
            $conditions,
            array('recommended' => 'DESC', 'recommendedSeq' => 'ASC', 'recommendedTime' => 'DESC', 'createdTime' => 'DESC'),
            0,
            $arguments['count']
        );

        foreach ($classrooms as &$classroom) {
            $teacherIds = $this->getClassroomService()->findTeachers($classroom['id']);
            $teachers = $this->getUserService()->findUsersByIds($teacherIds);
            $classroom['users'] = $teachers;
        }

        return $classrooms;
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
