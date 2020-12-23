<?php

namespace CorporateTrainingBundle\Biz\Teacher\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use CorporateTrainingBundle\Biz\Teacher\Service\ProfileService;

class ProfileServiceImpl extends BaseService implements ProfileService
{
    public function createProfile($fields)
    {
        $this->validateProfileFields($fields);
        if ($this->getProfileByUserId($fields['userId'])) {
            throw $this->createServiceException('User already exists');
        }

        $fields = $this->filterProfileFields($fields);

        return $this->getProfileDao()->create($fields);
    }

    public function updateProfile($id, $fields)
    {
        $this->checkProfileExist($id);
        $fields = $this->filterProfileFields($fields);

        return $this->getProfileDao()->update($id, $fields);
    }

    public function batchSetTeacherProfile($userIds, $fields)
    {
        foreach ($userIds as $userId) {
            $profile = $this->getProfileByUserId($userId);
            $fields['userId'] = $userId;
            if (empty($profile)) {
                $fields['creator'] = $this->getCurrentUser()->getId();
                $this->createProfile($fields);
            } else {
                $this->updateProfile($profile['id'], $fields);
            }
        }
    }

    public function deleteProfile($id)
    {
        $this->checkProfileExist($id);

        return  $this->getProfileDao()->delete($id);
    }

    public function getProfile($id)
    {
        return $this->getProfileDao()->get($id);
    }

    public function getProfileByUserId($userId)
    {
        return $this->getProfileDao()->getByUserId($userId);
    }

    public function findProfilesByIds($ids)
    {
        return $this->getProfileDao()->findByIds($ids);
    }

    public function findProfilesByLevelId($levelId)
    {
        return $this->getProfileDao()->findByLevelId($levelId);
    }

    public function countProfiles($conditions)
    {
        return $this->getProfileDao()->count($conditions);
    }

    public function searchProfiles($conditions, $orderBy, $start, $limit)
    {
        return $this->getProfileDao()->search($conditions, $orderBy, $start, $limit);
    }

    protected function validateProfileFields($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('userId'), true)) {
            throw $this->createInvalidArgumentException('Lack of required fields');
        }
    }

    protected function filterProfileFields($fields)
    {
        return ArrayToolkit::parts($fields, array('userId', 'levelId', 'teacherProfessionFieldIds', 'creator'));
    }

    protected function checkProfileExist($id)
    {
        $Profile = $this->getProfile($id);

        if (empty($Profile)) {
            throw $this->createNotFoundException("Profile #{$id} Not Exist");
        }
    }

    /**
     * @return \CorporateTrainingBundle\Biz\Teacher\Dao\Impl\ProfileDaoImpl
     */
    protected function getProfileDao()
    {
        return $this->createDao('CorporateTrainingBundle:Teacher:ProfileDao');
    }
}
