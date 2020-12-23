<?php

namespace CorporateTrainingBundle\Extensions\DataTag;

use AppBundle\Common\ArrayToolkit;
use Biz\Taxonomy\Service\TagService;
use AppBundle\Extensions\DataTag\DataTag;
use AppBundle\Extensions\DataTag\CourseBaseDataTag;

class RootOrgRelatedCourseSetsDataTag extends CourseBaseDataTag implements DataTag
{
    const ROOT_ORG_CODE = '1.';

    public function getData(array $arguments)
    {
        $courseSetId = $arguments['courseSetId'];
        $count = $arguments['count'];

        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);

        if (empty($courseSet)) {
            return array();
        }

        $courseSetTags = $this->getTagService()->findTagOwnerRelationsByTagIdsAndOwnerType($courseSet['tags'], 'course-set');

        $courseSetTags = array_filter($courseSetTags, function ($value) use ($courseSetId) {
            return $value['ownerId'] != $courseSetId;
        });
        if (empty($courseSetTags)) {
            return array();
        }

        //按标签相关度排序
        $courseSetIds = array();
        foreach ($courseSetTags as $tag) {
            if (empty($courseSetIds[$tag['ownerId']])) {
                $courseSetIds[$tag['ownerId']] = 1;
            } else {
                $courseSetIds[$tag['ownerId']] += 1;
            }
        }
        arsort($courseSetIds);

        $courseSetIds = array_keys($courseSetIds);

        $courseSets = $this->getCourseSetService()->searchCourseSets(array('ids' => $courseSetIds, 'status' => 'published', 'orgCode' => self::ROOT_ORG_CODE), array(), 0, PHP_INT_MAX);

        $courseSets = ArrayToolkit::index($courseSets, 'id');
        uksort($courseSets, function ($c1, $c2) use ($courseSetIds) {
            return array_search($c1, $courseSetIds) > array_search($c2, $courseSetIds);
        });

        $courseSets = array_values($courseSets);

        if (count($courseSets) > $count) {
            return array_slice($courseSets, 0, $count);
        }

        return $courseSets;
    }

    /**
     * @return TagService
     */
    protected function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy:TagService');
    }
}
