<?php

namespace CorporateTrainingBundle\Controller;

use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;

class AreaController extends BaseController
{
    public function searchAreasAction(Request $request)
    {
        $id = $request->query->get('id');
        $result = $this->getAreaService()->findAreasByParentId($id);

        return $this->createJsonResponse($result);
    }

    protected function getAreaService()
    {
        return $this->createService('CorporateTrainingBundle:Area:AreaService');
    }
}
