<?php

namespace CorporateTrainingBundle\Controller\OAuth2;

use AppBundle\Controller\OAuth2\LoginController as BaseController;
use Biz\System\Service\SettingService;
use Biz\User\Service\UserService;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\OAuth2\OAuthUser;
use AppBundle\Component\RateLimit\LoginFailRateLimiter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use AppBundle\Component\RateLimit\RegisterRateLimiter;
use Topxia\Service\Common\ServiceKernel;

class LoginController extends BaseController
{
    public function mainAction(Request $request)
    {
        $oauthUser = $this->getOauthUser($request);

        return $this->render('CorporateTrainingBundle::oauth2/index.html.twig', array(
            'oauthUser' => $oauthUser,
        ));
    }

    public function bindAccountAction(Request $request)
    {
        $oauthUser = $this->getOauthUser($request);

        $type = $request->request->get('accountType');
        $account = $request->request->get('account');

        $user = $this->getUserByTypeAndAccount($type, $account);
        $oauthUser->accountType = $type;
        $oauthUser->account = $account;
        $oauthUser->captchaEnabled = OAuthUser::MOBILE_TYPE == $oauthUser->accountType || $oauthUser->captchaEnabled;
        $oauthUser->isNewAccount = $user ? false : true;

        $request->getSession()->set(OAuthUser::SESSION_KEY, $oauthUser);

        $setting = $this->getSettingService()->get('login_bind', array());

        if ($oauthUser->isNewAccount) {
            if (('dingtalkweb' == $oauthUser->type || 'dingtalkmob' == $oauthUser->type) && 'sync' == $setting['dingtalkMode']) {
                $redirectUrl = $this->generateUrl('oauth2_login_create');
            } else {
                return $this->createMessageResponse('warning', 'login_bind.call_back.message.user_empty_error');
            }
        } else {
            $redirectUrl = $this->generateUrl('oauth2_login_bind_login');
        }

        return $this->redirect($redirectUrl);
    }

    public function createAction(Request $request)
    {
        $oauthUser = $this->getOauthUser($request);

        if ('POST' == $request->getMethod()) {
            if ($this->getUserService()->isOverMaxUsersNumber()) {
                return $this->createFailJsonResponse(array('msg' => $this->trans('user.auth.message.register_exception')));
            }
            if (!$this->isTypeAllowToCreate($oauthUser->type)) {
                return $this->createFailJsonResponse(array('msg' => 'not allow to create user.'));
            }

            $fields = $request->request->all();

            $oauthUser->accountType = $fields['accountType'];
            $oauthUser->account = $fields['nickname'];

            $request->getSession()->set(OAuthUser::SESSION_KEY, $oauthUser);
            if (!$this->getUserService()->isNicknameAvaliable($fields['nickname'])) {
                return $this->createFailJsonResponse(array('msg' => '用户名已存在'));
            }

            $this->registerAttemptCheck($request);
            $this->register($request);
            $this->authenticatedOauthUser();

            return $this->createSuccessJsonResponse(array('url' => $this->generateUrl('oauth2_login_success')));
        }

        return $this->render('CorporateTrainingBundle::oauth2/create-account.html.twig', array(
            'oauthUser' => $oauthUser,
        ));
    }

    public function bindLoginAction(Request $request)
    {
        $oauthUser = $this->getOauthUser($request);
        if ('POST' == $request->getMethod()) {
            $password = $request->request->get('password');

            $user = $this->getUserByTypeAndAccount($oauthUser->accountType, $oauthUser->account);
            $isBind = $this->getUserService()->isUserBound($user['id'], $oauthUser->type);

            if ($isBind) {
                return $this->createJsonResponse(array(
                    'success' => 0,
                    'message' => ServiceKernel::instance()->trans('login_bind.call_back.message.repeat'),
                ));
            }

            $this->loginAttemptCheck($oauthUser->account, $request);

            $isSuccess = $this->bindUser($oauthUser, $password);

            return $isSuccess ?
                $this->createSuccessJsonResponse(array('url' => $this->generateUrl('oauth2_login_success'))) :
                $this->createFailJsonResponse(array('message' => $this->trans('user.settings.security.password_modify.message.incorrect_password')));
        } else {
            $user = $this->getUserByTypeAndAccount($oauthUser->accountType, $oauthUser->account);

            return $this->render('oauth2/bind-login.html.twig', array(
                'oauthUser' => $oauthUser,
                'esUser' => $user,
            ));
        }
    }

