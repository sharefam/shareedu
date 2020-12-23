<?php

namespace CorporateTrainingBundle\Biz\OfflineExam\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Codeages\Biz\Framework\Event\Event;
use CorporateTrainingBundle\Biz\OfflineExam\Service\MemberService;

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

    public function getMemberByOfflineExamIdAndUserId($offlineExamId, $userId)
    {
        return $this->getMemberDao()->getByOfflineExamIdAndUserId($offlineExamId, $userId);
    }

    public function findMembersByIds($ids)
    {
        return $this->getMemberDao()->findByIds($ids);
    }

    public function findMembersByOfflineExamId($offlineExamId)
    {
        return $this->getMemberDao()->findByOfflineExamId($offlineExamId);
    }

    public function searchMembers($conditions, $orderBys, $start, $limit)
    {
        return $this->getMemberDao()->search($conditions, $orderBys, $start, $limit);
    }

    public function countMembers($conditions)
    {
        return $this->getMemberDao()->count($conditions);
    }

    public function markPass($offlineExamId, $userId, $score)
    {
        $oldMember = $this->getMemberByOfflineExamIdAndUserId($offlineExamId, $userId);

        $fields = array(
            'offlineExamId' => $offlineExamId,
            'userId' => $userId,
            'score' => $score,
            'operatorId' => $this->getCurrentUser()['id'],
            'status' => 'passed',
        );
        if (empty($oldMember)) {
            $member = $this->createMember($fields);
        } else {
            $member = $this->updateMember($oldMember['id'], $fields);
        }

        $this->dispatchEvent('offline.exam.mark.pass', new Event($member));
    }

    public function markUnPass($offlineExamId, $userId, $score)
    {
        $oldMember = $this->getMemberByOfflineExamIdAndUserId($offlineExamId, $userId);

        $fields = array(
            'offlineExamId' => $offlineExamId,
            'userId' => $userId,
            'score' => $score,
            'operatorId' => $this->getCurrentUser()['id'],
            'status' => 'unpassed',
        );
        if (empty($oldMember)) {
            $member = $this->createMember($fields);
        } else {
            $member = $this->updateMember($oldMember['id'], $fields);
        }

        $this->dispatchEvent('offline.exam.mark.unpass', new Event($member));
    }

    protected function validateFields($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('offlineExamId', 'userId'), true)) {
            throw $this->createInvalidArgumentException('Lack of required fields');
        }
    }

    protected function filterFields($fields)
    {
        return ArrayToolkit::parts(
            $fields,
            array(
                'offlineExamId',
                'userId',
                'score',
                'status',
                'operatorId',
            )
        );
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineExam\Dao\Impl\MemberDaoImpl
     */
    protected function getMemberDao()
    {
        return $this->createDao('CorporateTrainingBundle:OfflineExam:MemberDao');
    }
}
