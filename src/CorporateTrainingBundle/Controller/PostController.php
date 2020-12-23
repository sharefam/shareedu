<?php

namespace CorporateTrainingBundle\Controller;

use AppBundle\Controller\BaseController;
use CorporateTrainingBundle\Biz\Post\Service\PostService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class PostController extends BaseController
{
    public function matchAction(Request $request)
    {
        $data = array();
        $queryString = $request->query->get('q');

        $likeName = '%'.$queryString.'%';

        $posts = $this->getPostService()->searchPosts(
            array('likeName' => $likeName),
            array(),
            0,
            100
        );

        foreach ($posts as $post) {
            $data[] = array('id' => $post['id'], 'name' => $post['name']);
        }

        return new JsonResponse($data);
    }

    /**
     * @return PostService
     */
    protected function getPostService()
    {
        return $this->createService('CorporateTrainingBundle:Post:PostService');
    }
}
