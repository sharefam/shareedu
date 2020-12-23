<?php

namespace Biz\Activity\Type;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Config\Activity;
use Biz\Activity\Dao\PptActivityDao;
use Biz\Activity\Service\ActivityService;
use Biz\CloudFile\Service\CloudFileService;
use Biz\File\Service\UploadFileService;

class Ppt extends Activity
{
    protected function registerListeners()
    {
    }

    public function isFinished($activityId)
    {
        $activity = $this->getActivityService()->getActivity($activityId);
        $ppt = $this->getPptActivityDao()->get($activity['mediaId']);

        if ('time' === $ppt['finishType']) {
            $result = $this->getTaskResultService()->getMyLearnedTimeByActivityId($activityId);
            $result /= 60;

            return !empty($result) && $result >= $ppt['finishDetail'];
        }

        if ('end' === $ppt['finishType']) {
            $log = $this->getActivityLearnLogService()->getMyRecentFinishLogByActivityId($activityId);

            return !empty($log);
        }

        return false;
    }

    public function create($fields)
    {
        if (empty($fields['media'])) {
            throw $this->createInvalidArgumentException('参数不正确');
        }
        $media = json_decode($fields['media'], true);

        if (empty($media['id'])) {
            throw $this->createInvalidArgumentException('参数不正确');
        }
        $fields['mediaId'] = $media['id'];

        $default = array(
            'finishDetail' => 1,
            'finishType' => 'end',
        );
        $fields = array_merge($default, $fields);

        $ppt = ArrayToolkit::parts($fields, array(
            'mediaId',
            'playMode',
            'finishType',
            'finishDetail',
        ));

        $biz = $this->getBiz();
        $ppt['createdUserId'] = $biz['user']['id'];
        $ppt['createdTime'] = time();

        $ppt = $this->getPptActivityDao()->create($ppt);

        return $ppt;
    }

    public function copy($activity, $config = array())
    {
        $biz = $this->getBiz();
        $ppt = $this->getPptActivityDao()->get($activity['mediaId']);
        $newPpt = array(
            'mediaId' => $ppt['mediaId'],
            'playMode' => $ppt['playMode'],
            'finishType' => $ppt['finishType'],
            'finishDetail' => $ppt['finishDetail'],
            'createdUserId' => $biz['user']['id'],
        );

        return $this->getPptActivityDao()->create($newPpt);
    }

    public function sync($sourceActivity, $activity)
    {
        $sourcePpt = $this->getPptActivityDao()->get($sourceActivity['mediaId']);
        $ppt = $this->getPptActivityDao()->get($activity['mediaId']);
        $ppt['mediaId'] = $sourcePpt['mediaId'];
        $ppt['playMode'] = $sourcePpt['playMode'];
        $ppt['finishType'] = $sourcePpt['finishType'];
        $ppt['finishDetail'] = $sourcePpt['finishDetail'];

        return $this->getPptActivityDao()->update($ppt['id'], $ppt);
    }

    public function update($targetId, &$fields, $activity)
    {
        $updateFields = ArrayToolkit::parts($fields, array(
            'mediaId',
            'playMode',
            'finishType',
            'finishDetail',
        ));

        $updateFields['updatedTime'] = time();

        $pptActivity = $this->getPptActivityDao()->update($targetId, $updateFields);

        if ('animation' == $pptActivity['playMode']) {
            $ssl = isset($fields['ssl']) ? $fields['ssl'] : false;
            $this->convertAnimation($targetId, $ssl);
        }

        return $pptActivity;
    }

    public function delete($targetId)
    {
        return $this->getPptActivityDao()->delete($targetId);
    }

    public function get($targetId)
    {
        return $this->getPptActivityDao()->get($targetId);
    }

    public function find($targetIds)
    {
        return $this->getPptActivityDao()->findByIds($targetIds);
    }

    public function materialSupported()
    {
        return true;
    }

    public function copyFromResourcePlatform($activityExt, $config = array())
    {
        $newActivityExt = $activityExt;
        $files = $config['files'];

        $newActivityExt['syncId'] = $activityExt['id'];
        $newActivityExt['mediaId'] = empty($files[$activityExt['mediaId']]) ? 0 : $files[$activityExt['mediaId']]['id'];
        $newActivityExt['createdTime'] = time();
        $newActivityExt['createdUserId'] = 0;
        $newActivityExt['updatedTime'] = 0;
        unset($newActivityExt['id']);

        return $this->getPptActivityDao()->create($newActivityExt);
    }

    public function convertAnimation($id, $ssl)
    {
        $pptActivity = $this->getPptActivityDao()->get($id);
        $ppt = $this->getUploadFileService()->getFullFile($pptActivity['mediaId']);
        $player = $this->getCloudFileService()->player($ppt['globalId'], $ssl);

        if ('ppt' == $player['type']) {
            return;
        }

        if ('img' == $player['type']) {
            $options = array(
                'directives' => array('convertAnimation' => true),
            );
            $this->getUploadFileService()->reconvertFile($ppt['id'], $options);
        }
    }

    /**
     * @return PptActivityDao
     */
    protected function getPptActivityDao()
    {
        return $this->getBiz()->dao('Activity:PptActivityDao');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->getBiz()->service('File:UploadFileService');
    }

    /**
     * @return CloudFileService
     */
    protected function getCloudFileService()
    {
        return $this->getBiz()->service('CloudFile:CloudFileService');
    }
}
