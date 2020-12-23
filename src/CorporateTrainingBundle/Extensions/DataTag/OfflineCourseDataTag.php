<?php

namespace CorporateTrainingBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\CourseBaseDataTag;
use AppBundle\Extensions\DataTag\DataTag;

class OfflineCourseDataTag extends CourseBaseDataTag implements DataTag
{
    public function getData(array $arguments)
    {
        $offlineCourse = $this->getOfflineCourseService()->getOfflineCourse($arguments['id']);
        $courseItems = $this->getOfflineCourseService()->findOfflineCourseItems($arguments['id']);

        $offlineCourseItems = array('info' => $offlineCourse, 'items' => $courseItems);

        return $offlineCourseItems;
    }

    protected function getOfflineCourseService()
    {
        return $this->getServiceKernel()->createService('CorporateTrainingBundle:OfflineCourse:OfflineCourseService');
    }
}
