<?php

namespace CorporateTrainingBundle\Biz\DataStatistics\Dao\Base;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Doctrine\DBAL\Connection;

abstract class AbstractDataStatisticsDaoImpl extends GeneralDaoImpl
{
    protected $defaultCacheLifetime = 3600;

    abstract protected function getAllowedWhereConditions();

    /*
     * 关闭 DapProxy 默认cache策略
     * 启用redis cache 请使用DataStatisticsDaoRedisCacheProxy
     */
    public function declares()
    {
        return array(
            'cache' => false,
        );
    }

    protected function isAllowedWhereCondition($conditionKey)
    {
        $allowed = $this->getAllowedWhereConditions();

        return array_key_exists($conditionKey, $allowed);
    }

    protected function getAllowedWhereConditionStringByKey($conditionKey)
    {
        $allowed = $this->getAllowedWhereConditions();

        return $allowed[$conditionKey];
    }

    protected function addWhereToQueryBuilder($builder, $conditions)
    {
        $conditions = array_filter(
            $conditions,
            function ($value) {
                if ('' === $value || null === $value) {
                    return false;
                }

                if (is_array($value) && empty($value)) {
                    return false;
                }

                return true;
            }
        );

        foreach ($conditions as $conditionKey => $conditionValue) {
            if ($this->isAllowedWhereCondition($conditionKey)) {
                $conditionStr = $this->getAllowedWhereConditionStringByKey($conditionKey);

                $likeType = $this->matchLikeCondition($conditionStr);
                if ($likeType) {
                    list($conditionStr, $conditionValue) = $this->formatLikeCondition($conditionStr, $conditionValue,
                        $likeType);
                }

                $builder->andWhere($conditionStr);

                if (is_array($conditionValue)) {
                    $builder->setParameter($conditionKey, $conditionValue, Connection::PARAM_STR_ARRAY);
                } else {
                    $builder->setParameter($conditionKey, $conditionValue);
                }
            }
        }

        return $builder;
    }

    public function getCacheLifetime()
    {
        return $this->defaultCacheLifetime;
    }

    private function matchLikeCondition($where)
    {
        $matched = preg_match('/\s+((PRE_|SUF_)?LIKE)\s+/i', $where, $matches);
        if (!$matched) {
            return false;
        }

        return strtolower($matches[1]);
    }

    private function formatLikeCondition($conditionStr, $conditionValue, $likeType)
    {
        if ('pre_like' == $likeType) {
            $conditionStr = preg_replace('/pre_like/i', 'LIKE', $conditionStr, 1);
            $conditionValue = $conditionValue.'%';
        } elseif ('suf_like' == $likeType) {
            $conditionStr = preg_replace('/suf_like/i', 'LIKE', $conditionStr, 1);
            $conditionValue = '%'.$conditionValue;
        } else {
            $conditionValue = '%'.$conditionValue.'%';
        }

        return array($conditionStr, $conditionValue);
    }
}
