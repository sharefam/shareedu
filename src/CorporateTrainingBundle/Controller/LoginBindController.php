<?php

namespace CorporateTrainingBundle\Controller;

use AppBundle\Common\SimpleValidator;
use Biz\User\CurrentUser;
use CorporateTrainingBundle\Biz\DingTalk\Service\DingTalkUserService;
use CorporateTrainingBundle\Biz\OrgSync\Service\OrgSyncService;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\LoginBindController as BaseLoginBindController;
use CorporateTrainingBundle\Component\EIMClient\UserFactory;
use Topxia\Service\Common\ServiceKernel;

class LoginBindController extends BaseLoginBindController
{
    public function callbackAction(Request $request, $type)
    {
        $code = $request->query->get('code');
        $inviteCode = $request->query->get('inviteCode');
        $token = $request->query->get('token', '');

        $this->validateToken($request, $type);

        $callbackUrl = $this->generateUrl('login_bind_callback', array('type' => $type, 'token' => $token), true);
        $oauthClient = $this->createOAuthClient($type);
        $token = $oauthClient->getAccessToken($code, $callbackUrl);

        $bind = $this->getUserService()->getUserBindByTypeAndFromId($type, $token['userId']);
        $syncSetting = $this->getSettingService()->get('sync_department_setting', array());

        if (!empty($token['dingtalkUserid']) && !empty($syncSetting['key']) && !empty($syncSetting['secret'])) {
            $this->getDingTalkUserService()->createUser(array('unionid' => $token['dingtalkUnionid'], 'userid' => $token['dingtalkUserid']));
        }

        $request->getSession()->set('oauth_token', $token);

        if ($bind) {
            $user = $this->getUserService()->getUser($bind['toId']);

            if (empty($user) || 1 == $user['locked']) {
                if ('dingtalkmob' == $type) {
                    return $this->createMessageResponse('error', 'login_bind.call_back.message.user_empty_error');
                }
                $this->setFlashMessage('danger', 'login_bind.call_back.message.user_empty_error');

                return $this->redirect($this->generateUrl('login'));
            }

            if ($this->isPluginInstalled('LDAP')) {
                return $this->forward('LDAPPlugin:LoginBind:LDAPCallback', array('request' => $request, 'type' => $type, 'token' => $token, 'user' => $user));
            }

            $this->authenticateUser($user);

            if ($this->getAuthService()->hasPartnerAuth()) {
                return $this->redirect($this->generateUrl('partner_login', array('goto' => $this->getTargetPath($request))));
            } else {
                $goto = $this->getTargetPath($request);

                return $this->redirect($goto);
            }
        } else {
            if ('dingtalkweb' == $type || 'dingtalkmob' == $type) {
                $oUser = array(
                    'id' => $token['userId'],
                    'name' => $token['name'],
                    'avatar' => $token['avatar'],
                );
                if ($this->isEnableSyncDepartment()) {
                    //dingding第三方登录需要同步数据
                    return $this->forward('CorporateTrainingBundle:LoginBind:dingdingCallbackSyncDepartment', array('request' => $request, 'type' => $type, 'token' => $token, 'oUser' => $oUser));
                }
            } else {
                $oUser = $oauthClient->getUserInfo($token);
            }

            $this->storeOauthUserToSession($request, $oUser, $type);

            return $this->redirect($this->generateUrl('oauth2_login_index', array('inviteCode' => $inviteCode)));
        }
    }

    //dingding第三方登录需要同步数据
    public function dingdingCallbackSyncDepartmentAction(Request $request, $type, $token, $oUser)
    {
        $setting = $this->getSettingService()->get('sync_department_setting', array());
        $userClient = UserFactory::create($setting);
        $userId = $token['dingtalkUserid'];
        if (!$userId) {
            return $this->createMessageResponse('error', 'login_bind.call_back.dingtalkweb.message.user_empty');
        }

        $user = $userClient->get($userId);
        if (!empty($user) && !$this->isDepartmentMember($user['department'])) {
            $result = $this->getOrgSyncService()->syncFrom();

            if (!$result['success']) {
                return $this->createMessageResponse('error', 'login_bind.call_back.dingtalkweb.message.org_sync_error');
            }
            $this->getLogService()->info('org-sync', 'create-org', '用户部门不存在，同步钉钉部门', array('name' => isset($user['name']) ? $user['name'] : ''));

            if (!$this->isDepartmentMember($user['department'])) {
                return $this->createMessageResponse('error', 'login_bind.call_back.dingtalkweb.message.org_empty');
            }
        }
        $oauthUser = $this->storeOauthUserToSession($request, $oUser, $type);

        return $this->render('CorporateTrainingBundle::oauth2/index.html.twig', array(
            'oauthUser' => $oauthUser,
        ));
    }

    protected function isEnableSyncDepartment()
    {
        $enable = false;
        $setting = $this->getSettingService()->get('sync_department_setting', array());
        if (!empty($setting['enable'])) {
            $enable = true;
        }

        return $enable;
    }

