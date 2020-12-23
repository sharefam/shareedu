<?php

namespace AppBundle\Extensions\DataTag;

use Biz\Classroom\Service\ClassroomService;

class ClassroomDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取一个班级.
     *
     * 可传入的参数：
     *   classroomId 班级ID
     *
     * @param array $arguments 参数
     *
     * @return array 班级
     */
    public function getData(array $arguments)
    {
        if (!empty($arguments['classroomId'])) {
            return $this->getClassroomService()->getClassroom($arguments['classroomId']);
        } else {
            throw new \InvalidArgumentException('classroomId required');
        }
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:ClassroomService');
    }
}
