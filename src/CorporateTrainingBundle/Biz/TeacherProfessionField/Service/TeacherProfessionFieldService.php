<?php

namespace CorporateTrainingBundle\Biz\TeacherProfessionField\Service;

interface TeacherProfessionFieldService
{
    public function createTeacherProfessionField($teacherProfessionField);

    public function updateTeacherProfessionField($id, $fields);

    public function deleteTeacherProfessionField($id);

    public function getTeacherProfessionField($id);

    public function getTeacherProfessionFieldByName($name);

    public function findTeacherProfessionFieldsByIds($ids);

    public function searchTeacherProfessionFields($conditions, $orderBy, $start, $limit);

    public function countTeacherProfessionFields($conditions);
}
