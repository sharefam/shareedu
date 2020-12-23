<?php

namespace CorporateTrainingBundle\Controller;

use AppBundle\Controller\OrderRefundController as BaseController;
use Biz\Order\OrderRefundProcessor\OrderRefundProcessorFactory;
use Symfony\Component\HttpFoundation\Request;

class OrderRefundController extends BaseController
{
    public function refundAction(Request $request, $id)
    {
        $targetType = $request->query->get('targetType');

        if (!in_array($targetType, array('course', 'classroom'))) {
            throw $this->createAccessDeniedException('The parameter is wrong.');
        }

        $processor = OrderRefundProcessorFactory::create($targetType);

        $target = $processor->getTarget($id);
        $user = $this->getCurrentUser();
        $member = $processor->getTargetMember($id, $user['id']);

        if (empty($member)) {
            throw $this->createAccessDeniedException('You are not a student and cannot drop out.');
        }

        $order = array();

        if (!empty($member['orderId'])) {
            $order = $this->getOrderService()->getOrder($member['orderId']);

            if (empty($order)) {
                throw $this->createNotFoundException();
            }

            if ($order['targetType'] == 'groupSell') {
                throw $this->createAccessDeniedException('Group Sell course cannot drop out.');
            }
        }

        if ('POST' == $request->getMethod()) {
            $data = $request->request->all();
            $reason = empty($data['reason']) ? array() : $data['reason'];
            $amount = empty($data['applyRefund']) ? 0 : null;

            $reason['operator'] = $user['id'];
            if (empty($member['orderId'])) {
                if ($targetType == 'course') {
                    $this->getCourseMemberService()->removeStudent($id, $user['id']);
                }
            } else {
                $processor->applyRefundOrder($member['orderId'], $amount, $reason, $this->container);
            }

            return $this->createJsonResponse(true);
        }

        $maxRefundDays = (int) $this->setting('refund.maxRefundDays', 0);

        $refundOverdue = 0;
        if (!empty($refundOverdue)) {
            $refundOverdue = (time() - $order['createdTime']) > ($maxRefundDays * 86400);
        }

        return $this->render(
            'order-refund/refund-modal.html.twig',
            array(
                'target' => $target,
                'targetType' => $targetType,
                'order' => $order,
                'maxRefundDays' => $maxRefundDays,
                'refundOverdue' => $refundOverdue,
            )
        );
    }

    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }
}
