<?php

namespace CorporateTrainingBundle\Biz\Testpaper\Service;

interface TestpaperService
{
    public function SumPaperResultsStatusNumByCourseIdsAndType(array $courseIds, $type);
}
