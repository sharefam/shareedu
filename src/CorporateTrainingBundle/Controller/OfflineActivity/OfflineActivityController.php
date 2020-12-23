<?php

namespace CorporateTrainingBundle\Controller\OfflineActivity;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\Admin\BaseController;
use CorporateTrainingBundle\Biz\OfflineActivity\Service\MemberService;
use CorporateTrainingBundle\Biz\OfflineActivity\Service\OfflineActivityService;
use CorporateTrainingBundle\Biz\ResourceScope\Service\ResourceAccessScopeService;
use Symfony\Component\HttpFoundation\Request;

class OfflineActivityController extends BaseController
{
    public function detailAction(Request $request, $id)
    {
        if (!$this->getOfflineActivityService()->canUserVisitResource($id)) {
            return $this->createMessageResponse('error', $this->trans('resource.no_permission'));
        }
        $offlineActivity = $this->getOfflineActivityService()->getOfflineActivity($id);

        if ('published' != $offlineActivity['status']) {
            throw $this->createNotFoundException($this->trans('offline_activity.message.not_exist_error'));
        }
        $type = (time() > $offlineActivity['endTime']) ? 'end' : 'ongoing';
        $members = $this->getOfflineActivityMemberService()->findMembersByOfflineActivityId($id);

        $userIds = ArrayToolkit::column($members, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);
        $canManage = $this->getOfflineActivityService()->canManageOfflineActivity($id);
        $canAccess = true;
        if ($offlineActivity['conditionalAccess']) {
            $canAccess = $this->getResourceAccessScopeService()->canUserAccessResource('offlineActivity', $offlineActivity['id'], $this->getCurrentUser()->getId());
        }

        return $this->render('offline-activity/detail.html.twig', array(
            'offlineActivity' => $offlineActivity,
            'type' => $type,
            'users' => $users,
            'showType' => 'detail',
            'canManage' => $canManage,
            'canAccess' => $canAccess,
        ));
    }

    public function listAction(Request $request, $type)
    {
        $conditions = array(
            'status' => 'published',
        );

        $conditions['searchType'] = ('end' == $type ? $type : 'ongoing');

        $conditions['ids'] = $this->getResourceVisibleScopeService()->findVisibleResourceIdsByResourceTypeAndUserId('offlineActivity', $this->getCurrentUser()->getId());
        $count = $this->getOfflineActivityService()->countOfflineActivities($conditions);

        return $this->render('offline-activity/list.html.twig', array(
            'type' => $type,
            'total' => $count,
        ));
    }

    public function ajaxGetRowHtmlAction(Request $request)
    {
        $conditions = array(
            'status' => 'published',
        );

        $searchType = $request->query->get('type', 'ongoing');

        $conditions['searchType'] = ('end' == $searchType ? $searchType : 'ongoing');
        $conditions['ids'] = $this->getResourceVisibleScopeService()->findVisibleResourceIdsByResourceTypeAndUserId('offlineActivity', $this->getCurrentUser()->getId());

        $start = $request->query->get('start', 0);

        $offlineActivities = $this->getOfflineActivityService()->searchOfflineActivities(
            $conditions,
            array('startTime' => 'DESC'),
            $start,
            5
        );

        foreach ($offlineActivities as &$offlineActivity) {
            $offlineActivity['canAccess'] = true;
            if ($offlineActivity['conditionalAccess']) {
                $offlineActivity['canAccess'] = $this->getResourceAccessScopeService()->canUserAccessResource('offlineActivity', $offlineActivity['id'], $this->getCurrentUser()->getId());
            }
        }

        return $this->render('offline-activity/list-row.html.twig', array(
            'offlineActivities' => $offlineActivities,
            'type' => $searchType,
            'user' => $this->getCurrentUser(),
        ));
    }

    public function checkMemberAction(Request $request, $id)
    {
        $keyWord = $request->query->get('value');
        $user = $this->getUserService()->getUserByLoginField($keyWord);

        if (empty($user)) {
            $result = $this->trans('offline_activity.check_member.message.user_not_exist');
        } else {
            $result = $this->getOfflineActivityMemberService()->isMember($id, $user['id']);

            if ($result) {
                $result = $this->trans('offline_activity.check_member.message.user_repeated_sign_in');
            } else {
                $result = !$result;
            }
        }

        return $this->createJsonResponse($result);
    }

    public function enrollAction(Request $request, $activityId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException();
        }