    protected function bindUser(OAuthUser $oauthUser, $password)
    {
        $user = $this->getUserByTypeAndAccount($oauthUser->accountType, $oauthUser->account);

        $isCorrectPassword = $this->getUserService()->verifyPassword($user['id'], $password);
        if ($isCorrectPassword) {
            $this->getUserService()->bindUser($oauthUser->type, $oauthUser->authid, $user['id'], null);
            $this->authenticatedOauthUser();

            return true;
        } else {
            return false;
        }
    }

    protected function validateRegisterRequest(Request $request)
    {
        $validateResult = array(
            'hasError' => false,
        );

        $oauthUser = $this->getOauthUser($request);
        if (OAuthUser::MOBILE_TYPE == $oauthUser->accountType) {
            $smsToken = $request->request->get('smsToken');
            $mobile = $request->request->get(OAuthUser::MOBILE_TYPE);
            $smsCode = $request->request->get('smsCode');
            $status = $this->getBizSms()->check(BizSms::SMS_BIND_TYPE, $mobile, $smsToken, $smsCode);

            $validateResult['hasError'] = BizSms::STATUS_SUCCESS !== $status;
            $validateResult['msg'] = $status;
        }

        return $validateResult;
    }

    protected function validateRegisterType(Request $request)
    {
        $oauthUser = $this->getOauthUser($request);
        $isCloseRegister = OAuthUser::REGISTER_CLOSED === $oauthUser->mode;
        $notEmailAccount = OAuthUser::EMAIL_TYPE === $oauthUser->mode && OAuthUser::EMAIL_TYPE !== $oauthUser->accountType;
        $notMobileAccount = OAuthUser::MOBILE_TYPE === $oauthUser->mode && OAuthUser::MOBILE_TYPE !== $oauthUser->accountType;
        $notNicknameAccount = OAuthUser::NICKNAME_TYPE !== $oauthUser->accountType;
        if ($isCloseRegister || $notEmailAccount || $notMobileAccount || $notNicknameAccount) {
            throw new NotFoundHttpException();
        }
    }

    protected function authenticatedOauthUser()
    {
        $request = $this->get('request');
        $oauthUser = $this->getOauthUser($this->get('request'));
        $oauthUser->authenticated = true;
        $request->getSession()->set(OAuthUser::SESSION_KEY, $oauthUser);
    }

    protected function register(Request $request)
    {
        $oauthUser = $this->getOauthUser($request);
        $registerFields = array(
            'nickname' => $request->request->get('nickname'),
            'password' => $request->request->get('password'),
            $oauthUser->accountType => $oauthUser->account,
            'avatar' => $oauthUser->avatar,
            'type' => $oauthUser->type,
            'registeredWay' => $oauthUser->isApp() ? strtolower($oauthUser->os) : 'web',
            'authid' => $oauthUser->authid,
        );

        if (OAuthUser::MOBILE_TYPE == $oauthUser->accountType) {
            $registerFields['verifiedMobile'] = $oauthUser->account;
            $registerFields['email'] = $this->getUserService()->generateEmail($registerFields);
        }

        if (OAuthUser::NICKNAME_TYPE == $oauthUser->accountType) {
            $registerFields['email'] = $this->getUserService()->generateEmail($registerFields);
        }

        $this->getUserService()->register($registerFields, array($oauthUser->accountType, 'binder'));
    }

    private function registerAttemptCheck(Request $request)
    {
        $limiter = new RegisterRateLimiter($this->getBiz());
        $limiter->handle($request);
    }

    protected function getUserByTypeAndAccount($type, $account)
    {
        $user = null;
        switch ($type) {
            case OAuthUser::EMAIL_TYPE:
                $user = $this->getUserService()->getUserByEmail($account);
                break;
            case OAuthUser::MOBILE_TYPE:
                $user = $this->getUserService()->getUserByVerifiedMobile($account);
                break;
            case OAuthUser::NICKNAME_TYPE:
                $user = $this->getUserService()->getUserByNickname($account);
                break;
            default:
                throw new NotFoundHttpException();
        }

        return $user;
    }

    protected function loginAttemptCheck($account, Request $request)
    {
        $limiter = new LoginFailRateLimiter($this->getBiz());
        $request->request->set('username', $account);
        $limiter->handle($request);
    }

    protected function isTypeAllowToCreate($type)
    {
        $allowTypes = array('dingtalkweb', 'dingtalkmob');

        return in_array($type, $allowTypes);
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return \Biz\Common\BizSms
     */
    protected function getBizSms()
    {
        $biz = $this->getBiz();

        return $biz['biz_sms'];
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }
}
