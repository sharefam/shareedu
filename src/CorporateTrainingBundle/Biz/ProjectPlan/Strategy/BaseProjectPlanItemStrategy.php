<?php

namespace CorporateTrainingBundle\Biz\ProjectPlan\Strategy;

use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;
use CorporateTrainingBundle\Biz\ManagePermission\Service\ManagePermissionOrgService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class BaseProjectPlanItemStrategy
{
    protected $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    protected function checkProjectPlanExist($id)
    {
        $projectPlan = $this->getProjectPlanService()->getProjectPlan($id);

        if (empty($projectPlan)) {
            throw  new NotFoundException('Project Plan Item Not Found');
        }
    }

    protected function beginTransaction()
    {
        $this->biz['db']->beginTransaction();
    }

    protected function commit()
    {
        $this->biz['db']->commit();
    }

    protected function rollback()
    {
        $this->biz['db']->rollback();
    }

    /**
     * @return Logger
     */
    protected function getLogger()
    {
        return $this->biz['logger'];
    }

    /**
     * @return ManagePermissionOrgService
     */
    protected function getManagePermissionService()
    {
        return $this->createService('CorporateTrainingBundle:ManagePermission:ManagePermissionOrgService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\ProjectPlanServiceImpl
     */
    protected function getProjectPlanService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\MemberServiceImpl
     */
    protected function getProjectPlanMemberService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:MemberService');
    }

    protected function getProjectPlanItemDao()
    {
        return $this->createDao('CorporateTrainingBundle:ProjectPlan:ProjectPlanItemDao');
    }

    /**
     * @return EventDispatcherInterface
     */
    private function getDispatcher()
    {
        return $this->biz['dispatcher'];
    }

    /**
     * @param string      $eventName
     * @param Event|mixed $subject
     *
     * @return Event
     */
    protected function dispatchEvent($eventName, $subject, $arguments = array())
    {
        if ($subject instanceof Event) {
            $event = $subject;
        } else {
            $event = new Event($subject, $arguments);
        }

        return $this->getDispatcher()->dispatch($eventName, $event);
    }

    protected function createService($alias)
    {
        return $this->biz->service($alias);
    }

    protected function createDao($alias)
    {
        return $this->biz->dao($alias);
    }
}
