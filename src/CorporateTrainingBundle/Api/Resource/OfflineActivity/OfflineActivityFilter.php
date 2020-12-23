<?php

namespace CorporateTrainingBundle\Api\Resource\OfflineActivity;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Util\AssetHelper;

class OfflineActivityFilter extends Filter
{
    protected $simpleFields = array(
        'id',
        'title',
        'summary',
        'startDate',
        'endDate',
        'address',
        'status',
        'studentNum',
        'maxStudentNum',
        'cover',
        'activityTimeStatus',
        'applyStatus',
        'categoryName',
        'enrollmentEndDate',
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
        $cover['small'] = AssetHelper::getFurl(empty($cover['small']) ? '' : $cover['small'], 'activity.png');
        $cover['middle'] = AssetHelper::getFurl(empty($cover['middle']) ? '' : $cover['middle'], 'activity.png');
        $cover['large'] = AssetHelper::getFurl(empty($cover['large']) ? '' : $cover['large'], 'activity.png');
    }
}
