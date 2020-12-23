<?php

namespace CorporateTrainingBundle\Component\EIMClient;

use InvalidArgumentException;

class UserFactory
{
    /**
     * 创建EIM User Client实例.
     *
     * @param string $type   Client的类型
     * @param array  $config 必需包含key, secret两个参数
     *
     * @return EIM User Client
     */
    public static function create($config)
    {
        if (!array_key_exists('key', $config) || !array_key_exists('secret', $config)) {
            throw new InvalidArgumentException('参数中必需包含key, secret两个为key的值');
        }

        $users = self::users();

        if (!array_key_exists($config['type'], $users)) {
            throw new InvalidArgumentException(array('参数不正确%type%', array('%type%' => $config['type'])));
        }

        $class = $users[$config['type']]['class'];

        return new $class($config);
    }

    public static function users()
    {
        $users = array(
            'dingtalk' => array(
                'class' => 'CorporateTrainingBundle\Component\EIMClient\DingTalk\User',
            ),
        );

        return $users;
    }
}
