<?php

namespace CorporateTrainingBundle\Api\Resource\ProjectPlan;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Util\AssetHelper;

class ProjectPlanFilter extends Filter
{
    protected $simpleFields = array(
        'id',
        'name',
        'title',
        'summary',
        'startTime',
        'endTime',
        'enrollmentStartDate',
        'enrollmentEndDate',
        'status',
        'studentNum',
        'maxStudentNum',
        'cover',
        'currentState',
        'applyStatus',
        'categoryName',
        'itemsDetail',
    );

    protected function simpleFields(&$data)
    {
        if (!empty($data['summary'])) {
            $data['summary'] = $this->convertAbsoluteUrl($data['summary']);
        }
        $this->transformCover($data['cover']);
    }

    private function transformCover(&$cover)
    {
        $cover['small'] = AssetHelper::getFurl(empty($cover['small']) ? '' : $cover['small'], 'project-plan.png');
        $cover['middle'] = AssetHelper::getFurl(empty($cover['middle']) ? '' : $cover['middle'], 'project-plan.png');
        $cover['large'] = AssetHelper::getFurl(empty($cover['large']) ? '' : $cover['large'], 'project-plan.png');
    }
}