        $offlineActivity = $this->getOfflineActivityService()->getOfflineActivity($activityId);
        if (empty($offlineActivity['requireAudit'])) {
            return $this->redirectToRoute('offline_activity_attend', array('activityId' => $activityId));
        } else {
            return $this->redirectToRoute('offline_activity_apply', array('activityId' => $activityId));
        }
    }

    public function attendAction(Request $request, $activityId)
    {
        $offlineActivity = $this->getOfflineActivityService()->getOfflineActivity($activityId);
        $user = $this->getCurrentUser();
        $canAccess = true;
        if ($offlineActivity['conditionalAccess']) {
            $canAccess = $this->getResourceAccessScopeService()->canUserAccessResource('offlineActivity', $offlineActivity['id'], $user['id']);
        }
        if ('POST' === $request->getMethod()) {
            if (!$canAccess) {
                return $this->createAccessDeniedException($this->trans('resource_scope.no_permission'));
            }
            $this->getOfflineActivityMemberService()->enter($activityId, $user['id']);

            return $this->createJsonResponse(true);
        }

        return $this->render(
            '@CorporateTraining/offline-activity/attend-modal.html.twig',
            array(
                'offlineActivity' => $offlineActivity,
                'canAccess' => $canAccess,
            )
        );
    }

    public function applyAction(Request $request, $activityId)
    {
        $offlineActivity = $this->getOfflineActivityService()->getOfflineActivity($activityId);
        $user = $this->getCurrentUser();
        $canAccess = true;
        if ($offlineActivity['conditionalAccess']) {
            $canAccess = $this->getResourceAccessScopeService()->canUserAccessResource('offlineActivity', $offlineActivity['id'], $user['id']);
        }
        if ('POST' === $request->getMethod()) {
            if (!$canAccess) {
                return $this->createAccessDeniedException($this->trans('resource_scope.no_permission'));
            }
            $this->getOfflineActivityService()->applyAttendOfflineActivity($activityId, $user['id']);

            return $this->createJsonResponse(true);
        }

        return $this->render(
            '@CorporateTraining/offline-activity/apply-modal.html.twig',
            array(
                'offlineActivity' => $offlineActivity,
                'canAccess' => $canAccess,
            )
        );
    }

    public function signInAction(Request $request, $activityId)
    {
        $offlineActivity = $this->getOfflineActivityService()->getOfflineActivity($activityId);

        if ('app' == $request->query->get('origin')) {
            $nickname = $request->query->get('nickname');
            $result = $this->signInApp($offlineActivity, $nickname);

            return $result;
        }

        if ($offlineActivity['endTime'] < time()) {
            return $this->render('offline-activity/sign-in/error.html.twig',
                array(
                    'activityId' => $activityId,
                    'message' => $this->trans('offline_activity.message.activity_already_finished'),
                )
            );
        }

        if ('POST' === $request->getMethod()) {
            $field = $request->request->get('field');
            $user = $this->getUserService()->getUserByLoginField($field);

            if (empty($user)) {
                return $this->render('offline-activity/sign-in/error.html.twig',
                    array(
                        'activityId' => $activityId,
                        'message' => $this->trans('offline_activity.message.input_info_error'),
                    )
                );
            }

            $member = $this->getOfflineActivityMemberService()->getMemberByActivityIdAndUserId($activityId, $user['id']);
            if (empty($member)) {
                return $this->render('offline-activity/sign-in/error.html.twig',
                    array(
                        'activityId' => $activityId,
                        'message' => $this->trans('offline_activity.message.not_sign_in_error'),
                    )
                );
            }

            if ('attended' == $member['attendedStatus']) {
                return $this->render('offline-activity/sign-in/error.html.twig',
                    array(
                        'activityId' => $activityId,
                        'message' => $this->trans('offline_activity.message.user_repeated_sign_in'),
                    )
                );
            }

            $this->getOfflineActivityMemberService()->signIn($member['id']);

            return $this->render('offline-activity/sign-in/success.html.twig',
                array(
                    'activityId' => $activityId,
                    'message' => $this->trans('offline_activity.message.sign_in_success'),
                )
            );
        }

        return $this->render('offline-activity/sign-in/index.html.twig',
            array(
                'offlineActivity' => $offlineActivity,
            )
        );
    }

    protected function signInApp($activity, $nickname)
    {
        if ($activity['endTime'] < time()) {
            return $this->createJsonResponse(array('success' => false, 'message' => $this->trans('offline_activity.message.activity_already_finished')));
        }

        $user = $this->getUserService()->getUserByNickname($nickname);

        $member = $this->getOfflineActivityMemberService()->getMemberByActivityIdAndUserId($activity['id'], $user['id']);
        if (empty($member)) {
            return $this->createJsonResponse(array('success' => false, 'message' => $this->trans('offline_activity.message.not_sign_in_error')));
        }

        if ('attended' == $member['attendedStatus']) {
            return $this->createJsonResponse(array('success' => false, 'message' => $this->trans('offline_activity.message.user_repeated_sign_in')));
        }

        $this->getOfflineActivityMemberService()->signIn($member['id']);

        return $this->createJsonResponse(array('success' => true, 'message' => $this->trans('offline_activity.message.sign_in_success')));
    }

    /**
     * @return OfflineActivityService
     */
    protected function getOfflineActivityService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineActivity:OfflineActivityService');
    }

    /**
     * @return MemberService
     */
    protected function getOfflineActivityMemberService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineActivity:MemberService');
    }

    protected function getResourceVisibleScopeService()
    {
        return $this->createService('ResourceScope:ResourceVisibleScopeService');
    }

    /**
     * @return ResourceAccessScopeService
     */
    protected function getResourceAccessScopeService()
    {
        return $this->createService('ResourceScope:ResourceAccessScopeService');
    }
}
