<?php

namespace CorporateTrainingBundle\Extensions\DataTag;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Extensions\DataTag\DataTag;
use AppBundle\Extensions\DataTag\BaseDataTag;

class TeacherProfessionFieldsDataTag extends BaseDataTag implements DataTag
{
    public function getData(array $arguments)
    {
        if (!isset($arguments['teacherProfessionFieldIds'])) {
            throw new \InvalidArgumentException($this->getServiceKernel()->trans('TeacherProfessionFieldIds参数缺失'));
        }

        $teacherProfessionFields = $this->getTeacherProfessionFieldService()->findTeacherProfessionFieldsByIds($arguments['teacherProfessionFieldIds']);
        $teacherProfessionField = reset($teacherProfessionFields);

        $teacherProfessionFields['teacherProfessionFieldNames'] = $this->buildTeacherProfessionFieldNames($arguments['teacherProfessionFieldIds'], $teacherProfessionFields, $arguments['delimiter']);
        $teacherProfessionFields['teacherProfessionFieldName'] = empty($teacherProfessionField) ? '' : $teacherProfessionField['name'];

        return $teacherProfessionFields;
    }

    protected function buildTeacherProfessionFieldNames(array $teacherProfessionFieldIds, array $teacherProfessionFields, $delimiter = '/')
    {
        $professionsNames = '';

        if (empty($teacherProfessionFieldIds) || empty($teacherProfessionFields)) {
            return $professionsNames;
        }

        $teacherProfessionFields = ArrayToolkit::index($teacherProfessionFields, 'id');

        foreach ($teacherProfessionFieldIds as $professionId) {
            if (!empty($teacherProfessionFields[$professionId])) {
                $professionsNames = $professionsNames.$delimiter.$teacherProfessionFields[$professionId]['name'];
            }
        }

        return trim($professionsNames, $delimiter);
    }

    /**
     * @return \CorporateTrainingBundle\Biz\TeacherProfessionField\Service\Impl\TeacherProfessionFieldServiceImpl
     */
    protected function getTeacherProfessionFieldService()
    {
        return $this->getServiceKernel()->getBiz()->service('CorporateTrainingBundle:TeacherProfessionField:TeacherProfessionFieldService');
    }
}
