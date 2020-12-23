<?php

namespace CorporateTrainingBundle\Extensions\DataTag\Tests;

use Biz\BaseTestCase;
use CorporateTrainingBundle\Extensions\DataTag\LatestCourseSetsDataTag;

class LatestCourseSetsDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $group = $this->getCategoryService()->addGroup(array('code' => 'course', 'name' => '课程分类', 'depth' => 2));

        $course1 = array(
            'title' => 'online test course 1',
            'orgCode' => '1.',
            'courseSetId' => 1,
            'learnMode' => 'freeMode',
            'expiryMode' => 'forever',
            'type' => 'normal',
            'expiryEndDate' => 321423423,
            'expiryStartDate' => 543543534,
            'expiryDays' => 34,
        );
        $course2 = array(
            'title' => 'online test course 1',
            'orgCode' => '1.',
            'courseSetId' => 1,
            'learnMode' => 'freeMode',
            'expiryMode' => 'forever',
            'type' => 'normal',
            'expiryEndDate' => 321423423,
            'expiryStartDate' => 543543534,
            'expiryDays' => 34,
        );

        $course3 = array(
            'title' => 'online test course 1',
            'orgCode' => '1.',
            'courseSetId' => 1,
            'learnMode' => 'freeMode',
            'expiryMode' => 'forever',
            'type' => 'normal',
            'expiryEndDate' => 321423423,
            'expiryStartDate' => 543543534,
            'expiryDays' => 34,
        );

        $category1 = $this->getCategoryService()->createCategory(array(
            'name' => 'category 1',
            'code' => 'c1',
            'weight' => 1,
            'groupId' => $group['id'],
        ));

        $this->getCategoryService()->createCategory(array(
            'name' => 'category 2',
            'code' => 'c2',
            'weight' => 1,
            'parentId' => $category1['id'],
            'groupId' => $group['id'],
        ));

        $course1 = $this->getCourseSetService()->createCourseSet($course1);
        $course2 = $this->getCourseSetService()->createCourseSet($course2);
        $course3 = $this->getCourseSetService()->createCourseSet($course3);

        $this->getCourseSetService()->publishCourseSet($course1['id']);
        $this->getCourseSetService()->publishCourseSet($course2['id']);
        $this->getCourseSetService()->publishCourseSet($course3['id']);

        $this->getCourseSetService()->updateCourseSet($course1['id'], array('categoryId' => $category1['id'], 'courseSetId' => 1, 'title' => 6666, 'serializeMode' => 'none', 'tags' => ''));
        $this->getCourseSetService()->updateCourseSet($course2['id'], array('categoryId' => $category1['id'], 'courseSetId' => 1, 'title' => 6666, 'serializeMode' => 'none', 'tags' => ''));
        $this->getCourseSetService()->updateCourseSet($course3['id'], array('categoryId' => $category1['id'], 'courseSetId' => 1, 'title' => 6666,  'serializeMode' => 'none', 'tags' => ''));

        $datatag = new LatestCourseSetsDataTag();
        $courses1 = $datatag->getData(array('count' => 5));
        $this->assertEquals(3, count($courses1));
        $courses2 = $datatag->getData(array('count' => 5, 'categoryId' => $category1['id']));
        $this->assertEquals(3, count($courses2));
    }

    public function getCourseSetService()
    {
        return $this->getServiceKernel()->getBiz()->service('CorporateTrainingBundle:Course:CourseSetService');
    }

    public function getCategoryService()
    {
        return $this->getServiceKernel()->getBiz()->service('Taxonomy:CategoryService');
    }
}
