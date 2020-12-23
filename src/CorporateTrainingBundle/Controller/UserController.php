<?php

namespace CorporateTrainingBundle\Controller;

use AppBundle\Controller\UserController as BaseController;

class UserController extends BaseController
{
    public function headerBlockAction($user)
    {
        $userProfile = $this->getUserService()->getUserProfile($user['id']);
        $user = array_merge($user, $userProfile);

        if ($this->getCurrentUser()->isLogin()) {
            $isFollowed = $this->getUserService()->isFollowed($this->getCurrentUser()->id, $user['id']);
        } else {
            $isFollowed = false;
        }

        // 关注数
        $following = $this->getUserService()->findUserFollowingCount($user['id']);
        // 粉丝数
        $follower = $this->getUserService()->findUserFollowerCount($user['id']);
        if (in_array('ROLE_TEACHER', $user['roles'])) {
            $teacherProfile = $this->getTeacherProfileService()->getProfileByUserId($user['id']);
            $level = $this->getTeacherLevelService()->getLevel($teacherProfile['levelId']);
            if ($this->isPluginInstalled('Survey')) {
                $surveyScore = $this->getSurveyResultService()->getTeacherOnlineAndOfflineCoursesAverageSurveyScore($user['id']);
            }
        }

        return $this->render('user/header-block.html.twig', array(
            'user' => $user,
            'level' => isset($level) ? $level : '',
            'score' => isset($surveyScore) ? $surveyScore : '',
            'isFollowed' => $isFollowed,
            'following' => $following,
            'follower' => $follower,
        ));
    }

    protected function _aboutAction($user)
    {
        $userProfile = $this->getUserService()->getUserProfile($user['id']);

        if (in_array('ROLE_TEACHER', $user['roles'])) {
            $teacherProfile = $this->getTeacherProfileService()->getProfileByUserId($user['id']);
            $teacherProfessionFields = $this->getTeacherProfessionFieldService()->findTeacherProfessionFieldsByIds($teacherProfile['teacherProfessionFieldIds']);
        }

        return $this->render('user/about.html.twig', array(
            'user' => $user,
            'fields' => isset($teacherProfessionFields) ? $teacherProfessionFields : '',
            'userProfile' => $userProfile,
            'type' => 'about',
        ));
    }

    /**
     * @return \CorporateTrainingBundle\Biz\Teacher\Service\Impl\LevelServiceImpl
     */
    protected function getTeacherLevelService()
    {
        return $this->createService('CorporateTrainingBundle:Teacher:LevelService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\Teacher\Service\Impl\ProfileServiceImpl
     */
    protected function getTeacherProfileService()
    {
        return $this->createService('CorporateTrainingBundle:Teacher:ProfileService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\TeacherProfessionField\Service\Impl\TeacherProfessionFieldServiceImpl
     */
    protected function getTeacherProfessionFieldService()
    {
        return $this->createService('CorporateTrainingBundle:TeacherProfessionField:TeacherProfessionFieldService');
    }

    /**
     * @return \SurveyPlugin\Biz\Survey\Service\Impl\SurveyResultServiceImpl
     */
    protected function getSurveyResultService()
    {
        return $this->createService('SurveyPlugin:Survey:SurveyResultService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\OfflineCourse\Service\Impl\OfflineCourseServiceImpl
     */
    protected function getOfflineCourseService()
    {
        return $this->createService('CorporateTrainingBundle:OfflineCourse:OfflineCourseService');
    }
}
