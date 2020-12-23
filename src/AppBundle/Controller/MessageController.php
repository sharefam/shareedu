<?php

namespace AppBundle\Controller;

use AppBundle\Common\Paginator;
use Biz\User\Service\UserService;
use AppBundle\Common\ArrayToolkit;
use Biz\User\Service\MessageService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class MessageController extends BaseController
{
    public function indexAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $paginator = new Paginator(
            $request,
            $this->getMessageService()->countUserConversations($user->id),
            10
        );
        $conversations = $this->getMessageService()->findUserConversations(
            $user->id,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($conversations, 'fromId'));

        return $this->render('message/index.html.twig', array(
            'conversations' => $conversations,
            'users' => $users,
            'paginator' => $paginator,
        ));
    }

    public function checkReceiverAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();
        $name = $request->query->get('value');
        preg_match_all("/(?:\()(.*)(?:\))/i", $name, $result);
        $user = $this->getUserService()->getUserByNickname($result[1][0]);
        $userProfile = $this->getUserService()->getUserProfile($user['id']);
        if ($name != $userProfile['truename'].'('.$user['nickname'].')') {
            $response = array('success' => false, 'message' => 'json_response.receiver_not_exist.message');
        } elseif ($currentUser['nickname'] == $user['nickname']) {
            $response = array('success' => false, 'message' => 'json_response.cannot_send_message_self.message');
        } else {
            $response = array('success' => true, 'message' => '');
        }

        if (!$this->getWebExtension()->canSendMessage($user['id'])) {
            $response = array('success' => false, 'message' => 'json_response.receiver_not_allowed.message');
        }

        return $this->createJsonResponse($response);
    }

    public function showConversationAction(Request $request, $conversationId)
    {
        $user = $this->getCurrentUser();
        $conversation = $this->getMessageService()->getConversation($conversationId);
        if (empty($conversation) || $conversation['toId'] != $user['id']) {
            throw $this->createNotFoundException('私信会话不存在！');
        }
        $paginator = new Paginator(
            $request,
            $this->getMessageService()->countConversationMessages($conversationId),
            10
        );

        $this->getMessageService()->markConversationRead($conversationId);
        $this->getUserService()->updateUserNewMessageNum($user['id'], $conversation['unreadNum']);

        $messages = $this->getMessageService()->findConversationMessages(
            $conversation['id'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        if ('POST' == $request->getMethod()) {
            $message = $request->request->get('message_reply');
            if (!$this->getWebExtension()->canSendMessage($conversation['fromId'])) {
                throw $this->createAccessDeniedException('not_allowd_send_message');
            }
            $message = $this->getMessageService()->sendMessage($user['id'], $conversation['fromId'], $message['content']);
            $html = $this->renderView('message/item.html.twig', array('message' => $message, 'conversation' => $conversation));

            return $this->createJsonResponse(array('status' => 'ok', 'html' => $html));
        }

        return $this->render('message/conversation-show.html.twig', array(
            'conversation' => $conversation,
            'messages' => $messages,
            'receiver' => $this->getUserService()->getUser($conversation['fromId']),
            'paginator' => $paginator,
        ));
    }

    public function createAction(Request $request, $toId)
    {
        $user = $this->getCurrentUser();
        $receiver = $this->getUserService()->getUser($toId);
        $userProfile = $this->getUserService()->getUserProfile($toId);

        $message = array('receiver' => $userProfile['truename'].'('.$receiver['nickname'].')');
        if ('POST' == $request->getMethod()) {
            $message = $request->request->get('message');
            preg_match_all("/(?:\()(.*)(?:\))/i", $message['receiver'], $result);
            $nickname = $result[1][0];
            $receiver = $this->getUserService()->getUserByNickname($nickname);
            if (empty($receiver)) {
                throw $this->createNotFoundException('抱歉，该收信人尚未注册!');
            }
            if (!$this->getWebExtension()->canSendMessage($receiver['id'])) {
                throw $this->createAccessDeniedException('not_allowd_send_message');
            }
            $this->getMessageService()->sendMessage($user['id'], $receiver['id'], $message['content']);

            return $this->redirect($this->generateUrl('message'));
        }

        return $this->render('message/send-message-modal.html.twig', array(
            'message' => $message,
            'userId' => $toId, ));
    }

    public function sendAction(Request $request)
    {
        $user = $this->getCurrentUser();
        if ('POST' == $request->getMethod()) {
            $message = $request->request->get('message');
            preg_match_all("/(?:\()(.*)(?:\))/i", $message['receiver'], $result);
            $nickname = $result[1][0];
            $receiver = $this->getUserService()->getUserByNickname($nickname);
            if (empty($receiver)) {
                throw $this->createNotFoundException('抱歉，该收信人尚未注册!');
            }
            if (!$this->getWebExtension()->canSendMessage($receiver['id'])) {
                throw $this->createAccessDeniedException('not_allowd_send_message');
            }
            $this->getMessageService()->sendMessage($user['id'], $receiver['id'], $message['content']);

            return $this->redirect($this->generateUrl('message'));
        }

        return $this->render('message/create.html.twig');
    }

    public function sendToAction(Request $request, $receiverId)
    {
        $receiver = $this->getUserService()->getUser($receiverId);
        $user = $this->getCurrentUser();
        $message = array('receiver' => $receiver['nickname']);
        if ('POST' == $request->getMethod()) {
            $message = $request->request->get('message');
            $nickname = $message['receiver'];
            $receiver = $this->getUserService()->getUserByNickname($nickname);
            if (empty($receiver)) {
                throw $this->createNotFoundException('抱歉，该收信人尚未注册!');
            }
            if (!$this->getWebExtension()->canSendMessage($receiver['id'])) {
                throw $this->createAccessDeniedException('not_allowd_send_message');
            }
            $this->getMessageService()->sendMessage($user['id'], $receiver['id'], $message['content']);

            return $this->redirect($this->generateUrl('message'));
        }

        return $this->render('message/create.html.twig', array('message' => $message));
    }

    public function deleteConversationAction(Request $request, $conversationId)
    {
        $user = $this->getCurrentUser();
        $conversation = $this->getMessageService()->getConversation($conversationId);
        if (empty($conversation) || $conversation['toId'] != $user['id']) {
            throw $this->createAccessDeniedException('您无权删除此私信！');
        }

        $this->getMessageService()->deleteConversation($conversationId);

        return $this->redirect($this->generateUrl('message'));
    }

    public function deleteConversationMessageAction(Request $request, $conversationId, $messageId)
    {
        $user = $this->getCurrentUser();
        $conversation = $this->getMessageService()->getConversation($conversationId);
        if (empty($conversation) || $conversation['toId'] != $user['id']) {
            throw $this->createAccessDeniedException('您无权删除此私信！');
        }

        $this->getMessageService()->deleteConversationMessage($conversationId, $messageId);
        $messagesCount = $this->getMessageService()->countConversationMessages($conversationId);
        if ($messagesCount > 0) {
            return $this->redirect($this->generateUrl('message_conversation_show', array('conversationId' => $conversationId)));
        } else {
            return $this->redirect($this->generateUrl('message'));
        }
    }

    public function matchAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();
        $data = array();
        $queryString = $request->query->get('q');
        $findUsersByTruename = $this->getUserService()->searchUsers(
            array('truename' => $queryString),
            array('createdTime' => 'DESC'),
            0,
            10);

        foreach ($findUsersByTruename as $user) {
            $userProfile = $this->getUserService()->getUserProfile($user['id']);
            $data[] = array(
                'id' => $user['id'],
                'truename' => $userProfile['truename'].'('.$user['nickname'].')',
            );
        }

        return new JsonResponse($data);
    }

    protected function getWebExtension()
    {
        return $this->container->get('web.twig.extension');
    }

    /**
     * @return MessageService
     */
    protected function getMessageService()
    {
        return $this->getBiz()->service('User:MessageService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }
}
