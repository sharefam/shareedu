<?php

namespace CorporateTrainingBundle\Component\EIMClient\DingTalk;

use CorporateTrainingBundle\Component\EIMClient\AbstractDepartment;

class Department extends AbstractDepartment
{
    protected $accessTokenUrl = 'https://oapi.dingtalk.com/gettoken';
    protected $departmentListUrl = 'https://oapi.dingtalk.com/department/list';
    protected $departmentUrl = 'https://oapi.dingtalk.com/department/get';
    protected $departmentCreateUrl = 'https://oapi.dingtalk.com/department/create?';
    protected $departmentUpdateUrl = 'https://oapi.dingtalk.com/department/update?';
    protected $departmentDeleteUrl = 'https://oapi.dingtalk.com/department/delete';

    protected $contentType = array('Content-type: application/json');

    public function postRequest($url, $params)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, $this->agent);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->contentType);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        curl_setopt($curl, CURLOPT_URL, $url);

        // curl_setopt($curl, CURLINFO_HEADER_OUT, TRUE );

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }
}
