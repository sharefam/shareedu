<?php

namespace CorporateTrainingBundle\Biz\Post\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use CorporateTrainingBundle\Biz\Post\Service\PostService;
use CorporateTrainingBundle\Biz\Post\Util\ChineseFirstCharter;

class PostServiceImpl extends BaseService implements PostService
{
    /**
     *  Post API.
     */
    public function createPost($fields)
    {
        $this->validatePostFields($fields);

        if ($this->getPostByCode($fields['code'])) {
            throw $this->createServiceException('Post already exists');
        }

        $seq = $this->countPosts(array('groupId' => $fields['groupId']));
        $fields['seq'] = ++$seq;
        $fields = $this->filterPostFields($fields);

        return $this->getPostDao()->create($fields);
    }

    public function batchCreatePost($postNames, $fields)
    {
        $chineseFirstCharter = new ChineseFirstCharter();
        foreach ($postNames as $name) {
            $post = $this->getPostByName($name);
            if (empty($post)) {
                $fields['name'] = $name;
                $fields['code'] = $chineseFirstCharter->getFirstCharters($name);
                $count = $this->countPosts(array('code' => $fields['code']));
                if ($count >= 1) {
                    $fields['code'] = $fields['code'].'repeat';
                    $post = $this->createPost($fields);
                    $post['code'] = str_replace('repeat', $post['id'], $post['code']);
                    $this->updatePost($post['id'], $post);
                } else {
                    $this->createPost($fields);
                }
            }
        }
    }

    public function updatePost($id, $fields)
    {
        $this->checkPostExist($id);
        $this->validatePostFields($fields);
        $fields = $this->filterPostFields($fields);

        return $this->getPostDao()->update($id, $fields);
    }

    public function deletePost($id)
    {
        $this->checkPostExist($id);
        $result = $this->getPostDao()->delete($id);

        if ($result) {
            $post = $this->getPost($id);
            $this->updatePostSeq($post['seq'], $post['groupId']);
            $this->deleteAssignedPostCourse($id);
        }

        return $result;
    }

    public function getPost($id)
    {
        return $this->getPostDao()->get($id);
    }

    public function getPostByName($name)
    {
        return $this->getPostDao()->getByName($name);
    }

    public function getPostByCode($code)
    {
        return $this->getPostDao()->getByCode($code);
    }

    public function getAllPosts()
    {
        return $this->getPostDao()->findAll();
    }

    public function getPostByGroupIdAndRankId($groupId, $rankId)
    {
        return $this->getPostDao()->getByGroupIdAndRankId($groupId, $rankId);
    }

    public function getPostStructureTree($groups)
    {
        foreach ($groups as $key => $group) {
            $posts = $this->findPostsByGroupId($group['id']);
            $groups[$key]['posts'] = $posts;
        }

        return $groups;
    }

    public function getPostTree()
    {
        $tree = $this->makeTree();

        return $tree;
    }

    public function makeTree()
    {
        $parentTree = array();
        $posts = $this->getAllPosts();
        if (!empty($posts)) {
            $postGroups = ArrayToolkit::group($posts, 'groupId');
            $groups = $this->searchPostGroups(array('ids' => ArrayToolkit::column($posts, 'groupId')), array('seq' => 'ASC'), 0, PHP_INT_MAX);
            $groups = ArrayToolkit::index($groups, 'id');
            foreach ($groups as $group) {
                $parentTree[] = array('id' => 'group'.$group['id'], 'name' => $group['name'], 'selectable' => false, 'nodes' => $this->getChildrenTree($postGroups[$group['id']]));
            }
        }

        return $parentTree;
    }

    public function getChildrenTree($posts)
    {
        foreach ($posts as &$post) {
            $post['nodes'] = array();
        }

        return $posts;
    }

    public function findPostsByGroupId($groupId)
    {
        return $this->getPostDao()->findByGroupId($groupId);
    }

    public function findPostsByIds($ids)
    {
        return $this->getPostDao()->findByIds($ids);
    }

    public function findPostsBySeqAndGroupId($seq, $groupId)
    {
        return $this->getPostDao()->findBySeqAndGroupId($seq, $groupId);
    }

    public function countPosts($conditions)
    {
        return $this->getPostDao()->count($conditions);
    }

