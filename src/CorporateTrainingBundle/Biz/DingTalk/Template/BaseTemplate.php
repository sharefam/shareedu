<?php

namespace CorporateTrainingBundle\Biz\DingTalk\Template;

use Codeages\Biz\Framework\Context\BizAware;
use Topxia\Service\Common\ServiceKernel;

abstract class BaseTemplate extends BizAware
{
    protected $linkTitle = '查看详情';

    protected $linkUrl = '';

    protected $markdownContent = ''; //可用mark的语法

    protected $title = '';

    protected $type = '';

    protected function makeSingleContent()
    {
        $link = 'dingtalk://dingtalkclient/page/link?url='.urlencode($this->linkUrl).'&pc_slide=false';

        return array(
            'msgtype' => 'action_card',
            'action_card' => array(
                'title' => $this->title,
                'markdown' => $this->markdownContent,
                'btn_orientation' => '0',
                'btn_json_list' => array(
                    'title' => $this->linkTitle,
                    'action_url' => $link,
                ),
            ),
            'image' => array(
                'media_id' => 'MEDIA_ID',
            ),
        );
    }

    protected function getBaseUrl()
    {
        $site = $this->getSettingService()->get('site', array());

        return empty($site['url']) ? '' : rtrim($site['url'], '/');
    }

    /**
     * @param $path
     * @param string $defaultKey
     * @param string $cdnType
     *
     * @return mixed|string
     */
    protected function getFileUrl($path, $defaultKey = '', $cdnType = 'default')
    {
        if (empty($path)) {
            if (empty($defaultKey)) {
                return '';
            }

            $defaultSetting = $this->getSettingService()->get('default', array());
            if (('course.png' == $defaultKey && !empty($defaultSetting['defaultCoursePicture'])) || 'avatar.png' == $defaultKey && !empty($defaultSetting['defaultAvatar']) && empty($defaultSetting[$defaultKey])) {
                $path = $defaultSetting[$defaultKey];
            } else {
                return $this->getBaseUrl().'/assets/img/default/'.$defaultKey;
            }
        }

        $path = str_replace('public://', '', $path);
        $path = str_replace('files/', '', $path);
        $files = 0 == strpos($path, '/') ? '/files' : '/files/';
        $path = $this->getBaseUrl().$files."{$path}";

        return $path;
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System:SettingService');
    }
}
