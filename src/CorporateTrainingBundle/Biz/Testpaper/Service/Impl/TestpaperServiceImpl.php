<?php

namespace CorporateTrainingBundle\Biz\Testpaper\Service\Impl;

use CorporateTrainingBundle\Biz\Testpaper\Service\TestpaperService;
use Biz\Testpaper\Service\Impl\TestpaperServiceImpl as BaseServiceImpl;

class TestpaperServiceImpl extends BaseServiceImpl implements TestpaperService
{
    public function SumPaperResultsStatusNumByCourseIdsAndType(array $courseIds, $type)
    {
        $statusInfo = array();
        foreach ($courseIds as $courseId) {
            $results = $this->SumPaperResultsStatusNumByCourseIdAndType($courseId, $type);

            foreach ($results as $status => $result) {
                if (!isset($statusInfo[$status])) {
                    $statusInfo[$status] = 0;
                }
                $statusInfo[$status] += $result;
            }
        }

        return $statusInfo;
    }
}
