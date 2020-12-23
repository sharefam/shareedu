<?php

namespace CorporateTrainingBundle\Controller;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use PostMapPlugin\Biz\Rank\Service\RankService;

class AdvancedMemberSelectController extends BaseController
{
    public function showAction(Request $request, $targetType, $targetId)
    {
        $selector = $this->getMemberSelector($targetType);

        if (!$selector->canSelect($targetId)) {
            throw $this->createAccessDeniedException();
        }

        $postGroups = $this->buildPost();
        $userGroups = $this->buildUserGroup();
        if ($this->isPluginInstalled('PostMap')) {
            $postRanks = $this->buildPostRank();
        }

        $conditions = $this->prepareUserSearchConditions(array());
        $count = $this->getUserService()->countUsers($conditions);

        $paginator = new Paginator(
            $request,
            $count,
            5
        );
        $users = $this->getUserService()->searchUsers(
            $conditions,
            array('id' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $paginator->setBaseUrl($this->generateUrl('advanced_member_select_user_search'));

        return $this->render(
            'advanced-user-select/advanced-user-select-modal.html.twig',
            array(
                'postRanks' => empty($postRanks) ? array() : $postRanks,
                'postGroups' => $postGroups,
                'userGroups' => $userGroups,
                'users' => $users,
                'paginator' => $paginator,
                'targetType' => $targetType,
                'targetId' => $targetId,
                'orgIds' => implode(',', $conditions['orgIds']),
            )
        );
    }

    public function userSearchAction(Request $request)
    {
        $fields = $request->request->all();
        $conditions = $this->prepareUserSearchConditions($fields);

        $count = $this->getUserService()->countUsers($conditions);

        $paginator = new Paginator(
            $request,
            $count,
            5
        );
        $users = $this->getUserService()->searchUsers(
            $conditions,
            array('id' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render(
            'advanced-user-select/widgets/user-list.html.twig',
            array(
                'users' => $users,
                'paginator' => $paginator,
            )
        );
    }

    public function convertUserAttributeAction(Request $request)
    {
        $jsonData = $request->request->get('userAttribute', array());
        $data = json_decode($jsonData, true);
        $attributes = $this->buildAttributes($data);
        $orgIds = $this->getCurrentUser()->getManageOrgIdsRecursively();
        $userIds = $this->getUserAttributeService()->findDistinctUserIdsByAttributes($attributes, $orgIds);
        $userIds = array_values($userIds);

        return $this->createJsonResponse(array('userIds' => $userIds));
    }

    public function batchAddMemberAction(Request $request, $targetType, $targetId)
    {
        $selector = $this->getMemberSelector($targetType);
        $userIds = $request->request->get('userIds', array());
        $notificationSetting = (int) $request->request->get('notificationSetting', 0);
        $status = false;
        $result = $selector->selectUserIds($targetId, $userIds, $notificationSetting);
        if (!empty($result)) {
            $status = true;
        }

        return $this->createJsonResponse(array('status' => $status));
    }

    public function chooseAllUserAction(Request $request)
    {
        $fields = $request->request->all();
        $conditions = $this->prepareUserSearchConditions($fields);
        $users = $this->getUserService()->searchUsers(
            $conditions,
            array('id' => 'DESC'),
            0,
            PHP_INT_MAX
        );

        $userProfiles = $this->getUserService()->findUserProfilesByIds(ArrayToolkit::column($users, 'id'));
        $userProfiles = ArrayToolkit::index($userProfiles, 'id');
        $userAttributes = array();
        if (!empty($users)) {
            foreach ($users as $user) {
                $userAttributes[] = array(
                    'id' => $user['id'],
                    'name' => empty($userProfiles[$user['id']]['truename']) ? $user['nickname'] : $userProfiles[$user['id']]['truename'],
                );
            }
        }

        return $this->createJsonResponse($userAttributes);
    }

    protected function buildPost()
    {
        $postGroups = $this->getPostService()->searchPostGroups(
            array(),
            array('seq' => 'ASC'),
            0,
            PHP_INT_MAX
        );

        return $this->getPostService()->getPostStructureTree($postGroups);
    }

    protected function buildPostRank()
    {
        $posts = $this->getPostService()->searchPosts(array(), array(), 0, PHP_INT_MAX);
        $rankIds = ArrayToolkit::column($posts, 'rankId');

        $postRanks = $this->getPostRankService()->searchRanks(
            array('ids' => $rankIds),
            array('groupId' => 'ASC', 'seq' => 'ASC'),
            0,
            PHP_INT_MAX
        );

        foreach ($postRanks as $key => $rank) {
            $posts = $this->getPostService()->searchPosts(array('rankId' => $rank['id']), array(), 0, PHP_INT_MAX);
            $postRanks[$key]['posts'] = $posts;
        }

        return $postRanks;
    }

    protected function buildUserGroup()
    {
        return $this->getUserGroupService()->searchUserGroups(
            array(),
            array('id' => 'ASC'),
            0,
            PHP_INT_MAX
        );
    }

    protected function buildAttributes($fields)
    {
        $attributes = array();

        foreach ($fields as $field) {
            $type = $field['type'];

            if (in_array($type, array('org', 'user', 'post', 'userGroup')) && !empty($field['id'])) {
                $attributes[] = array(
                    'attributeType' => $type,
                    'attributeId' => $field['id'],
                );
            }
        }

        return $attributes;
    }

    protected function prepareOrgIds($conditions)
    {
        if (!isset($conditions['orgIds'])) {
            return $this->getCurrentUser()->getManageOrgIdsRecursively();
        }

        return explode(',', $conditions['orgIds']);
    }

    protected function findUserIdsByOrgIds($orgIds)
    {
        $usersOrg = $this->getUserOrgService()->findUserOrgsByOrgIds($orgIds);

        return ArrayToolkit::column($usersOrg, 'userId');
    }

    protected function prepareUserSearchConditions($fields)
    {
        $conditions = array(
            'noType' => 'system',
            'locked' => 0,
        );

        if (!empty($fields['keyword']) && !empty($fields['keywordType'])) {
            $conditions[$fields['keywordType']] = $fields['keyword'];
        }

        if (!empty($fields['postId'])) {
            $conditions['postId'] = $fields['postId'];
        }

        $orgIds = $this->prepareOrgIds($fields);
        $userIds = $this->findUserIdsByOrgIds($orgIds);

        $conditions['userIds'] = empty($userIds) ? array(-1) : $userIds;
        $conditions['orgIds'] = $orgIds;

        if (!empty($fields['hireDate_GTE'])) {
            $conditions['hireDate_GTE'] = strtotime($fields['hireDate_GTE']);
        }

        if (!empty($fields['hireDate_LTE'])) {
            $conditions['hireDate_GTE'] = strtotime($fields['hireDate_GTE']);
        }

        return $conditions;
    }

    protected function getMemberSelector($targetType)
    {
        $memberSelectFactory = $this->getBiz()->offsetGet('advanced_member_select_factory');

        return  $memberSelectFactory->create($targetType);
    }

    /**
     * @return \CorporateTrainingBundle\Biz\Post\Service\Impl\PostServiceImpl
     */
    protected function getPostService()
    {
        return $this->createService('CorporateTrainingBundle:Post:PostService');
    }

    /**
     * @return RankService
     */
    protected function getPostRankService()
    {
        return $this->createService('PostMapPlugin:Rank:RankService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\UserGroup\Service\Impl\UserGroupServiceImpl
     */
    protected function getUserGroupService()
    {
        return $this->createService('CorporateTrainingBundle:UserGroup:UserGroupService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\UserAttribute\Service\Impl\UserAttributeServiceImpl
     */
    protected function getUserAttributeService()
    {
        return $this->createService('CorporateTrainingBundle:UserAttribute:UserAttributeService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\User\Service\Impl\UserOrgServiceImpl
     */
    protected function getUserOrgService()
    {
        return $this->createService('CorporateTrainingBundle:User:UserOrgService');
    }
}
