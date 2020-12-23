<?php

namespace ApiBundle\Api\Resource\App;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\Service\CourseSetService;
use Biz\DiscoveryColumn\Service\DiscoveryColumnService;

class AppChannel extends AbstractResource
{
    const DEFAULT_DISPLAY_COUNT = 6;

    public function search(ApiRequest $request)
    {
        $channel = $this->getDiscoveryColumnService()->getDisplayData();

        foreach ($channel as $key => $data) {
            if (empty($data['isDisplay'])) {
                unset($channel[$key]);
            }
        }
        $channel = array_values($channel);
        if (!$channel) {
            return $this->getDefaultChannel();
        }

        return $channel;
    }

    private function getDefaultChannel()
    {
        $user = $this->service('User:UserService')->getUser($this->getCurrentUser()->getId());
        $rootOrg = $this->getOrgService()->getOrgByOrgCode('1.');
        $orgIds = array_merge($user['orgIds'], array($rootOrg['id']));

        $conditions = array(
            'status' => 'published',
            'showable' => 1,
            'type' => 'normal',
            'orgIds' => $orgIds,
        );
        $latestCourseSets = $courseSets = $this->getCourseSetService()->searchCourseSets(
            $conditions,
            array('createdTime' => 'DESC'),
            0,
            self::DEFAULT_DISPLAY_COUNT
        );

        $defaultChannel = array(
            array(
                'title' => '最新课程',
                'type' => 'course',
                'categoryId' => 0,
                'orderType' => 'new',
                'showCount' => self::DEFAULT_DISPLAY_COUNT,
                'data' => $latestCourseSets,
                'actualCount' => self::DEFAULT_DISPLAY_COUNT,
            ),
        );

        return $defaultChannel;
    }

    /**
     * @return CourseSetService
     */
    private function getCourseSetService()
    {
        return $this->service('Course:CourseSetService');
    }

    /**
     * @return DiscoveryColumnService
     */
    private function getDiscoveryColumnService()
    {
        return $this->service('DiscoveryColumn:DiscoveryColumnService');
    }

    private function getOrgService()
    {
        return $this->service('Org:OrgService');
    }
}
