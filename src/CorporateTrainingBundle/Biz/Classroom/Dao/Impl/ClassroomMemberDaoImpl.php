<?php

namespace CorporateTrainingBundle\Biz\Classroom\Dao\Impl;

use Biz\Classroom\Dao\Impl\ClassroomMemberDaoImpl as BaseMemberDaoImpl;

class ClassroomMemberDaoImpl extends BaseMemberDaoImpl
{
    protected $table = 'classroom_member';

    public function findByClassroomId($classroomId)
    {
        return $this->findByFields(array('classroomId' => $classroomId));
    }

    public function declares()
    {
        return parent::declares();
    }
}
