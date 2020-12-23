<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\ConvertIpToolkit;
use Symfony\Component\HttpFoundation\Request;

class LoginRecordController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = $request->query->all();

        $conditions = $this->fillOrgCode($conditions);

        if (isset($conditions['keywordType']) && isset($conditions['keyword'])) {
            $conditions[$conditions['keywordType']] = $conditions['keyword'];

            unset($conditions['keywordType']);
            unset($conditions['keyword']);
        }

        $conditions['action'] = 'login_success';

        $paginator = new Paginator(
            $this->get('request'),
            $this->getLogService()->countUserLoginLog($conditions),
            20
        );

        $logRecords = $this->getLogService()->searchUserLoginLog(
            $conditions,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $logRecords = ConvertIpToolkit::ConvertIps($logRecords);

        $userIds = ArrayToolkit::column($logRecords, 'userId');

        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('admin/login-record/index.html.twig', array(
            'logRecords' => $logRecords,
            'users' => $users,
            'paginator' => $paginator,
        ));
    }

    public function showUserLoginRecordAction(Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);

        $conditions = array(
            'userId' => $user['id'],
            'action' => 'login_success',
            'module' => 'user',
        );

        $paginator = new Paginator(
            $this->get('request'),
            $this->getLogService()->searchLogCount($conditions),
            8
        );

        $loginRecords = $this->getLogService()->searchLogs(
            $conditions,
            'created',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $loginRecords = ConvertIpToolkit::ConvertIps($loginRecords);

        return $this->render('admin/login-record/login-record-details.html.twig', array(
            'user' => $user,
            'loginRecords' => $loginRecords,
            'loginRecordPaginator' => $paginator,
        ));
    }

    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }
}
