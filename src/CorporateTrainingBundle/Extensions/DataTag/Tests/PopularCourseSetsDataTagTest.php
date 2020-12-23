<?php

namespace CorporateTrainingBundle\Extensions\DataTag\Tests;

use Biz\BaseTestCase;
use CorporateTrainingBundle\Extensions\DataTag\PopularCourseSetsDataTag;

class PopularCourseSetsDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $group = $this->getCategoryService()->addGroup(array('code' => 'course', 'name' => '课程分类', 'depth' => 2));
        $category1 = $this->getCategoryService()->createCategory(array(
            'name' => 'category 1',
            'code' => 'c1',
            'weight' => 1,
            'groupId' => 1,
        ));
        $user1 = $this->getUserService()->register(array(
            'email' => '1234@qq.com',
            'nickname' => 'user1',
            'password' => '123456',
            'confirmPassword' => '123456',
            'createdIp' => '127.0.0.1',
        ));
        $user2 = $this->getUserService()->register(array(
            'email' => '12345@qq.com',
            'nickname' => 'user2',
            'password' => '123456',
            'confirmPassword' => '123456',
            'createdIp' => '127.0.0.1',
        ));
        $user3 = $this->getUserService()->register(array(
            'email' => '123456@qq.com',
            'nickname' => 'user3',
            'password' => '123456',
            'confirmPassword' => '123456',
            'createdIp' => '127.0.0.1',
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
        $this->getCourseSetService()->updateCourseSet($course2['id'], array('categoryId' => $category1['id'], 'courseSetId' => 1, 'title' => 6666, 'serializeMode' => 'none', 'tags' => ''));
        $this->getCourseSetService()->updateCourseSet($course3['id'], array('categoryId' => $category1['id'], 'courseSetId' => 1, 'title' => 6666, 'serializeMode' => 'none', 'tags' => ''));

        $this->getCourseMemberService()->becomeStudent($course1['id'], $user1['id']);
        $this->getCourseMemberService()->becomeStudent($course1['id'], $user2['id']);
        $this->getCourseMemberService()->becomeStudent($course2['id'], $user3['id']);

        $datatag = new PopularCourseSetsDataTag();
        $courses = $datatag->getData(array('categoryId' => 1, 'count' => 5, 'type' => 'studentNum'));
        $this->assertEquals(3, count($courses));
        $this->assertEquals($course1['id'], $courses[0]['id']);
        $this->assertEquals($course2['id'], $courses[1]['id']);

        $courses = $datatag->getData(array('count' => 5));
        $this->assertEquals(3, count($courses));
    }

    public function getCourseSetService()
    {
        return $this->getServiceKernel()->createService('CorporateTrainingBundle:Course:CourseSetService');
    }

    public function getCourseMemberService()
    {
        return $this->getServiceKernel()->createService('Course:MemberService');
    }

    public function getUserService()
    {
        return $this->getServiceKernel()->createService('User:UserService');
    }

    public function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy:CategoryService');
    }
}
