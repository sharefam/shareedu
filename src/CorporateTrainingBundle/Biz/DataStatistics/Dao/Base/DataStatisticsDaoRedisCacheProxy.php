<?php

namespace CorporateTrainingBundle\Biz\DataStatistics\Dao\Base;

class DataStatisticsDaoRedisCacheProxy
{
    protected $biz;

    protected $dao;

    public function __construct($biz, $dao)
    {
        $this->biz = $biz;
        $this->dao = $dao;
    }

    public function __call($method, $arguments)
    {
        if ($this->isRedisEnabled()) {
            $cache = $this->getRedis()->get($this->getCacheKey($method, $arguments));
            if (false !== $cache) {
                return $cache;
            }
        }

        $result = call_user_func_array(array($this->biz->dao($this->dao), $method), $arguments);

        if ($this->isRedisEnabled()) {
            $cacheLifetime = call_user_func_array(array($this->biz->dao($this->dao), 'getCacheLifetime'), array());
            $this->getRedis()->set($this->getCacheKey($method, $arguments), $result, $cacheLifetime);
        }

        return $result;
    }

    private function isRedisEnabled()
    {
        return $this->biz->offsetExists('redis');
    }

    private function getRedis()
    {
        return $this->biz['redis'];
    }

    private function getCacheKey($methodName, $arguments)
    {
        $key = 'statistics:'.$this->dao.':'.$methodName.':args:'.json_encode($arguments);

        return $key;
    }
}
