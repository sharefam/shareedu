<?php

namespace CorporateTrainingBundle\Controller\Admin;

use AppBundle\Controller\Admin\JobController as BaseController;
use Symfony\Component\HttpFoundation\Request;

class JobController extends BaseController
{
    public function setNextExecTimeAction(Request $request, $id)
    {
        $job = $this->getSchedulerFacadeService()->getJob($id);
        if ('POST' == $request->getMethod()) {
            $nextFiredTime = $request->request->get('nextExecTime', '');
            if (empty($nextFiredTime)) {
                return $this->createJsonResponse(false);
            }
            $nextFiredTime = strtotime($nextFiredTime);
            $job = $this->getSchedulerFacadeService()->setNextFiredTime($id, $nextFiredTime);
            if (empty($job)) {
                return $this->createJsonResponse(false);
            }

            return $this->createJsonResponse(true);
        }

        return $this->render('admin/jobs/set-next-fired-time-modal.html.twig', array(
            'job' => $job,
        ));
    }

    /**
     * @return \CorporateTrainingBundle\Biz\SchedulerFacade\Service\SchedulerFacadeService
     */
    protected function getSchedulerFacadeService()
    {
        return $this->getBiz()->service('CorporateTrainingBundle:SchedulerFacade:SchedulerFacadeService');
    }
}
