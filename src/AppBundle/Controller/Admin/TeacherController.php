<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use Symfony\Component\HttpFoundation\Request;

class TeacherController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = $request->query->all();
        $conditions = $this->fillOrgCode($conditions);
        $conditions['roles'] = 'ROLE_TEACHER';
        $paginator = new Paginator(
            $this->get('request'),
            $this->getUserService()->countUsers($conditions),
            20
        );

        $users = $this->getUserService()->searchUsers(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('admin/teacher/index.html.twig', array(
            'users' => $users,
            'paginator' => $paginator,
        ));
    }

    public function promoteAction(Request $request, $id)
    {
        $promotedUser = $this->getUserService()->searchUsers(
            array(
                'roles' => 'ROLE_TEACHER',
                'promoted' => 1,
            ),
            array('promotedSeq' => 'DESC'),
            0,
            1
        );
        $number = empty($promotedUser) ? 1 : $promotedUser[0]['promotedSeq'] + 1;
        $user = $this->getUserService()->promoteUser($id, $number);
        $teacherProfile = $this->getProfileService()->getProfileByUserId($user['id']);

        return $this->render('admin/teacher/tr.html.twig', array('user' => $user, 'teacherProfile' => $teacherProfile));
    }

    public function promoteListAction(Request $request)
    {
        $user = $this->getUser();
        $fields = $request->query->all();
        $conditions = array(
            'roles' => 'ROLE_TEACHER',
            'promoted' => 1,
        );
        $conditions = array_merge($conditions, $fields);
        $conditions = $this->fillOrgCode($conditions);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getUserService()->countUsers($conditions),
            20
        );

        $users = $this->getUserService()->searchUsers(
            $conditions,
            array('promotedSeq' => 'ASC', 'promotedTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('admin/teacher/teacher-promote-list.html.twig', array(
            'users' => $users,
            'paginator' => $paginator,
        ));
    }

    public function promoteCancelAction(Request $request, $id)
    {
        $user = $this->getUserService()->cancelPromoteUser($id);
        $teacherProfile = $this->getProfileService()->getProfileByUserId($user['id']);
        $promoteUsers = $this->getUserService()->searchUsers(
            array('roles' => 'ROLE_TEACHER', 'promoted' => 1),
            array('promotedSeq' => 'ASC'),
            0,
            PHP_INT_MAX
        );
        $userIds = ArrayToolkit::column($promoteUsers, 'id');
        $this->getUserService()->sortPromoteUser($userIds);

        return $this->render('admin/teacher/tr.html.twig', array('user' => $user, 'teacherProfile' => $teacherProfile));
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\Teacher\Service\Impl\ProfileServiceImpl
     */
    protected function getProfileService()
    {
        return $this->createService('CorporateTrainingBundle:Teacher:ProfileService');
    }
}
