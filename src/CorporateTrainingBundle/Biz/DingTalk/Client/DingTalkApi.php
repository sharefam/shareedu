<?php

namespace  CorporateTrainingBundle\Biz\DingTalk\Client;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Topxia\Service\Common\ServiceKernel;

class DingTalkApi
{
    const HOST = 'https://oapi.dingtalk.com';

    protected $agent = 'CorporateTraining Enterprise Instant Messaging Client 1.0';

    public $connectTimeout = 30;

    public $readTimeout = 30;

    public $httpMethod;

    protected $apiVersion = '2.0';

    protected $contentType = array('Content-type: application/json');

    protected $sdkVersion = 'dingtalk-sdk-php-20161214';

    private static $logger;

    public function post($url, $params)
    {
        $url = self::HOST.$url;
        try {
            $resp = $this->postRequest($url, $params);
        } catch (\Exception $e) {
            $this->logCommunicationError('post', $url, 'HTTP_ERROR_'.$e->getCode(), $e->getMessage());
        }
        $respObject = json_decode($resp, true);

        if (!empty($respObject['errcode'])) {
            $this->logCommunicationError('post', $url, 'HTTP_ERROR_'.$respObject['errcode'], $respObject['errmsg']);
        }

        return $respObject;
    }

    public function get($url, $params)
    {
        $url = self::HOST.$url;
        try {
            $resp = $this->getRequest($url, $params);
        } catch (\Exception $e) {
            $this->logCommunicationError('get', $url, 'HTTP_ERROR_'.$e->getCode(), $e->getMessage());
        }
        $respObject = json_decode($resp, true);

        if (!empty($respObject['errcode'])) {
            $this->logCommunicationError('get', $url, 'HTTP_ERROR_'.$respObject['errcode'], $respObject['errmsg']);
        }

        return $respObject;
    }

    /**
     * HTTP GET.
     *
     * @param string $url 要请求的url地址
     * @param $request
     *
     * @return string
     */
    public function getRequest($url, $params)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, $this->agent);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->readTimeout);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);

        $url = $url.'?'.http_build_query($params);
        curl_setopt($curl, CURLOPT_URL, $url);
        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }

    public function postRequest($url, $params)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, $this->agent);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->readTimeout);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        curl_setopt($curl, CURLOPT_URL, $url);
        //https 请求
        if (strlen($url) > 5 && 'https' == strtolower(substr($url, 0, 5))) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }

    protected function logCommunicationError($apiName, $requestUrl, $errorCode, $responseTxt)
    {
        $logger = $this->getLogger();
        $logData = array(
            date('Y-m-d H:i:s'),
            $apiName,
            $this->sdkVersion,
            $requestUrl,
            $errorCode,
            str_replace("\n", '', $responseTxt),
        );
        $logger->error(json_encode($logData));
    }

    protected function getLogger()
    {
        if (!empty(self::$logger)) {
            return self::$logger;
        }

        $logger = new Logger('dingTalkClient');
        $logger->pushHandler(new StreamHandler(ServiceKernel::instance()->getParameter('kernel.logs_dir').'/dingTalk.log', Logger::DEBUG));

        self::$logger = $logger;

        return $logger;
    }
}
