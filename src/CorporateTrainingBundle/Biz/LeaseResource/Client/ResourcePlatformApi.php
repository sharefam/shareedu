<?php

namespace CorporateTrainingBundle\Biz\LeaseResource\Client;

use Biz\CloudPlatform\Client\AbstractCloudAPI;
use Biz\CloudPlatform\Client\CloudAPIIOException;

class ResourcePlatformApi extends AbstractCloudAPI
{
    protected $apiUrl = 'http://esm.edusoho.com';

    public function getCourseInfo($resourceCode)
    {
        try {
            return $this->get('/api/lease_course/'.$resourceCode, array('accessKey' => $this->accessKey));
        } catch (CloudAPIIOException $e) {
            throw $e;
        }
    }

    public function getResourceToken($globalId, $uri, $leaseResourceType, $resourceCodes, $params = array())
    {
        try {
            $result = $this->get('/api/resource_token/'.$globalId, array('accessKey' => $this->accessKey, 'uri' => $uri, 'resourceType' => $leaseResourceType, 'resourceCodes' => $resourceCodes, 'params' => $params));
            if (!empty($result['error'])) {
                throw new CloudAPIIOException($result['error']['message']);
            }

            return $result;
        } catch (CloudAPIIOException $e) {
            throw $e;
        }
    }

    protected function _request($method, $uri, $params, $headers)
    {
        $requestId = substr(md5(uniqid('', true)), -16);

        $url = $this->apiUrl.$uri;

        if ($this->isWithoutNetwork()) {
            if ($this->debug && $this->logger) {
                $this->logger->debug("NetWork Off, So Block:[{$requestId}] {$method} {$url}", array('params' => $params, 'headers' => $headers));
            }

            return array('network' => 'off');
        }

        $headers[] = 'Content-type: application/json';
        $headers[] = 'Accept:application/vnd.edusoho.v2+json';

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_USERAGENT, $this->userAgent);

        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 1);

        if ('POST' == $method) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));
        } elseif ('PUT' == $method) {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));
        } elseif ('DELETE' == $method) {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));
        } elseif ('PATCH' == $method) {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));
        } else {
            if (!empty($params)) {
                $url = $url.(strpos($url, '?') ? '&' : '?').http_build_query($params);
            }
        }

        $headers[] = 'Auth-Token: '.$this->_makeAuthToken($url, 'GET' == $method ? array() : $params);
        $headers[] = 'API-REQUEST-ID: '.$requestId;

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_URL, $url);

        $response = curl_exec($curl);
        $curlinfo = curl_getinfo($curl);

        $header = substr($response, 0, $curlinfo['header_size']);
        $body = substr($response, $curlinfo['header_size']);

        $this->debug && $this->logger && $this->logger->debug("[{$requestId}] CURL_INFO", $curlinfo);
        $this->debug && $this->logger && $this->logger->debug("[{$requestId}] RESPONSE_HEADER {$header}");
        $this->debug && $this->logger && $this->logger->debug("[{$requestId}] RESPONSE_BODY {$body}");

        curl_close($curl);

        $context = array(
            'CURLINFO' => $curlinfo,
            'HEADER' => $header,
            'BODY' => $body,
        );

        if (empty($curlinfo['namelookup_time'])) {
            $this->logger && $this->logger->error("[{$requestId}] NAME_LOOK_UP_TIMEOUT", $context);
        }

        if (empty($curlinfo['connect_time'])) {
            $this->logger && $this->logger->error("[{$requestId}] API_CONNECT_TIMEOUT", $context);
            throw new CloudAPIIOException("Connect api server timeout (url: {$url}).");
        }

        if (empty($curlinfo['starttransfer_time'])) {
            $this->logger && $this->logger->error("[{$requestId}] API_TIMEOUT", $context);
            throw new CloudAPIIOException("Request api server timeout (url:{$url}).");
        }

        if ($curlinfo['http_code'] >= 500) {
            $this->logger && $this->logger->error("[{$requestId}] API_RESOPNSE_ERROR", $context);
            throw new CloudAPIIOException("Api server internal error (url:{$url}).");
        }

        $result = json_decode($body, true);

        if (is_null($result)) {
            $this->logger && $this->logger->error("[{$requestId}] RESPONSE_JSON_DECODE_ERROR", $context);
            throw new CloudAPIIOException("Api result json decode error: (url:{$url}).");
        }

        if ($this->debug && $this->logger) {
            $this->logger->debug("[{$requestId}] {$method} {$url}", array('params' => $params, 'headers' => $headers));
        }

        return $result;
    }
}
