<?php

namespace CorporateTrainingBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\GroupController as BaseController;

class GroupController extends BaseController
{
    public function sidebarAction(Request $request)
    {
        list($user, $myJoinGroup, $newGroups) = $this->_getGroupList();

        return $this->render('group/group-sidebar.html.twig', array(
                'user' => $user,
                'myJoinGroup' => $myJoinGroup,
                'newGroups' => $newGroups,
            )
        );
    }
}
