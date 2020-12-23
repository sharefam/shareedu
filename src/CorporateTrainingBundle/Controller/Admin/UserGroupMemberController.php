<?php

namespace CorporateTrainingBundle\Controller\Admin;

use CorporateTrainingBundle\Biz\UserAttribute\Service\UserAttributeService;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\Paginator;
use AppBundle\Controller\Admin\BaseController;
use Topxia\Service\Common\ServiceKernel;

class UserGroupMemberController extends BaseController
{
    public function listAction(Request $request, $id)
    {
        $conditions['groupId'] = $id;

        $paginator = new Paginator(
            $request,
            $this->getUserGroupMemberService()->countUserGroupMembers($conditions),
            20
        );

        $userGroupMembers = $this->getUserGroupMemberService()->searchUserGroupMembers(
            $conditions,
            array('id' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        foreach ($userGroupMembers as &$userGroupMember) {
            $userGroupMember['num'] = $this->getUserGroupMemberService()->countUserGroupMembersByMemberTypeAndMemberId($userGroupMember['memberType'], $userGroupMember['memberId']);
        }

        return $this->render('admin/user-group-member/list.html.twig', array(
            'userGroupId' => $id,
            'userGroupMembers' => $userGroupMembers,
            'paginator' => $paginator,
        ));
    }

    public function addAction(Request $request, $id)
    {
        if ('POST' == $request->getMethod()) {
            $data = $request->request->all();
            $userGroupMembers = json_decode($data['userGroups'], true);

            if (empty($userGroupMembers)) {
                return $this->redirect(
                    $this->generateUrl(
                        'admin_user_group_member_list',
                        array(
                            'id' => $id,
                        )
                    )
                );
            }

            foreach ($userGroupMembers as &$userGroupMember) {
                $userGroupMember['groupId'] = $id;
                $userGroupMember['memberType'] = $userGroupMember['attributeType'];
                unset($userGroupMember['name'], $userGroupMember['id'], $userGroupMember['attributeType']);
                $member = $this->getUserGroupMemberService()->isMemberExistInUserGroup($id, $userGroupMember['memberId'], $userGroupMember['memberType']);
                if (!$member) {
                    $this->getUserGroupMemberService()->addUserGroupMember($userGroupMember);
                }
            }

            return $this->redirect(
                $this->generateUrl(
                    'admin_user_group_member_list',
                    array(
                        'id' => $id,
                    )
                )
            );
        }
    }

    public function deleteAction(Request $request, $id)
    {
        $result = $this->getUserGroupMemberService()->deleteUserGroupMember($id);

        if ($result) {
            $result = array('success' => $result, 'message' => ServiceKernel::instance()->trans('admin.user_group_member.message.delete_success'));
        } else {
            $result = array('success' => $result, 'message' => ServiceKernel::instance()->trans('admin.user_group_member.message.delete_error'));
        }

        return $this->createJsonResponse($result);
    }

    public function matchAction(Request $request)
    {
        $queryString = $request->query->get('q');
        $attributes = array('user', 'org', 'post');
        $name = $this->getUserAttributeService()->searchAttributesName($attributes, $queryString);

        return $this->createJsonResponse($name);
    }

    protected function getUserGroupMemberService()
    {
        return $this->createService('CorporateTrainingBundle:UserGroup:MemberService');
    }

    protected function getTagService()
    {
        return $this->createService('Taxonomy:TagService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    public function getOrgService()
    {
        return $this->createService('Org:OrgService');
    }

    protected function getPostService()
    {
        return $this->createService('CorporateTrainingBundle:Post:PostService');
    }

    /**
     * @return UserAttributeService
     */
    protected function getUserAttributeService()
    {
        return $this->createService('CorporateTrainingBundle:UserAttribute:UserAttributeService');
    }
}
