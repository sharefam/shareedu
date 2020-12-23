<?php

namespace CorporateTrainingBundle\Extensions\DataTag\Tests;

use Biz\BaseTestCase;
use CorporateTrainingBundle\Extensions\DataTag\RecommendCourseSetsDataTag;

class RecommendCourseSetsDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $group = $this->getCategoryService()->addGroup(array('code' => 'course', 'name' => '课程分类', 'depth' => 2));
        $category1 = $this->getCategoryService()->createCategory(array(
            'name' => 'category 1',
            'code' => 'c1',
            'weight' => 1,
            'parentId' => 0,
            'groupId' => $group['id'],
        ));
        $category2 = $this->getCategoryService()->createCategory(array(
            'name' => 'category 2',
            'code' => 'c2',
            'weight' => 1,
            'parentId' => $category1['id'],
            'groupId' => $group['id'],
        ));
        $course1 = array(
            'type' => 'normal',
            'title' => 'course1',
            'expiryMode' => 'forever',
            'expiryEndDate' => 321423423,
            'expiryStartDate' => 543543534,
            'expiryDays' => 34,
            'learnMode' => 'freeMode',
        );
        $course2 = array(
            'type' => 'normal',
            'title' => 'course2',
            'expiryMode' => 'forever',
            'expiryEndDate' => 321423423,
            'expiryStartDate' => 543543534,
            'expiryDays' => 34,
            'learnMode' => 'freeMode',
        );
        $course3 = array(
            'type' => 'normal',
            'title' => 'course3',
            'expiryMode' => 'forever',
            'expiryEndDate' => 321423423,
            'expiryStartDate' => 543543534,
            'expiryDays' => 34,
            'learnMode' => 'freeMode',
        );

        $course1 = $this->getCourseSetService()->createCourseSet($course1);
        $course2 = $this->getCourseSetService()->createCourseSet($course2);
        $course3 = $this->getCourseSetService()->createCourseSet($course3);

        $this->getCourseSetService()->publishCourseSet($course1['id']);
        $this->getCourseSetService()->publishCourseSet($course2['id']);
        $this->getCourseSetService()->publishCourseSet($course3['id']);

        $this->getCourseSetService()->updateCourseSet($course1['id'], array('categoryId' => $category1['id'], 'courseSetId' => 1, 'title' => 6666, 'serializeMode' => 'none', 'tags' => ''));
        $this->getCourseSetService()->updateCourseSet($course2['id'], array('categoryId' => $category2['id'], 'courseSetId' => 1, 'title' => 6666, 'serializeMode' => 'none', 'tags' => ''));
        $this->getCourseSetService()->updateCourseSet($course3['id'], array('categoryId' => $category2['id'], 'courseSetId' => 1, 'title' => 6666, 'serializeMode' => 'none', 'tags' => ''));

        $this->getCourseSetService()->recommendCourse($course1['id'], 1);
        $this->getCourseSetService()->recommendCourse($course2['id'], 2);
        $this->getCourseSetService()->recommendCourse($course3['id'], 3);

        $datatag = new RecommendCourseSetsDataTag();
        $courses = $datatag->getData(array('count' => 5, 'type' => 'live'));
        $this->assertEquals(0, count($courses));
        $courses = $datatag->getData(array('count' => 5, 'type' => 'normal'));
        $this->assertEquals(3, count($courses));
        $courses = $datatag->getData(array('count' => 5, 'type' => 'live', 'categoryId' => $category2['id']));
        $this->assertEquals(0, count($courses));
        $courses = $datatag->getData(array('count' => 5, 'categoryCode' => $category1['code']));
        $this->assertEquals(3, count($courses));
    }

    public function getCourseSetService()
    {
        return $this->getServiceKernel()->createService('CorporateTrainingBundle:Course:CourseSetService');
    }

    public function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy:CategoryService');
    }
}
