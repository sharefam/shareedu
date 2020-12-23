<?php

namespace CorporateTrainingBundle\Extensions\DataTag\Tests;

use Biz\BaseTestCase;
use CorporateTrainingBundle\Extensions\DataTag\RecommendPublicClassroomsDataTag;

class RecommendPublicClassroomsDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $classroom1 = $this->getClassroomService()->addClassroom(array('title' => 'classroom1', 'showable' => 1, 'private' => 0));
        $classroom2 = $this->getClassroomService()->addClassroom(array('title' => 'classroom2', 'showable' => 1, 'private' => 0));
        $classroom3 = $this->getClassroomService()->addClassroom(array('title' => 'classroom3', 'showable' => 1, 'private' => 0));
        $this->getClassroomService()->addClassroom(array('title' => 'classroom4', 'showable' => 1, 'private' => 0));

        $this->getClassroomService()->publishClassroom($classroom1['id']);
        $this->getClassroomService()->publishClassroom($classroom2['id']);
        $this->getClassroomService()->publishClassroom($classroom3['id']);

        $this->getClassroomService()->recommendClassroom($classroom1['id'], 11);
        $this->getClassroomService()->recommendClassroom($classroom2['id'], 12);

        $datatag = new RecommendPublicClassroomsDataTag();
        $classrooms = $datatag->getData(array('count' => 5, 'orgCode' => '1.', 'orgIds' => array(1)));
        $this->assertEquals(3, count($classrooms));
        $this->assertEquals('1', $classrooms[0]['recommended']);

        $classrooms = $datatag->getData(array('count' => 5, 'orgCode' => '', 'orgIds' => array(0)));
        $this->assertEquals(0, count($classrooms));
    }

    public function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:ClassroomService');
    }
}
