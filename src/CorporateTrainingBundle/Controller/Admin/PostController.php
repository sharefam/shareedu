<?php

namespace CorporateTrainingBundle\Controller\Admin;

use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Biz\Post\Service\PostService;
use PostMapPlugin\Biz\Rank\Service\RankService;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\Admin\BaseController;
use Topxia\Service\Common\ServiceKernel;

class PostController extends BaseController
{
    public function indexAction(Request $request)
    {
        $postGroups = $this->getPostService()->searchPostGroups(array(), array('seq' => 'ASC'), 0, PHP_INT_MAX);
        $postGroups = $this->getPostService()->getPostStructureTree($postGroups);

        if ($this->isPluginInstalled('PostMap')) {
            $posts = array();
            foreach ($postGroups as $postGroup) {
                $posts = array_merge($posts, $postGroup['posts']);
            }
            $rankIds = ArrayToolkit::column($posts, 'rankId');
            $ranks = $this->getRankService()->findRanksByIds($rankIds);
            $ranks = ArrayToolkit::index($ranks, 'id');
        }

        return $this->render('admin/post/index.html.twig', array(
            'postGroups' => $postGroups,
            'ranks' => empty($ranks) ? null : $ranks,
        ));
    }

    public function createAction(Request $request, $groupId)
    {
        if ('POST' == $request->getMethod()) {
            $postNames = $request->request->get('postNames');
            $postNames = trim($postNames);
            $postNames = explode("\r\n", $postNames);
            $postNames = array_filter($postNames);

            $fields['groupId'] = $groupId;
            $user = $this->getCurrentUser();
            $fields['createdUserId'] = $user['id'];
            $post = $this->getPostService()->batchCreatePost($postNames, $fields);

            return $this->redirect($this->generateUrl('admin_post_manage'));
        }

        $post = array('id' => 0, 'name' => '', 'groupId' => $groupId);
        $postGroup = $this->getPostService()->getPostGroup($groupId);

        return $this->render('admin/post/batch-create-post-modal.html.twig', array(
            'post' => $post,
            'postGroup' => $postGroup,
        ));
    }

    public function editAction(Request $request, $id)
    {
        if ('POST' == $request->getMethod()) {
            $fields = $request->request->all();

            $post = $this->getPostService()->updatePost($id, $fields);

            return $this->redirect($this->generateUrl('admin_post_manage'));
        }

        $post = $this->getPostService()->getPost($id);
        $postGroup = $this->getPostService()->getPostGroup($post['groupId']);
        $rank = array();
        if ($this->isPluginInstalled('PostMap')) {
            $rank = $this->getRankService()->getRank($post['rankId']);
        }

        return $this->render('admin/post/post-modal.html.twig', array(
            'post' => $post,
            'postGroup' => $postGroup,
            'rank' => empty($rank) ? '' : json_encode($rank),
        ));
    }

    public function deleteAction(Request $request, $id)
    {
        $result = $this->getPostService()->checkPostCanDelete($id);

        if (!$result) {
            return $this->createJsonResponse(array('success' => $result, 'message' => ServiceKernel::instance()->trans('admin.post.message.check_delete_error')));
        }

        $result = $this->getPostService()->deletePost($id);

        if ($result) {
            $result = array('success' => $result, 'message' => ServiceKernel::instance()->trans('admin.post.message.delete_success'));
        } else {
            $result = array('success' => $result, 'message' => ServiceKernel::instance()->trans('admin.post.message.delete_error'));
        }

        return $this->createJsonResponse($result);
    }

    public function sortAction(Request $request)
    {
        $ids = $request->request->get('ids');

        if (!empty($ids)) {
            $this->getPostService()->sortPosts($ids);
        }

        return $this->createJsonResponse(true);
    }

    public function createGroupAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $postGroup = $request->request->all();
            $user = $this->getCurrentUser();
            $postGroup['createdUserId'] = $user['id'];
            $postGroup = $this->getPostService()->createPostGroup($postGroup);

