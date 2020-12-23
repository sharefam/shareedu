<?php

namespace CorporateTrainingBundle\Controller\Admin;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\Admin\BlockController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\Paginator;
use AppBundle\Common\BlockToolkit;

class BlockController extends BaseController
{
    public function indexAction(Request $request, $category = '')
    {
        list($condation, $sort) = $this->dealQueryFields($category);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getBlockService()->searchBlockTemplateCount($condation),
            20
        );
        $blockTemplates = $this->getBlockService()->searchBlockTemplates(
            $condation,
            $sort,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $blockTemplateIds = ArrayToolkit::column($blockTemplates, 'id');

        $blocks = $this->getBlockService()->getBlocksByBlockTemplateIds($blockTemplateIds);
        $blockIds = ArrayToolkit::column($blocks, 'id');
        $latestHistories = $this->getBlockService()->getLatestBlockHistoriesByBlockIds($blockIds);
        $userIds = ArrayToolkit::column($latestHistories, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('admin/block/index.html.twig', array(
            'blockTemplates' => $blockTemplates,
            'users' => $users,
            'latestHistories' => $latestHistories,
            'paginator' => $paginator,
            'type' => $category,
        ));
    }

    public function updateAction(Request $request, $blockTemplateId)
    {
        $user = $this->getUser();

        if ('POST' == $request->getMethod()) {
            $fields = $request->request->all();
            $fields['userId'] = $user['id'];
            if (empty($fields['blockId'])) {
                $block = $this->getBlockService()->createBlock($fields);
            } else {
                $block = $this->getBlockService()->updateBlock($fields['blockId'], $fields);
            }
            $latestBlockHistory = $this->getBlockService()->getLatestBlockHistory();
            $latestUpdateUser = $this->getUserService()->getUser($latestBlockHistory['userId']);
            $html = $this->renderView('admin/block/list-tr.html.twig', array(
                'blockTemplate' => $block,
                'latestUpdateUser' => $latestUpdateUser,
                'latestHistory' => $latestBlockHistory,
            ));

            return $this->createJsonResponse(array('status' => 'ok', 'html' => $html));
        }

        $block = $this->getBlockService()->getBlockByTemplateId($blockTemplateId);

        return $this->render('admin/block/block-update-modal.html.twig', array(
            'block' => $block,
        ));
    }

    public function visualEditAction(Request $request, $blockTemplateId)
    {
        $user = $this->getUser();
        if ('POST' == $request->getMethod()) {
            $condation = $request->request->all();
            $block['data'] = $condation['data'];
            $block['templateName'] = $condation['templateName'];
            $html = BlockToolkit::render($block, $this->container);
            $fields = array(
                'data' => $block['data'],
                'content' => $html,
                'userId' => $user['id'],
                'blockTemplateId' => $condation['blockTemplateId'],
                'code' => $condation['code'],
                'mode' => $condation['mode'],
            );
            if (empty($condation['blockId'])) {
                $block = $this->getBlockService()->createBlock($fields);
            } else {
                $block = $this->getBlockService()->updateBlock($condation['blockId'], $fields);
            }

            $this->setFlashMessage('success', 'site.save.success');
        }

        $block = $this->getBlockService()->getBlockByTemplateId($blockTemplateId);

        return $this->render('admin/block/block-visual-edit.html.twig', array(
            'block' => $block,
            'action' => 'edit',
        ));
    }

    public function visualHistoryAction(Request $request, $blockTemplateId)
    {
        $block = $this->getBlockService()->getBlockByTemplateId($blockTemplateId);
        $paginator = new Paginator(
            $this->get('request'),
            null,
            5
        );
        $blockHistorys = array();
        $historyUsers = array();

        if (!empty($block)) {
            $paginator = new Paginator(
                $this->get('request'),
                $this->getBlockService()->findBlockHistoryCountByBlockId($block['blockId']),
                20
            );

            $blockHistorys = $this->getBlockService()->findBlockHistorysByBlockId(
                $block['blockId'],
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount());

            $historyUsers = $this->getUserService()->findUsersByIds(ArrayToolkit::column($blockHistorys, 'userId'));
        }

        return $this->render('admin/block/block-visual-history.html.twig', array(
            'block' => $block,
            'paginator' => $paginator,
            'blockHistorys' => $blockHistorys,
            'historyUsers' => $historyUsers,
        ));
    }

    public function recoveryAction(Request $request, $blockTemplateId, $historyId)
    {
        $history = $this->getBlockService()->getBlockHistory($historyId);
        $block = $this->getBlockService()->getBlockByTemplateId($blockTemplateId);
        $this->getBlockService()->recovery($block['blockId'], $history);
        $this->setFlashMessage('success', 'site.reset.success');

        return $this->redirect($this->generateUrl('admin_block_visual_edit_history', array('blockTemplateId' => $blockTemplateId)));
    }
}
