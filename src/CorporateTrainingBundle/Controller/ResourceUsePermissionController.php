<?php

namespace CorporateTrainingBundle\Controller;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\BaseController;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;
use CorporateTrainingBundle\Biz\ResourceUsePermissionShared\Service\ResourceUsePermissionSharedHistoryService;
use CorporateTrainingBundle\Biz\ResourceUsePermissionShared\Service\ResourceUsePermissionSharedService;
use Symfony\Component\HttpFoundation\Request;

class ResourceUsePermissionController extends BaseController
{
    public function accreditAction(Request $request, $type, $resourceId)
    {
        if ('POST' == $request->getMethod()) {
            $userIds = $request->request->get('userIds');
            if (!empty($userIds)) {
                $userIds = explode(',', $userIds);
                $this->getResourceUsePermissionSharedService()->batchCreateSharedRecord(array(
                'resourceType' => $type,
                'resourceId' => $resourceId,
                'userIds' => $userIds,
            ));
            }
            $count = $this->getResourceUsePermissionSharedService()->countSharedRecords(array('resourceType' => $type, 'resourceId' => $resourceId));

            return $this->createJsonResponse($count);
        }

        return  $this->render('resource-use-permission/accredit-modal.html.twig', array(
            'resourceId' => $resourceId,
            'resourceType' => $type,
        ));
    }

    public function recordListAction(Request $request, $type, $resourceId)
    {
        return  $this->render('resource-use-permission/record-list-modal.html.twig', array(
            'resourceId' => $resourceId,
            'resourceType' => $type,
        ));
    }

    public function ajaxRecordListAction(Request $request, $type, $resourceId)
    {
        $conditions = array(
            'resourceType' => $type,
            'resourceId' => $resourceId,
        );
        $paginator = new Paginator(
            $request,
            $this->getResourceUsePermissionSharedService()->countSharedRecords($conditions),
            20
        );
        $paginator->setBaseUrl($this->generateUrl('resource_use_permission_record_ajax_list', array('type' => $type, 'resourceId' => $resourceId)));

        $records = $this->getResourceUsePermissionSharedService()->searchSharedRecords(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $recordToUserIds = ArrayToolkit::column($records, 'toUserId');
        $recordFromUserIds = ArrayToolkit::column($records, 'fromUserId');
        $userIds = array_merge($recordToUserIds, $recordFromUserIds);

        $recordUsers = array();
        if (!empty($userIds)) {
            $recordUsers = $this->getUserService()->findUsersByIds($userIds);
        }

        return  $this->render('resource-use-permission/record-list-tr.html.twig', array(
            'resourceId' => $resourceId,
            'resourceType' => $type,
            'records' => $records,
            'recordUsers' => $recordUsers,
            'paginator' => $paginator,
        ));
    }

    public function ajaxHistoryListAction(Request $request, $type, $resourceId)
    {
        $conditions = array(
            'resourceType' => $type,
            'resourceId' => $resourceId,
        );
        $username = $request->request->get('nameLike', '');

        if (!empty($username)) {
            $userIds = $this->getUserService()->findUserIdsByNickNameOrTrueName($username);
            $conditions['toUserIds'] = empty($userIds) ? array(-1) : $userIds;
        }

        $paginator = new Paginator(
            $request,
            $this->getResourceUsePermissionSharedService()->countSharedRecords($conditions),
            20
        );
        $paginator->setBaseUrl($this->generateUrl('resource_use_permission_history_ajax_list', array('type' => $type, 'resourceId' => $resourceId)));

        $histories = $this->getResourceUsePermissionSharedHistoryService()->searchHistories(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $recordToUserIds = ArrayToolkit::column($histories, 'toUserId');
        $actionUserIds = ArrayToolkit::column($histories, 'actionUser');
        $userIds = array_merge($recordToUserIds, $actionUserIds);

        $recordUsers = array();
        if (!empty($userIds)) {
            $recordUsers = $this->getUserService()->findUsersByIds($userIds);
        }

        return  $this->render('resource-use-permission/history-list-tr.html.twig', array(
            'resourceId' => $resourceId,
            'resourceType' => $type,
            'histories' => $histories,
            'recordUsers' => $recordUsers,
            'paginator' => $paginator,
        ));
    }

    public function userMatchAction(Request $request)
    {
        $queryString = $request->query->get('q');
        $users = $this->getUserService()->searchUsers(array('truename' => $queryString, 'noType' => 'system', 'locked' => 0), array('id' => 'ASC'), 0, 20, array('truename', 'nickname', 'id'));

        return $this->createJsonResponse($users);
    }

    public function canceledSharedAction(Request $request, $recordId)
    {
        $record = $this->getResourceUsePermissionSharedService()->getSharedRecord($recordId);
        if (empty($record)) {
            throw  new NotFoundException($this->trans('resource.canceled_shared.error_message'));
        }
        $result = $this->getResourceUsePermissionSharedService()->canceledSharedRecord($recordId);
        $sharedCount = $this->getResourceUsePermissionSharedService()->countSharedRecords(array('resourceType' => $record['resourceType'], 'resourceId' => $record['resourceId']));

        return $this->createJsonResponse(array('success' => $result, 'sharedCount' => $sharedCount));
    }

    /**
     * @return \Biz\User\Service\UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return ResourceUsePermissionSharedService
     */
    protected function getResourceUsePermissionSharedService()
    {
        return $this->createService('ResourceUsePermissionShared:ResourceUsePermissionSharedService');
    }

    /**
     * @return ResourceUsePermissionSharedHistoryService
     */
    protected function getResourceUsePermissionSharedHistoryService()
    {
        return $this->createService('ResourceUsePermissionShared:ResourceUsePermissionSharedHistoryService');
    }
}
