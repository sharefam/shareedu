<?php

namespace AppBundle\Extensions\DataTag;

use Biz\Course\Service\CourseService;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\ExtensionManager;
use CorporateTrainingBundle\Biz\Classroom\Service\ClassroomService;

class LatestStatusesDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取所有用户的最新动态
     *
     * 可传入的参数：
     *   mode     必需 动态的模式(simple, full)
     *   count    必需 获取动态数量
     *   objectType 可选 动态所属对象类型
     *   objectId   可选 动态所属对象编号
     *
     * @param array $arguments 参数
     *
     * @return array 用户列表
     */
    public function getData(array $arguments)
    {
        $conditions = array();
        if (isset($arguments['private']) && 0 == $arguments['private']) {
            $conditions['private'] = 0;
        }

        if (isset($arguments['objectType']) && isset($arguments['objectId'])) {
            if ('course' == $arguments['objectType']) {
                $conditions['courseIds'] = array($arguments['objectId']);
            } elseif ('courseSet' == $arguments['objectType']) {
                $courses = $this->getCourseService()->findCoursesByCourseSetId($arguments['objectId']);
                $conditions['courseIds'] = ArrayToolkit::column($courses, 'id');
            } else {
                $classroomCourses = $this->getClassroomService()->findClassroomCoursesByClassroomId($arguments['objectId']);
                if ($classroomCourses) {
                    $conditions['classroomCourseIds'] = empty($classroomCourses) ? array(-1) : ArrayToolkit::column($classroomCourses, 'courseId');
                    $conditions['classroomId'] = $arguments['objectId'];
                } else {
                    $conditions['onlyClassroomId'] = $arguments['objectId'];
                }
            }
        }

        $statuses = $this->getStatusService()->searchStatuses($conditions, array('createdTime' => 'DESC'), 0, $arguments['count']);

        if (empty($statuses)) {
            return array();
        }

        $userIds = ArrayToolkit::column($statuses, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);

        $manager = ExtensionManager::instance();

        foreach ($statuses as &$status) {
            $status['user'] = $users[$status['userId']];
            $status['message'] = $manager->renderStatus($status, $arguments['mode']);
            unset($status);
        }

        return $statuses;
    }

    protected function getStatusService()
    {
        return $this->getServiceKernel()->getBiz()->service('User:StatusService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->getBiz()->service('User:UserService');
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->getServiceKernel()->getBiz()->service('Classroom:ClassroomService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course:CourseService');
    }
}