    public function searchPosts($conditions, $orderBy, $start, $limit)
    {
        return $this->getPostDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function sortPosts($ids)
    {
        foreach ($ids as $index => $id) {
            $this->getPostDao()->update($id, array('seq' => $index + 1));
        }
    }

    public function isPostNameAvailable($name, $exclude)
    {
        if (empty($name)) {
            return false;
        }

        if ($name == $exclude) {
            return true;
        }

        $post = $this->getPostByName($name);

        return $post ? false : true;
    }

    public function checkPostCanDelete($postId)
    {
        $result = false;
        $count = $this->getUserService()->countUsers(array('postId' => $postId));

        if ($count <= 0) {
            $result = true;
        }

        return $result;
    }

    /**
     * PostGroup API.
     */
    public function createPostGroup($fields)
    {
        $this->validatePostGroupFields($fields);

        if ($this->getPostGroupByName($fields['name'])) {
            throw $this->createServiceException('Post Group already exists');
        }

        $seq = $this->searchPostGroupCount(array());
        $fields['seq'] = ++$seq;
        $fields = $this->filterPostGroupFields($fields);

        return $this->getPostGroupDao()->create($fields);
    }

    public function updatePostGroup($id, $fields)
    {
        $this->checkPostGroupExist($id);
        $this->validatePostGroupFields($fields);
        $fields = $this->filterPostGroupFields($fields);

        return $this->getPostGroupDao()->update($id, $fields);
    }

    public function deletePostGroup($id)
    {
        $this->checkPostGroupExist($id);
        $postGroup = $this->getPostGroup($id);

        return $this->getPostGroupDao()->delete($postGroup['id']);
    }

    public function getPostGroup($id)
    {
        return $this->getPostGroupDao()->get($id);
    }

    public function getPostGroupByName($name)
    {
        return $this->getPostGroupDao()->getByName($name);
    }

    public function findPostGroupByIds($ids)
    {
        return $this->getPostGroupDao()->findByIds($ids);
    }

    public function searchPostGroupCount($conditions)
    {
        return $this->getPostGroupDao()->count($conditions);
    }

    public function searchPostGroups($conditions, $orderBy, $start, $limit)
    {
        return $this->getPostGroupDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function sortPostGroups($ids)
    {
        foreach ($ids as $index => $id) {
            $this->getPostGroupDao()->update($id, array('seq' => $index + 1));
        }
    }

    public function isPostGroupNameAvailable($name, $exclude)
    {
        if (empty($name)) {
            return false;
        }

        if ($name == $exclude) {
            return true;
        }

        $postGroup = $this->getPostGroupByName($name);

        return $postGroup ? false : true;
    }

    public function isPostCodeAvailable($code, $exclude)
    {
        $post = $this->getPostDao()->getPostByCode($code);

        if (empty($post)) {
            return true;
        }

        return $post['code'] === $exclude;
    }

    public function checkPostGroupCanDelete($postGroupId)
    {
        $result = false;
        $posts = $this->findPostsByGroupId($postGroupId);

        if (empty($posts)) {
            $result = true;
        }

        return $result;
    }

    protected function updatePostSeq($seq, $postGroupId)
    {
        $posts = $this->findPostsBySeqAndGroupId($seq, $postGroupId);

        foreach ($posts as $post) {
            $this->updatePost($post['id'], array('seq' => --$post['seq']));
        }
    }

    protected function deleteAssignedPostCourse($postId)
    {
        $postCourses = $this->getPostCourseService()->findPostCoursesByPostId($postId);

        if (!empty($postCourses)) {
            foreach ($postCourses as $postCourse) {
                $this->getPostCourseService()->deletePostCourse($postCourse['id']);
            }
        }
    }

    protected function validatePostFields($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('name', 'code'), true)) {
            throw $this->createInvalidArgumentException('Lack of required fields');
        }
    }

    protected function validatePostGroupFields($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('name'), true)) {
            throw $this->createInvalidArgumentException('Lack of required fields');
        }
    }

    protected function filterPostFields($fields)
    {
        return ArrayToolkit::parts($fields, array('name', 'groupId', 'seq', 'code', 'createdUserId', 'rankId', 'visible', 'description'));
    }

    protected function filterPostGroupFields($fields)
    {
        return ArrayToolkit::parts($fields, array('name', 'seq', 'createdUserId', 'visible', 'rankGroupIds'));
    }

    protected function checkPostExist($id)
    {
        $post = $this->getPost($id);

        if (empty($post)) {
            throw $this->createNotFoundException("Post#{$id} Not Exist");
        }
    }

    protected function checkPostGroupExist($id)
    {
        $postGroup = $this->getPostGroup($id);

        if (empty($postGroup)) {
            throw $this->createNotFoundException("PostGroup#{$id} Not Exist");
        }
    }

    protected function getPostDao()
    {
        return $this->biz->dao('CorporateTrainingBundle:Post:PostDao');
    }

    protected function getPostGroupDao()
    {
        return $this->biz->dao('CorporateTrainingBundle:Post:PostGroupDao');
    }

    protected function getPostCourseService()
    {
        return $this->biz->service('CorporateTrainingBundle:PostCourse:PostCourseService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