            return $this->redirect($this->generateUrl('admin_post_manage'));
        }

        return $this->render('admin/post/post-group-modal.html.twig');
    }

    public function editGroupAction(Request $request, $id)
    {
        if ('POST' == $request->getMethod()) {
            $fields = $request->request->all();
            $postGroup = $this->getPostService()->updatePostGroup($id, $fields);

            return $this->redirect($this->generateUrl('admin_post_manage'));
        }

        $postGroup = $this->getPostService()->getPostGroup($id);

        return $this->render('admin/post/post-group-modal.html.twig', array(
            'postGroup' => $postGroup,
        ));
    }

    public function deleteGroupAction(Request $request, $id)
    {
        $result = $this->getPostService()->checkPostGroupCanDelete($id);

        if (!$result) {
            return $this->createJsonResponse(array('success' => false, 'message' => ServiceKernel::instance()->trans('admin.post.group.message.check_delete_error')));
        }

        $result = $this->getPostService()->deletePostGroup($id);

        if ($result) {
            $result = array('success' => $result, 'message' => ServiceKernel::instance()->trans('admin.post.group.message.delete_success'));
        } else {
            $result = array('success' => $result, 'message' => ServiceKernel::instance()->trans('admin.post.group.message.delete_error'));
        }

        return $this->createJsonResponse($result);
    }

    public function sortGroupAction(Request $request)
    {
        $ids = $request->request->get('ids');

        if (!empty($ids)) {
            $this->getPostService()->sortPostGroups($ids);
        }

        return $this->createJsonResponse(true);
    }

    public function checkNameAction(Request $request)
    {
        $name = $request->query->get('value');
        $exclude = $request->query->get('exclude');
        $available = $this->getPostService()->isPostNameAvailable($name, $exclude);

        if ($available) {
            $result = array(
                'success' => true,
                'message' => '',
            );
        } else {
            $result = array(
                'success' => false,
                'message' => ServiceKernel::instance()->trans('admin.post.message.post_exist'),
            );
        }

        return $this->createJsonResponse($result);
    }

    public function batchCreateCheckNameAction(Request $request)
    {
        $postNames = $request->query->get('value');
        $postNames = trim($postNames);
        $postNames = explode("\n", $postNames);
        $postNames = array_filter($postNames);
        $existNames = '';
        foreach ($postNames as $postName) {
            $available = $this->getPostService()->isPostNameAvailable($postName, '');

            if (!$available) {
                $existNames .= $postName.',';
            }
        }

        if (empty($existNames)) {
            $response = array('success' => true, 'message' => '');
        } else {
            $response = array('success' => false, 'message' => ServiceKernel::instance()->trans('admin.post.message.batch_create_check_name', array('%existNames%' => $existNames)));
        }

        return $this->createJsonResponse($response);
    }

    public function checkGroupNameAction(Request $request)
    {
        $name = $request->query->get('value');
        $exclude = $request->query->get('exclude');
        $available = $this->getPostService()->isPostGroupNameAvailable($name, $exclude);

        if ($available) {
            $result = array(
                'success' => true,
                'message' => '',
            );
        } else {
            $result = array(
                'success' => false,
                'message' => ServiceKernel::instance()->trans('admin.post.message.post_group_exist'),
            );
        }

        return $this->createJsonResponse($result);
    }

    public function checkCodeAction(Request $request)
    {
        $code = $request->query->get('value');
        $exclude = $request->query->get('exclude');
        $isAvailable = $this->getPostService()->isPostCodeAvailable($code, $exclude);

        if ($isAvailable) {
            $response = array('success' => true, 'message' => '');
        } else {
            $response = array('success' => false, 'message' => ServiceKernel::instance()->trans('admin.post.message.code_exist'));
        }

        return $this->createJsonResponse($response);
    }

    /**
     * @return PostService
     */
    protected function getPostService()
    {
        return $this->createService('CorporateTrainingBundle:Post:PostService');
    }

    /**
     * @return RankService
     */
    protected function getRankService()
    {
        return $this->createService('PostMapPlugin:Rank:RankService');
    }
}
