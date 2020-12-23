<?php

namespace Biz\System\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\AppLoggerConstant;
use Biz\BaseService;
use Biz\LoggerConstantInterface;
use Biz\System\Dao\LogDao;
use Biz\System\Dao\LogJoinUserDao;
use Biz\User\Service\UserService;
use Biz\System\Service\LogService;

class LogServiceImpl extends BaseService implements LogService
{
    public function info($module, $action, $message, array $data = null)
    {
        return $this->addLog('info', $module, $action, $message, $data);
    }

    public function warning($module, $action, $message, array $data = null)
    {
        return $this->addLog('warning', $module, $action, $message, $data);
    }

    public function error($module, $action, $message, array $data = null)
    {
        return $this->addLog('error', $module, $action, $message, $data);
    }

    public function searchLogs($conditions, $sort, $start, $limit)
    {
        $conditions = $this->prepareSearchConditions($conditions);

        if (!is_array($sort)) {
            switch ($sort) {
                case 'created':
                    $sort = array('id' => 'DESC');
                    break;
                case 'createdByAsc':
                    $sort = array('id' => 'ASC');
                    break;
                default:
                    throw $this->createServiceException('参数sort不正确。');
                    break;
            }
        }

        $logs = $this->getLogDao()->search($conditions, $sort, $start, $limit);

        foreach ($logs as &$log) {
            $log['data'] = empty($log['data']) ? array() : json_decode($log['data'], true);
            unset($log);
        }

        return $logs;
    }

    public function searchLogCount($conditions)
    {
        $conditions = $this->prepareSearchConditions($conditions);

        return $this->getLogDao()->count($conditions);
    }

    protected function addLog($level, $module, $action, $message, array $data = null)
    {
        $user = $this->getCurrentUser();

        return $this->getLogDao()->create(
            array(
                'module' => $module,
                'action' => $action,
                'message' => $message,
                'data' => empty($data) ? '' : json_encode($data),
                'userId' => $user['id'],
                'ip' => $user['currentIp'],
                'createdTime' => time(),
                'level' => $level,
            )
        );
    }

    public function analysisLoginNumByTime($startTime, $endTime)
    {
        return $this->getLogDao()->analysisLoginNumByTime($startTime, $endTime);
    }

    public function analysisLoginDataByTime($startTime, $endTime)
    {
        return $this->getLogDao()->analysisLoginDataByTime($startTime, $endTime);
    }

    public function analysisWebDateHourLoginDataByUserIds($date, array $userIds)
    {
        $dateHourDate = $this->getLogDao()->analysisWebDateHourLoginDataByUserIds($date, $userIds);
        $dateHourDate = ArrayToolkit::index($dateHourDate, 'hour');

        return $dateHourDate;
    }

    public function analysisAppDateHourLoginDataByUserIds($date, array $userIds)
    {
        $dateHourDate = $this->getLogDao()->analysisAppDateHourLoginDataByUserIds($date, $userIds);
        $dateHourDate = ArrayToolkit::index($dateHourDate, 'hour');

        return $dateHourDate;
    }

    public function countUserLoginLog($conditions)
    {
        $conditions = $this->prepareSearchConditions($conditions);

        return $this->getLogJoinUserDao()->countLogJoinUser($conditions);
    }

    public function searchUserLoginLog($conditions, $start, $limit)
    {
        $conditions = $this->prepareSearchConditions($conditions);

        return $this->getLogJoinUserDao()->searchLogJoinUser($conditions, $start, $limit);
    }

    public function getModules()
    {
        $loggerConstantList = $this->getLoggerConstantList();
        $modules = array();
        foreach ($loggerConstantList as $loggerConstant) {
            $modules = array_merge($modules, $loggerConstant->getModules());
        }

        return $modules;
    }

    public function getActionsByModule($module)
    {
        $loggerConstantList = $this->getLoggerConstantList();
        $actions = array();
        foreach ($loggerConstantList as $loggerConstant) {
            $actions = array_merge($actions, $loggerConstant->getActions());
        }

        if (isset($actions[$module])) {
            return $actions[$module];
        } else {
            return array();
        }
    }

    public function updateLog($id, $fields)
    {
        $fields = $this->filterLogFields($fields);

        return $this->getLogDao()->update($id, $fields);
    }

    protected function filterLogFields($fields)
    {
        return ArrayToolkit::parts(
            $fields,
            array(
                'userId',
                'module',
                'action',
                'message',
                'data',
                'ip',
                'level',
            )
        );
    }

    /**
     * @return array LoggerConstantInterface
     */
    protected function getLoggerConstantList()
    {
        $loggerList = array();
        $loggerList[] = new AppLoggerConstant();

        $customLoggerClass = 'CustomBundle\Biz\LoggerConstant';
        if (class_exists($customLoggerClass)) {
            $customLogger = new $customLoggerClass();

            if ($customLogger instanceof LoggerConstantInterface) {
                $loggerList[] = $customLogger;
            }
        }

        $pcm = $this->biz['pluginConfigurationManager'];

        $installedPlugins = $pcm->getInstalledPlugins();

        foreach ($installedPlugins as $installedPlugin) {
            $code = ucfirst($installedPlugin['code']);
            $pluginLoggerClass = "{$code}Plugin\\Biz\\LoggerConstant";
            if (class_exists($pluginLoggerClass)) {
                $pluginLogger = new $pluginLoggerClass();

                if ($pluginLogger instanceof LoggerConstantInterface) {
                    $loggerList[] = $pluginLogger;
                }
            }
        }

        return $loggerList;
    }

    /**
     * @return LogDao
     */
    protected function getLogDao()
    {
        return $this->createDao('System:LogDao');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }

    protected function prepareSearchConditions($conditions)
    {
        if (!empty($conditions['nickname'])) {
            $existsUser = $this->getUserService()->getUserByNickname($conditions['nickname']);
            $userId = $existsUser ? $existsUser['id'] : -1;
            $conditions['userId'] = $userId;
            unset($conditions['nickname']);
        }

        if (!empty($conditions['truename'])) {
            $existsUsers = $this->getUserService()->findUserProfilesByTrueName($conditions['truename']);
            if (empty($existsUsers)) {
                $existsUser = $this->getUserService()->getUserByNickname($conditions['truename']);
                $userId = isset($existsUser) ? $existsUser['id'] : -1;
                $conditions['userId'] = $userId;
            } else {
                $existsUserIds = ArrayToolkit::column($existsUsers, 'id');
                $conditions['userIds'] = isset($existsUserIds) ? $existsUserIds : array(-1);
            }

            unset($conditions['truename']);
        }

        if (!empty($conditions['startDateTime'])) {
            $conditions['startDateTime'] = strtotime($conditions['startDateTime']);
        }
        if (!empty($conditions['endDateTime'])) {
            $conditions['endDateTime'] = strtotime($conditions['endDateTime']);
        }

        if (empty($conditions['level']) || !in_array($conditions['level'], array('info', 'warning', 'error'))) {
            unset($conditions['level']);
        }

        return $conditions;
    }

    /**
     * @return LogJoinUserDao
     */
    protected function getLogJoinUserDao()
    {
        return $this->createDao('System:LogJoinUserDao');
    }
}
