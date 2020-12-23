<?php

namespace AppBundle\Controller\Admin;

use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseService;
use Biz\Sms\Service\SmsService;
use Biz\System\Service\SettingService;
use AppBundle\Common\SmsToolkit;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\StringToolkit;
use Symfony\Component\HttpFoundation\Request;
use Biz\CloudPlatform\CloudAPIFactory;

class SmsController extends BaseController
{
    public function prepareAction(Request $request, $targetType, $id)
    {
        $item = array();
        $mobileNum = 0;
        $mobileNeedVerified = false;
        $url = '';
        $smsType = 'sms_'.$targetType.'_publish';
        $smsInfo = $this->getCloudSmsInfo();

        if ('classroom' == $targetType) {
            $item = $this->getClassroomService()->getClassroom($id);
            $mobileNum = $this->getUserService()->countUserHasMobile($mobileNeedVerified);
            $url = $this->generateUrl('classroom_show', array('id' => $id));
        } elseif ('course' == $targetType) {
            $item = $this->getCourseSetService()->getCourseSet($id);
            $url = $this->generateUrl('course_show', array('id' => $item['defaultCourseId']));

            $mobileNum = $this->getUserService()->countUserHasMobile($mobileNeedVerified);
        }

        $item['title'] = StringToolkit::cutter($item['title'], 20, 15, 4);

        return $this->render('admin/sms/sms-send.html.twig', array(
            'item' => $item,
            'targetType' => $targetType,
            'url' => $url,
            'count' => $mobileNum,
            'index' => 1,
            'isOpen' => $this->getSmsService()->isOpen($smsType),
            'smsInfo' => $smsInfo,
        ));
    }

    public function sendAction(Request $request, $targetType, $id)
    {
        $smsType = 'sms_'.$targetType.'_publish';
        $index = $request->query->get('index');
        $onceSendNum = 100;
        $url = $request->query->get('url');
        $count = $request->query->get('count');
        $parameters = array();
        $mobileNeedVerified = false;
        $description = '';
        $courseSet = array();

        if ('classroom' == $targetType) {
            $classroom = $this->getClassroomService()->getClassroom($id);
            $classroomSetting = $this->getSettingService()->get('classroom');
            $classroomName = isset($classroomSetting['name']) ? $classroomSetting['name'] : '班级';
            $classroom['title'] = StringToolkit::cutter($classroom['title'], 20, 15, 4);
            $parameters['classroom_title'] = $classroomName.'：《'.$classroom['title'].'》';
            $description = $parameters['classroom_title'].'发布';
            $students = $this->getUserService()->findUsersHasMobile($index, $onceSendNum, $mobileNeedVerified);
        } elseif ('course' == $targetType) {
            $courseSet = $this->getCourseSetService()->getCourseSet($id);
            $courseSet['title'] = StringToolkit::cutter($courseSet['title'], 20, 15, 4);
            $parameters['course_title'] = '课程'.'：《'.$courseSet['title'].'》';
            $description = $parameters['course_title'].'发布';
            $students = $this->getUserService()->findUsersHasMobile($index, $onceSendNum, $mobileNeedVerified);
        }

        if (!$this->getSmsService()->isOpen($smsType)) {
            throw new \RuntimeException('请先开启相关设置!');
        }

        $parameters['url'] = $url.' ';
        if (!empty($students)) {
            $studentIds = ArrayToolkit::column($students, 'id');

            $this->getSmsService()->smsSend($smsType, $studentIds, $description, $parameters);
        }

        if ($count > $index + $onceSendNum) {
            return $this->createJsonResponse(array('index' => $index + $onceSendNum, 'process' => intval(($index + $onceSendNum) / $count * 100)));
        } else {
            return $this->createJsonResponse(array('status' => 'success', 'process' => 100));
        }
    }

    public function changeLinkAction(Request $request)
    {
        $url = $request->getHost();
        $url .= $request->query->get('url');

        $shortUrl = SmsToolkit::getShortLink($url);
        $url = empty($shortUrl) ? 'http://'.$url : $shortUrl;

        return $this->createJsonResponse(array('url' => $url.' '));
    }

    private function getCloudSmsInfo()
    {
        $api = CloudAPIFactory::create('root');
        $smsInfo = $api->get('/me/sms_account');

        return $smsInfo;
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    /**
     * @return SmsService
     */
    protected function getSmsService()
    {
        return $this->getBiz()->service('Sms:SmsService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->getBiz()->service('Classroom:ClassroomService');
    }
}
