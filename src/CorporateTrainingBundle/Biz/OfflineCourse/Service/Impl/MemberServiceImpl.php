<?php

namespace CorporateTrainingBundle\Biz\OfflineCourse\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use CorporateTrainingBundle\Biz\OfflineCourse\Service\MemberService;

class MemberServiceImpl extends BaseService implements MemberService
{
    public function createMember($member)
    {
        $this->validateFields($member);
        $member = $this->filterFields($member);

        return $this->getMemberDao()->create($member);
    }

    public function updateMember($id, $fields)
    {
        $fields = $this->filterFields($fields);

        return $this->getMemberDao()->update($id, $fields);
    }

    public function deleteMember($id)
    {
        return $this->getMemberDao()->delete($id);
    }

    public function getMember($id)
    {
        return $this->getMemberDao()->get($id);
    }

    public function getMemberByOfflineCourseIdAndUserId($offlineCourseId, $userId)
    {
        return $this->getMemberDao()->getByOfflineCourseIdAndUserId($offlineCourseId, $userId);
    }

    public function findMembersByIds($ids)
    {
        return $this->getMemberDao()->findByIds($ids);
    }

    public function findMembersByOfflineCourseId($offlineCourseId)
    {
        return $this->getMemberDao()->findByOfflineCourseId($offlineCourseId);
    }

    public function findMembersByUserId($userId)
    {
        return $this->getMemberDao()->findByUserId($userId);
    }

    public function searchMembers($conditions, $orderBys, $start, $limit)
    {
        return $this->getMemberDao()->search($conditions, $orderBys, $start, $limit);
    }

    public function countMembers($conditions)
    {
        return $this->getMemberDao()->count($conditions);
    }

    public function waveLearnedNum($id, $num)
    {
        return $this->getMemberDao()->wave(
            array($id),
            array('learnedNum' => $num)
        );
    }

    protected function validateFields($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('offlineCourseId', 'userId'), true)) {
            throw $this->createInvalidArgumentException('Lack of required fields');
        }
    }

    protected function filterFields($fields)
    {
        return ArrayToolkit::parts(
            $fields,
            array(
                'offlineCourseId',
                'userId',
                'learnedNum',
            )
        );
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Dao\Impl\MemberDaoImpl
     */
    protected function getMemberDao()
    {
        return $this->createDao('CorporateTrainingBundle:OfflineCourse:MemberDao');
    }
}
