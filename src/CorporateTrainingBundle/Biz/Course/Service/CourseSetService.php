<?php

namespace CorporateTrainingBundle\Biz\Course\Service;

use Biz\Course\Service\CourseSetService as BaseService;

interface CourseSetService extends BaseService
{
    public function initOrgsRelation();

    public function findCourseSetsByCategoryId($categoryId);
}
