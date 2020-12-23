<?php

namespace CorporateTrainingBundle\Biz\Classroom\Service;

use Biz\Classroom\Service\ClassroomService as BaseService;

interface ClassroomService extends BaseService
{
    public function initOrgsRelation();

    public function canManageClassroom($id, $permission = 'admin_classroom_content_manage');
}
