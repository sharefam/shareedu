<?php

namespace CorporateTrainingBundle\Biz\Exporter;

class PostListExporter extends AbstractExporter
{
    public function canExport($parameters)
    {
        $biz = $this->biz;
        $user = $biz['user'];

        return $user->hasPermission('admin_post');
    }

    public function getExportFileName()
    {
        return 'post.xls';
    }

    public function getSortedHeadingRow($parameters)
    {
        return array(
            array('code' => 'name', 'title' => $this->trans('admin.post.name')),
            array('code' => 'code', 'title' => $this->trans('admin.post.code')),
            array('code' => 'postGroup', 'title' => $this->trans('admin.post.group')),
        );
    }

    public function buildExportData($parameters)
    {
        $postGroups = $this->getPostService()->searchPostGroups(array(), array('seq' => 'ASC'), 0, PHP_INT_MAX);
        $postData = array();
        foreach ($postGroups as $postGroup) {
            $posts = $this->getPostService()->findPostsByGroupId($postGroup['id']);
            foreach ($posts as $post) {
                $postData[] = array(
                    'name' => $post['name'],
                    'code' => $post['code'],
                    'postGroup' => $postGroup['name'],
                );
            }
        }

        $exportData[] = array(
            'data' => $postData,
        );

        return $exportData;
    }
}
