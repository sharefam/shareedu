<?php

namespace CorporateTrainingBundle\Api\Resource\OfflineActivity;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;

class OfflineActivityMemberFilter extends Filter
{
    protected $publicFields = array(
        'userId', 'offlineActivityId', 'user', 'result', 'postName',
    );

    protected function publicFields(&$data)
    {
        if (!empty($data['user'])) {
            $userFilter = new UserFilter();
            $userFilter->filter($data['user']);
        }
    }
}
