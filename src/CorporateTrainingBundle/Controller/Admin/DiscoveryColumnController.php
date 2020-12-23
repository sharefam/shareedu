<?php

namespace CorporateTrainingBundle\Controller\Admin;

use AppBundle\Controller\Admin\DiscoveryColumnController as BaseController;
use Biz\DiscoveryColumn\Service\DiscoveryColumnService;
use Symfony\Component\HttpFoundation\Request;

class DiscoveryColumnController extends BaseController
{
    public function defaultEditAction(Request $request, $id)
    {
        $discoveryColumn = $this->getDiscoveryColumnService()->getDiscoveryColumn($id);

        if (empty($discoveryColumn)) {
            throw $this->createNotFoundException();
        }

        if ($request->getMethod() == 'POST') {
            $conditions = $request->request->all();

            $this->getDiscoveryColumnService()->updateDiscoveryColumn($id, $conditions);

            return $this->redirect($this->generateUrl('admin_discovery_column_index'));
        }

        return $this->render('admin/discovery-column/default-discovery-column-modal.html.twig', array(
            'discoveryColumn' => $discoveryColumn,
            'categoryId' => $discoveryColumn['categoryId'],
        ));
    }

    /**
     * @return DiscoveryColumnService
     */
    protected function getDiscoveryColumnService()
    {
        return $this->createService('DiscoveryColumn:DiscoveryColumnService');
    }
}