    protected function generateUser($type, $token, $oauthUser, $setData)
    {
        $registration = array();
        $randString = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $oauthUser['name'] = preg_replace('/[^\x{4e00}-\x{9fa5}a-zA-z0-9_.]+/u', '', $oauthUser['name']);
        $oauthUser['name'] = str_replace(array('-'), array('_'), $oauthUser['name']);

        if (!SimpleValidator::nickname($oauthUser['name'])) {
            $oauthUser['name'] = '';
        }

        $tempType = $type;

        if (empty($oauthUser['name'])) {
            if ('weixinmob' == $type || 'weixinweb' == $type) {
                $tempType = 'weixin';
            }

            $oauthUser['name'] = $tempType.substr($randString, 9, 3);
        }

        $nameLength = mb_strlen($oauthUser['name'], 'utf-8');

        if ($nameLength > 10) {
            $oauthUser['name'] = mb_substr($oauthUser['name'], 0, 11, 'utf-8');
        }

        if (!empty($setData['nickname']) && !empty($setData['email'])) {
            $registration['nickname'] = $setData['nickname'];
            $registration['email'] = $setData['email'];
            $registration['emailOrMobile'] = $setData['email'];
        } else {
            $nicknames = array();
            $nicknames[] = isset($setData['nickname']) ? $setData['nickname'] : $oauthUser['name'];
            $nicknames[] = mb_substr($oauthUser['name'], 0, 8, 'utf-8').substr($randString, 0, 3);
            $nicknames[] = mb_substr($oauthUser['name'], 0, 8, 'utf-8').substr($randString, 3, 3);
            $nicknames[] = mb_substr($oauthUser['name'], 0, 8, 'utf-8').substr($randString, 6, 3);

            foreach ($nicknames as $name) {
                if ($this->getUserService()->isNicknameAvaliable($name)) {
                    $registration['nickname'] = $name;
                    break;
                }
            }

            if (empty($registration['nickname'])) {
                return array();
            }

            $syncSetting = $this->getSettingService()->get('sync_department_setting', array());
            if (!empty($syncSetting) && $syncSetting['enable'] && 'dingtalk' == $syncSetting['type']) {
                if (!empty($setData['emailDingtalk'])) {
                    $registration['email'] = $setData['emailDingTalk'];
                } else {
                    $registration['email'] = 'u_'.substr($randString, 0, 12).'@edusoho.net';
                }
            } else {
                $registration['email'] = 'u_'.substr($randString, 0, 12).'@edusoho.net';
            }
        }

        if ($this->getSensitiveService()->scanText($registration['nickname'])) {
            return $this->createMessageResponse('error', 'login_bind.generate_user.message.nickname_sensitive');
        }

        $registration['password'] = substr(base_convert(sha1(uniqid(mt_rand(), true)), 16, 36), 0, 8);
        $registration['token'] = $token;
        $registration['createdIp'] = $oauthUser['createdIp'];

        if (isset($setData['mobile']) && !empty($setData['mobile'])) {
            $registration['mobile'] = $setData['mobile'];
            $registration['emailOrMobile'] = $setData['mobile'];
        }

        if (isset($setData['invite_code']) && !empty($setData['invite_code'])) {
            $registration['invite_code'] = $setData['invite_code'];
        }

        $user = $this->getAuthService()->register($registration, $type);

        return $user;
    }

    private function isDepartmentMember($department)
    {
        $result = false;
        $orgs = $this->getOrgService()->findOrgsBySyncIds($department);

        if ($orgs) {
            $result = true;
        }

        return $result;
    }

    private function isExistedUserByMobile($mobile)
    {
        $result = false;
        $user = $this->getUserService()->getUserByVerifiedMobile($mobile);

        if ($user) {
            $result = true;
        }

        return $result;
    }

    public function existAction(Request $request, $type)
    {
        $token = $request->getSession()->get('oauth_token');
        $client = $this->createOAuthClient($type);
        $oauthUser = $client->getUserInfo($token);
        $data = $request->request->all();

        $message = ServiceKernel::instance()->trans('login_bind.exist.message.email_mobile_error');

        if (SimpleValidator::email($data['emailOrMobileOrNickname'])) {
            $user = $this->getUserService()->getUserByEmail($data['emailOrMobileOrNickname']);
            $message = ServiceKernel::instance()->trans('login_bind.exist.message.email_error');
        } elseif (SimpleValidator::mobile($data['emailOrMobileOrNickname'])) {
            $user = $this->getUserService()->getUserByVerifiedMobile($data['emailOrMobileOrNickname']);
            $message = ServiceKernel::instance()->trans('login_bind.exist.message.mobile_error');
        } elseif (SimpleValidator::nickname($data['emailOrMobileOrNickname'])) {
            $user = $this->getUserService()->getUserByNickname($data['emailOrMobileOrNickname']);
            $message = '该用户名尚未注册';
        }

        if (empty($user)) {
            $response = array('success' => false, 'message' => $message);
        } elseif (!$this->getUserService()->verifyPassword($user['id'], $data['password'])) {
            $response = array('success' => false, 'message' => ServiceKernel::instance()->trans('login_bind.exist.message.password_error'));
        } elseif ($this->getUserService()->getUserBindByTypeAndUserId($type, $user['id'])) {
            $response = array('success' => false, 'message' => sprintf(ServiceKernel::instance()->trans('login_bind.exist.message.user_exit', array('%s%' => $this->setting('site.name')))));
        } else {
            $response = array('success' => true, '_target_path' => $this->getTargetPath($request));
            $this->getUserService()->bindUser($type, $oauthUser['id'], $user['id'], $token);
            $currentUser = new CurrentUser();
            $currentUser->fromArray($user);
            $this->switchUser($request, $currentUser);
        }

        return $this->createJsonResponse($response);
    }

    protected function getOrgService()
    {
        return $this->createService('Org:OrgService');
    }

    /**
     * @return OrgSyncService
     */
    protected function getOrgSyncService()
    {
        return $this->createService('OrgSync:OrgSyncService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return DingTalkUserService
     */
    protected function getDingTalkUserService()
    {
        return $this->createService('CorporateTrainingBundle:DingTalk:DingTalkUserService');
    }

    protected function getLdapUserService()
    {
        return $this->createService('LDAPPlugin:User:UserService');
    }
}
