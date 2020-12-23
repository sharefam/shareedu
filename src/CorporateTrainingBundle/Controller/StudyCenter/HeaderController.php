<?php

namespace CorporateTrainingBundle\Controller\StudyCenter;

use CorporateTrainingBundle\Common\OrgToolkit;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\BaseController;

class HeaderController extends BaseController
{
    public function headerAction(Request $request, $userId)
    {
        $user = $this->getUserService()->getUser($userId);
        $user['profile'] = $this->getUserService()->getUserProfile($user['id']);
        $post = $this->getPostService()->getPost($user['postId']);

        $orgs = $this->getOrgService()->findOrgsByIds($user['orgIds']);
        $orgNames = OrgToolkit::buildOrgsNames($user['orgIds'], $orgs);
        $user['learnTime'] = $this->getTaskResultService()->sumLearnTimeByUserId($user['id']);

        $user['courseCount'] = $this->getCourseService()->countUserLearnCourse($user['id']);

        return $this->render('study-center/header.html.twig', array(
            'user' => $user,
            'post' => $post,
            'orgNames' => $orgNames,
        ));
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function getPostService()
    {
        return $this->createService('CorporateTrainingBundle:Post:PostService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityLearnLogService');
    }

    protected function getOrgService()
    {
        return $this->createService('Org:OrgService');
    }

    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }
}
