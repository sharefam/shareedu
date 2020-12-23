<?php

namespace CorporateTrainingBundle\Biz\Mail;

use Biz\CloudPlatform\CloudAPIFactory;
use Biz\Mail\CloudMail;

class NotificationCloudMail extends CloudMail
{
    public function doSend()
    {
        $cloudConfig = $this->setting('cloud_email_crm', array());

        if (isset($cloudConfig['status']) && $cloudConfig['status'] == 'enable') {
            $options = $this->options;
            $template = $this->parseTemplate($options['template']);
            $format = isset($options['format']) && $options['format'] == 'html' ? 'html' : 'text';
            $params = array(
                'to' => implode(',', $this->to),
                'title' => $template['title'],
                'body' => $template['body'],
                'format' => $format,
                'template' => 'email_default',
                'sourceFrom' => empty($options['sourceFrom']) ? '' : $options['sourceFrom'],
                'type' => 'market',
            );

            if (!empty($options['sendedSn'])) {
                $params['sendedSn'] = $options['sendedSn'];
            }
            $api = CloudAPIFactory::create('root');
            $result = $api->post('/emails', $params);

            return empty($result['sendedSn']) ? false : true;
        }

        return false;
    }

    protected function mailCheckRatelimiter()
    {
    }
}
