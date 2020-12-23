<?php

namespace CorporateTrainingBundle\Biz\DingTalk\Client;

class DingTalkRequest
{
    private $appId;

    private $appSecret;
    /**
     * 微应用的id
     **/
    private $agentId;

    /**
     * 用户操作产生的授权码
     **/
    private $taskId;

    /**
     * 接收者的部门id列表
     **/
    private $deptIdList;

    /**
     * 与msgtype对应的消息体，具体见文档
     **/
    private $msg;

    /**
     * 是否发送给企业全部用户
     **/
    private $toAllUser;

    /**
     * 接收者的用户userid列表
     **/
    private $userIdList;

    /**
     * 员工在当前开发者企业账号范围内的唯一标识，系统生成，固定值，不会改变
     */
    private $unionid;

    /**
     * 员工id
     */
    private $userid;

    private $apiParams = array();

    public function setAgentId($agentId)
    {
        $this->agentId = $agentId;
        $this->apiParams['agent_id'] = $agentId;
    }

    public function setAppSecret($appSecret)
    {
        $this->appSecret = $appSecret;
        $this->apiParams['appSecret'] = $appSecret;
    }

    public function setAppId($appId)
    {
        $this->appId = $appId;
        $this->apiParams['appId'] = $appId;
    }

    public function getAgentId()
    {
        return $this->agentId;
    }

    public function setUnionid($unionid)
    {
        $this->unionid = $unionid;
        $this->apiParams['unionid'] = $unionid;
    }

    /**
     * @param string $userid
     */
    public function setUserid($userid)
    {
        $this->userid = $userid;
        $this->apiParams['userid'] = $userid;
    }

    /**
     * @param string $taskId
     */
    public function setTaskId($taskId)
    {
        $this->taskId = $taskId;
        $this->apiParams['task_id'] = $taskId;
    }

    public function setDeptIdList($deptIdList)
    {
        $this->deptIdList = $deptIdList;
        $this->apiParams['dept_id_list'] = $deptIdList;
    }

    public function getDeptIdList()
    {
        return $this->deptIdList;
    }

    /**
     * @param int $mobile
     */
    public function setMobile($mobile)
    {
        $this->apiParams['mobile'] = $mobile;
    }

    /**
     * @param array $msg
     */
    public function setMsg($msg)
    {
        $msg = json_encode($msg);
        $this->msg = $msg;
        $this->apiParams['msg'] = $msg;
    }

    public function getMsg()
    {
        return $this->msg;
    }

    public function setToAllUser($toAllUser)
    {
        $this->toAllUser = $toAllUser;
        $this->apiParams['to_all_user'] = $toAllUser;
    }

    public function getToAllUser()
    {
        return $this->toAllUser;
    }

    /**
     * @param array $userIdList
     */
    public function setUserIdList($userIdList)
    {
        $userIdList = implode(',', array_values($userIdList));
        $this->userIdList = $userIdList;
        $this->apiParams['userid_list'] = $userIdList;
    }

    public function getUserIdList()
    {
        return $this->userIdList;
    }

    /**
     * @return array
     */
    public function getApiParams()
    {
        return $this->apiParams;
    }
}
