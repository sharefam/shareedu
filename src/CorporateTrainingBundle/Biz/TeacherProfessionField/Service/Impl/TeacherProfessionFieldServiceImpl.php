<?php

namespace CorporateTrainingBundle\Biz\TeacherProfessionField\Service\Impl;

use Biz\BaseService;
use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Biz\TeacherProfessionField\Service\TeacherProfessionFieldService;

class TeacherProfessionFieldServiceImpl extends BaseService implements TeacherProfessionFieldService
{
    public function createTeacherProfessionField($teacherProfessionField)
    {
        $this->validateFields($teacherProfessionField);

        if ($this->getTeacherProfessionFieldByName($teacherProfessionField['name'])) {
            throw $this->createServiceException('teacher_profession_field already exists');
        }

        $seq = $this->countTeacherProfessionFields(array());
        $teacherProfessionField['seq'] = ++$seq;
        $teacherProfessionField = $this->filterFields($teacherProfessionField);

        return $this->getTeacherProfessionFieldDao()->create($teacherProfessionField);
    }

    public function updateTeacherProfessionField($id, $fields)
    {
        $this->checkTeacherProfessionFieldExist($id);
        $fields = $this->filterFields($fields);

        return $this->getTeacherProfessionFieldDao()->update($id, $fields);
    }

    public function deleteTeacherProfessionField($id)
    {
        $this->checkTeacherProfessionFieldExist($id);
        $professionField = $this->getTeacherProfessionField($id);
        $result = $this->getTeacherProfessionFieldDao()->delete($id);

        if ($result) {
            $this->updateTeacherProfessionFieldSeq($professionField['seq']);
        }

        return $result;
    }

    public function getTeacherProfessionField($id)
    {
        return $this->getTeacherProfessionFieldDao()->get($id);
    }

    public function getTeacherProfessionFieldByName($name)
    {
        return $this->getTeacherProfessionFieldDao()->getByName($name);
    }

    public function findTeacherProfessionFieldsByIds($ids)
    {
        return $this->getTeacherProfessionFieldDao()->findByIds($ids);
    }

    public function findAllTeacherProfessionFields()
    {
        return $this->getTeacherProfessionFieldDao()->search(array(), array(), 0, PHP_INT_MAX);
    }

    public function searchTeacherProfessionFields($conditions, $orderBy, $start, $limit)
    {
        return $this->getTeacherProfessionFieldDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function countTeacherProfessionFields($conditions)
    {
        return $this->getTeacherProfessionFieldDao()->count($conditions);
    }

    public function isTeacherProfessionFieldNameAvailable($name, $exclude)
    {
        if (empty($name)) {
            return false;
        }

        if ($name == $exclude) {
            return true;
        }

        $teacherProfessionField = $this->getTeacherProfessionFieldByName($name);

        return $teacherProfessionField ? false : true;
    }

    protected function updateTeacherProfessionFieldSeq($seq)
    {
        $teacherProfessionFields = $this->searchTeacherProfessionFields(array('seq_GT' => $seq), array(), 0, PHP_INT_MAX);

        foreach ($teacherProfessionFields as $teacherProfessionField) {
            $this->updateTeacherProfessionField($teacherProfessionField['id'], array('seq' => --$teacherProfessionField['seq']));
        }
    }

    protected function checkTeacherProfessionFieldExist($id)
    {
        $teacherProfessionField = $this->getTeacherProfessionField($id);

        if (empty($teacherProfessionField)) {
            throw $this->createNotFoundException("TeacherProfessionField#{$id} Not Exist");
        }
    }

    private function validateFields($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('name'), true)) {
            throw $this->createInvalidArgumentException('缺少必要字段，创建专项领域失败！');
        }
    }

    private function filterFields($fields)
    {
        return ArrayToolkit::parts($fields, array('name', 'seq'));
    }

    protected function getTeacherProfessionFieldDao()
    {
        return $this->createDao('CorporateTrainingBundle:TeacherProfessionField:TeacherProfessionFieldDao');
    }
}
