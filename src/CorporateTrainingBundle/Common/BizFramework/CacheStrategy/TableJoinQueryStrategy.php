<?php

namespace CorporateTrainingBundle\Common\BizFramework\CacheStrategy;

use Codeages\Biz\Framework\Dao\CacheStrategy;
use Codeages\Biz\Framework\Dao\GeneralDaoInterface;
use Codeages\Biz\Framework\Context\Biz;

/**
 * 多表联查表级别缓存策略，只适用于无更新操作的Dao使用
 */
class TableJoinQueryStrategy implements CacheStrategy
{
    const LIFE_TIME = 3600;

    protected $biz;

    protected $redis;

    protected $storage;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
        $this->redis = $this->biz->offsetGet('redis');
        $this->storage = $this->biz->offsetGet('dao.cache.array_storage');
    }

    public function beforeQuery(GeneralDaoInterface $dao, $method, $arguments)
    {
        $key = $this->key($dao, $method, $arguments);

        return $this->redis->get($key);
    }

    public function afterQuery(GeneralDaoInterface $dao, $method, $arguments, $data)
    {
        $key = $this->key($dao, $method, $arguments);

        return $this->redis->set($key, $data, self::LIFE_TIME);
    }

    public function afterCreate(GeneralDaoInterface $dao, $method, $arguments, $row)
    {
        // 该策略针对连表查询，如果DAO内有更新操作，对应缓存更新需要额外处理
        return;
    }

    public function afterUpdate(GeneralDaoInterface $dao, $method, $arguments, $row)
    {
        // 该策略针对连表查询，如果DAO内有更新操作，对应缓存更新需要额外处理
        return;
    }

    public function afterWave(GeneralDaoInterface $dao, $method, $arguments, $affected)
    {
        // 该策略针对连表查询，如果DAO内有更新操作，对应缓存更新需要额外处理
        return;
    }

    public function afterDelete(GeneralDaoInterface $dao, $method, $arguments)
    {
        // 该策略针对连表查询，如果DAO内有更新操作，对应缓存更新需要额外处理
        return;
    }

    public function flush(GeneralDaoInterface $dao)
    {
        // 该策略针对连表查询，如果DAO内有更新操作，对应缓存更新需要额外处理
        return;
    }

    private function key(GeneralDaoInterface $dao, $method, $arguments)
    {
        $version = $this->getTableVersion($dao);
        $daoName = $this->getDaoName($dao);
        $key = sprintf('dao:%s:v:%s:%s:%s', $daoName, $version, $method, json_encode($arguments));

        return $key;
    }

    private function getDaoName($dao)
    {
        $tables = $dao->getJoinTables();

        return implode('-', $tables);
    }

    private function getTableVersion($dao)
    {
        $tables = $dao->getJoinTables();

        $version = '';

        foreach ($tables as $table) {
            $version = $version.$this->getSingleTableVersion($table).'-';
        }

        return $version;
    }

    private function getSingleTableVersion($tableName)
    {
        $key = sprintf('dao:%s:v', $tableName);

        if (isset($this->storage[$key])) {
            return $this->storage[$key];
        }

        $version = $this->redis->get($key);
        if (false === $version) {
            $version = $this->redis->incr($key);
        }

        $this->storage[$key] = $version;

        return $version;
    }
}
