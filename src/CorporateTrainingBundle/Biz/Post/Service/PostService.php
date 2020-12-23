<?php

namespace CorporateTrainingBundle\Biz\Post\Service;

interface PostService
{
    /**
     *  Post API.
     */
    public function createPost($fields);

    public function batchCreatePost($postNames, $post);

    public function updatePost($id, $fields);

    public function deletePost($id);

    public function getPost($id);

    public function getPostByName($name);

    public function getPostByCode($code);

    public function getAllPosts();

    public function getPostByGroupIdAndRankId($groupId, $rankId);

    public function getPostStructureTree($groups);

    public function findPostsByGroupId($groupId);

    public function findPostsByIds($ids);

    public function findPostsBySeqAndGroupId($seq, $groupId);

    public function countPosts($conditions);

    public function searchPosts($conditions, $orderBy, $start, $limit);

    public function sortPosts($ids);

    public function isPostNameAvailable($name, $exclude);

    public function checkPostCanDelete($id);

    /**
     * PostGroup API.
     */
    public function createPostGroup($fields);

    public function updatePostGroup($id, $fields);

    public function deletePostGroup($id);

    public function getPostGroup($id);

    public function findPostGroupByIds($ids);

    public function searchPostGroupCount($conditions);

    public function searchPostGroups($conditions, $orderBy, $start, $limit);

    public function sortPostGroups($ids);

    public function isPostGroupNameAvailable($name, $exclude);

    public function isPostCodeAvailable($code, $exclude);

    public function checkPostGroupCanDelete($id);
}
