<?php

namespace CorporateTrainingBundle\Controller\Admin;

use AppBundle\Controller\Admin\BaseController;
use CorporateTrainingBundle\Biz\Focus\Service\FocusService;
use Symfony\Component\HttpFoundation\Request;

class FocusController extends BaseController
{
    public function focusAction(Request $request, $type, $time)
    {
        $currentUser = $this->getCurrentUser();
        $focuses = $this->getFocusService()->findFocusNow($type, $time);
        $focusesLater = $this->getFocusService()->findFocusLater($type, $time);
        $focusesAgo = $this->getFocusService()->findFocusAgo($type, $time);

        if (!empty($focusesLater)) {
            foreach ($focusesLater as $focus) {
                array_push($focuses, $focus);
            }
        }

        if (!empty($focusesAgo)) {
            foreach ($focusesAgo as $focus) {
                array_push($focuses, $focus);
            }
        }

        return $this->render(
            'CorporateTrainingBundle::admin/default/focus.html.twig',
            array(
                'focuses' => $focuses,
                'currentUser' => $currentUser,
            )
        );
    }

    public function workCalendarAction(Request $request, $type)
    {
        $startTime = $request->query->get('start');
        $endTime = $request->query->get('end');
        $focuses = $this->getFocusService()->findFocusByStartTimeAndEndTime($type, $startTime, $endTime);

        foreach ($focuses as &$focus) {
            $url = '';
            if ($focus['focus_type'] == 'live_course') {
                $url = $this->generateUrl(
                    'course_set_manage_course_tasks',
                    array(
                        'courseSetId' => $focus['courseSet']['id'],
                        'courseId' => $focus['courseId'],
                    )
                );
            }

            if ($focus['focus_type'] == 'project_plan') {
                $currentUser = $this->getCurrentUser();

                if ($currentUser->hasPermission('admin_train') || $currentUser->hasPermission('admin_data')
                    || in_array('ROLE_SUPER_ADMIN', $currentUser['roles']) || in_array('ROLE_ADMIN', $currentUser['roles']) || $currentUser->hasPermission('admin_project_plan')) {
                    $url = $this->generateUrl('project_plan_manage_overview_board', array('id' => $focus['id']));
                } else {
                    $url = $this->generateUrl('admin_train_teach_manage_project_plan_teaching');
                }
            }

            if ($focus['focus_type'] == 'offline_activity') {
                $url = $this->generateUrl('admin_offline_activity');
            }

            if ($focus['focus_type'] == 'exam') {
                $url = $this->generateUrl('admin_exam_manage_list');
            }

            if ($focus['focus_type'] == 'survey') {
                $url = $this->generateUrl('admin_survey_manage_list');
            }

            $focus['url'] = $url;
        }

        return $this->createJsonResponse($focuses);
    }

    /**
     * @return FocusService
     */
    protected function getFocusService()
    {
        return $this->createService('CorporateTrainingBundle:Focus:FocusService');
    }
}
