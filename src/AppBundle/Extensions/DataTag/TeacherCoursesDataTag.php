<?php

namespace AppBundle\Extensions\DataTag;

class TeacherCoursesDataTag extends CourseBaseDataTag implements DataTag
{
    /**
     * 获取特定讲师的课程列表.
     *
     * @todo  逻辑有问题，应该是取讲师所在的所有课程，而不是创建者创建的所有课程
     *
     * 可传入的参数：
     *   userId   必需 讲师ID
     *   count    必需 课程数量，取值不能超过100
     *
     * @param array $arguments 参数
     *
     * @return array 课程列表
     */
    public function getData(array $arguments)
    {
        $this->checkCount($arguments);
        $this->checkUserId($arguments);

        $conditions = array(
            'status' => 'published',
            'userId' => $arguments['userId'],
        );
        $courses = $this->getCourseService()->searchCourses($conditions, 'latest', 0, $arguments['count']);

        return $this->getCourseTeachersAndCategories($courses);
    }
}
