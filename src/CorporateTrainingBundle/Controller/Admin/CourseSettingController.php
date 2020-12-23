<?php

namespace CorporateTrainingBundle\Controller\Admin;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\Admin\BaseController;
use Biz\Content\Service\FileService;
use Biz\System\Service\SettingService;
use Symfony\Component\HttpFoundation\Request;

class CourseSettingController extends BaseController
{
    public function settingAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $courseSetting = $this->getSettingService()->get('course', array());

            $default = array(
                'welcome_message_enabled' => '0',
                'welcome_message_body' => '{{nickname}},欢迎加入课程{{course}}',
                'teacher_manage_marketing' => '0',
                'teacher_search_order' => '0',
                'teacher_manage_student' => '0',
                'teacher_export_student' => '0',
                'explore_default_orderBy' => 'latest',
                'free_course_nologin_view' => '1',
                'relatedCourses' => '0',
                'coursesPrice' => '0',
                'allowAnonymousPreview' => '1',
                'copy_enabled' => '0',
                'testpaperCopy_enabled' => '0',
                'custom_chapter_enabled' => '0',
            );

            $courseSetting = array_merge($default, $courseSetting);
            $fields = $request->request->all();
            $courseFieldsSetting = $fields['course_setting'];
            $questionFieldsSetting = $fields['question_setting'];

            $this->setCourseSetting($courseFieldsSetting, $courseSetting);
            $this->setQuestionSetting($questionFieldsSetting);

            $this->setFlashMessage('success', 'site.save.success');
        }

        $courseSetting = $this->getSettingService()->get('course', array());
        $courseDefaultSetting = $this->getSettingService()->get('course_default', array());
        $defaultSetting = $this->getSettingService()->get('default', array());

        return $this->render(
            'CorporateTrainingBundle::admin/course-set/course-setting.html.twig',
            array(
                'courseSetting' => $courseSetting,
                'courseDefaultSetting' => $courseDefaultSetting,
                'defaultSetting' => $defaultSetting,
                'hasOwnCopyright' => false,
            )
        );
    }

    public function defaultCoursePictureCropAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $options = $request->request->all();
            $data = $options['images'];

            $fileIds = ArrayToolkit::column($data, 'id');
            $files = $this->getFileService()->getFilesByIds($fileIds);

            $files = ArrayToolkit::index($files, 'id');
            $fileIds = ArrayToolkit::index($data, 'type');

            $setting = $this->getSettingService()->get('default', array());

            $oldAvatars = array(
                'course.png' => !empty($setting['course.png']) ? $setting['course.png'] : null,
            );

            $setting['defaultCoursePicture'] = 1;
            unset($setting['defaultCoursePictureFileName']);
            $setting['course.png'] = $files[$fileIds['course.png']['id']]['uri'];

            $this->getSettingService()->set('default', $setting);

            $fileService = $this->getFileService();
            array_map(function ($oldAvatar) use ($fileService) {
                if (!empty($oldAvatar)) {
                    $fileService->deleteFileByUri($oldAvatar);
                }
            }, $oldAvatars);

            return $this->redirect($this->generateUrl('admin_setting_course_setting'));
        }

        $fileId = $request->getSession()->get('fileId');
        list($pictureUrl, $naturalSize, $scaledSize) = $this->getFileService()->getImgFileMetaInfo($fileId, 480, 270);

        return $this->render(
            'CorporateTrainingBundle::admin/system/default-course-picture-crop.html.twig',
            array(
                'pictureUrl' => $pictureUrl,
                'naturalSize' => $naturalSize,
                'scaledSize' => $scaledSize,
            )
        );
    }

    protected function setCourseSetting($courseFieldsSetting, $courseSetting)
    {
        $courseDefaultSetting = array(
            'custom_chapter_enabled' => 0,
            'chapter_name' => '章',
            'part_name' => '节',
        );

        $courseDefaultSetting = array_merge($courseDefaultSetting, $courseFieldsSetting);
        $this->getSettingService()->set('course_default', $courseDefaultSetting);

        $default = $this->getSettingService()->get('default', array());
        $defaultSetting = array_merge($default, $courseDefaultSetting);
        $this->getSettingService()->set('default', $defaultSetting);

        $courseSetting = array_merge($courseSetting, $courseFieldsSetting);

        $this->getSettingService()->set('course', $courseSetting);
        $this->getLogService()->info('admin/system/', 'update_settings', '更新课程设置', $courseSetting);
    }

    protected function setQuestionSetting($questionsSetting)
    {
        $this->getSettingService()->set('questions', $questionsSetting);
        $this->getLogService()->info('admin/system/', 'questions_settings', '更新题库设置', $questionsSetting);
    }

    protected function getCourseDefaultSet()
    {
        return array(
            'defaultCoursePicture' => 0,
            'defaultCoursePictureFileName' => 'coursePicture',
            'articleShareContent' => '我正在看{{articletitle}}，关注{{sitename}}，分享知识，成就未来。',
            'courseShareContent' => '我正在学习{{course}}，收获巨大哦，一起来学习吧！',
            'groupShareContent' => '我在{{groupname}}小组,发表了{{threadname}},很不错哦,一起来看看吧!',
            'classroomShareContent' => '我正在学习{{classroom}}，收获巨大哦，一起来学习吧！',
            'chapter_name' => '章',
            'part_name' => '节',
        );
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return FileService
     */
    protected function getFileService()
    {
        return $this->createService('Content:FileService');
    }
}
