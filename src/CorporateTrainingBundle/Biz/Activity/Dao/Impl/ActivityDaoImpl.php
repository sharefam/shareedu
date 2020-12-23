<?php

namespace CorporateTrainingBundle\Biz\Activity\Dao\Impl;

use Biz\Activity\Dao\Impl\ActivityDaoImpl as BaseDaoImpl;

class ActivityDaoImpl extends BaseDaoImpl
{
    public function getByMediaIdAndMediaType($mediaId, $mediaType)
    {
        return $this->getByFields(array('mediaId' => $mediaId, 'mediaType' => $mediaType));
    }

    public function declares()
    {
        $declares = parent::declares();
        array_push($declares['conditions'], 'mediaId IN (:mediaIds)');

        return $declares;
    }
}
