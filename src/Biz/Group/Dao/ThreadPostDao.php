<?php

namespace Biz\Group\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ThreadPostDao extends GeneralDaoInterface
{
    public function searchPostsThreadIds($conditions, $orderBy, $start, $limit);

    public function countPostsThreadIds($conditions);

    public function countPostsGroupByUserId($conditions);

    public function deleteByThreadId($threadId);

    public function findByPostId($postId);

    public function findByThreadId($threadId);

    public function deleteByPostId($postId);
}
