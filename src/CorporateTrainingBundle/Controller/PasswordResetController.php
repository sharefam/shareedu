<?php

namespace CorporateTrainingBundle\Controller;

use AppBundle\Controller\BaseController;
use AppBundle\Common\SmsToolkit;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;

class PasswordResetController extends BaseController
{
    public function indexAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $data = array('email' => '');

        if ($user->isLogin()) {
            if (!$user['setup'] || stripos($user['email'], '@edusoho.net') != false) {
                return $this->redirect($this->generateUrl('settings_setup'));
            } else {
                $data['email'] = $user['email'];
            }
        }

        $form = $this->createFormBuilder($data)
            ->add('email', 'email')
            ->getForm();

        $error = null;

        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $data = $form->getData();
                $user = $this->getUserService()->getUserByEmail($data['email']);

                if (empty($user)) {
                    list($result, $message) = $this->getAuthService()->checkEmail($data['email']);

                    if ($result == 'error_duplicate') {
                        $error = ServiceKernel::instance()->trans('password_reset.message.error_duplicate');

                        return $this->render('password-reset/index.html.twig', array(
                            'form' => $form->createView(),
                            'error' => $error,
                        ));
                    }
                }

                if ($user) {
                    $token = $this->getUserService()->makeToken('password-reset', $user['id'], strtotime('+1 day'));
                    try {
                        $site = $this->setting('site', array());
                        $mailOptions = array(
                            'to' => $user['email'],
                            'template' => 'email_reset_password',
                            'format' => 'html',
                            'params' => array(
                                'nickname' => $user['nickname'],
                                'verifyurl' => $this->generateUrl('password_reset_update', array('token' => $token), true),
                                'sitename' => $site['name'],
                                'siteurl' => $site['url'],
                            ),
                        );

                        $mailFactory = $this->getBiz()->offsetGet('mail_factory');
                        $mail = $mailFactory($mailOptions);
                        $mail->send();
                    } catch (\Exception $e) {
                        $this->getLogService()->error('user', 'password-reset', '重设密码邮件发送失败:'.$e->getMessage());

                        return $this->createMessageResponse('error', 'password_reset.message.reset_error');
                    }

                    $this->getLogService()->info('user', 'password-reset', "{$user['email']}向发送了找回密码邮件。");

                    return $this->render(
                        'password-reset/sent.html.twig',
                        array(
                        'user' => $user,
                        'emailLoginUrl' => $this->getEmailLoginUrl($user['email']),
                        )
                    );
                } else {
                    $error = ServiceKernel::instance()->trans('password_reset.message.email_login_error');
                }
            }
        }

        return $this->render(
            'password-reset/index.html.twig',
            array(
            'form' => $form->createView(),
            'error' => $error,
            )
        );
    }

    public function updateAction(Request $request)
    {
        $token = $this->getUserService()->getToken('password-reset', $request->query->get('token') ?: $request->request->get('token'));

        if (empty($token)) {
            return $this->render(
                'password-reset/error.html.twig'
            );
        }

        $form = $this->createFormBuilder()
            ->add('password', 'password')
            ->add('confirmPassword', 'password')
            ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $data = $form->getData();

                $this->getAuthService()->changePassword($token['userId'], null, $data['password']);

                $this->getUserService()->deleteToken('password-reset', $token['token']);

                return $this->render(
                    'password-reset/success.html.twig'
                );
            }
        }

        return $this->render(
            'password-reset/update.html.twig',
            array(
            'form' => $form->createView(),
            )
        );
    }

    public function changeRawPasswordAction(Request $request)
    {
        $fields = $request->query->all();
        $user_token = $this->getTokenService()->verifyToken('email_password_reset', $fields['token']);
        $flag = $this->getUserService()->changeRawPassword($user_token['data']['userId'], $user_token['data']['rawPassword']);

        if (!$flag) {
            return $this->render(
                'password-reset/raw-error.html.twig'
            );
        } else {
            return $this->render(
                'password-reset/raw-success.html.twig'
            );
        }
    }

    public function resetBySmsAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();

            list($result, $sessionField, $requestField) = SmsToolkit::smsCheck($request, $scenario = 'sms_forget_password');

            if ($result) {
                $targetUser = $this->getUserService()->getUserByVerifiedMobile($request->request->get('mobile'));

                if (empty($targetUser)) {
                    return $this->createMessageResponse('error', 'password_reset.reset_by_sms.message.user_empty');
                }

                $token = $this->getUserService()->makeToken('password-reset', $targetUser['id'], strtotime('+1 day'));
                $request->request->set('token', $token);

                return $this->redirect($this->generateUrl('password_reset_update', array(
                    'token' => $token,
                )));
            } else {
                return $this->createMessageResponse('error', 'password_reset.reset_by_sms.message.reset_error');
            }
        }

        return $this->createJsonResponse('GET method');
    }

    public function getEmailLoginUrl($email)
    {
        $host = substr($email, strpos($email, '@') + 1);

        if ($host == 'hotmail.com') {
            return 'http://www.'.$host;
        }

        if ($host == 'gmail.com') {
            return 'http://mail.google.com';
        }

        return 'http://mail.'.$host;
    }

    public function checkMobileExistsAction(Request $request)
    {
        $mobile = $request->query->get('value');
        list($result, $message) = $this->getAuthService()->checkMobile($mobile);

        if ($result == 'success') {
            $response = array('success' => false, 'message' => ServiceKernel::instance()->trans('password_reset.check_mobile_exists.mobile_empty'));
        } else {
            $response = array('success' => true, 'message' => '');
        }

        return $this->createJsonResponse($response);
    }

    protected function getAuthService()
    {
        return $this->createService('User:AuthService');
    }

    protected function getTokenService()
    {
        return $this->createService('User:TokenService');
    }
}
