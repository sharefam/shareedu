<?php

namespace CorporateTrainingBundle\Component\EIMClient;

use InvalidArgumentException;

class DepartmentFactory
{
    /**
     * 创建EIM Department Client实例.
     *
     * @param string $type   Client的类型
     * @param array  $config 必需包含key, secret两个参数
     *
     * @return EIM Department Client
     */
    private static $departments;

    public static function create($config)
    {
        if (!array_key_exists('key', $config) || !array_key_exists('secret', $config)) {
            throw new InvalidArgumentException('参数中必需包含key, secret两个为key的值');
        }

        $departments = self::departments();

        if (!array_key_exists($config['type'], $departments)) {
            throw new InvalidArgumentException(array('参数不正确%type%', array('%type%' => $config['type'])));
        }

        return self::createClass($config);
    }

    public static function departments()
    {
        $departments = array(
            'dingtalk' => array(
                'class' => 'CorporateTrainingBundle\Component\EIMClient\DingTalk\Department',
            ),
        );

        return $departments;
    }

    public static function createClass($config)
    {
        if (empty(self::$departments)) {
            $departments = self::departments();
            $class = $departments[$config['type']]['class'];
            self::$departments = new $class($config);
        }

        return self::$departments;
    }

    /**
     * 仅给单元测试mock用。
     */
    public function setClass($class)
    {
        self::$departments = $class;
    }
}
