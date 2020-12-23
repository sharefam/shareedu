<?php

namespace CorporateTrainingBundle\Handler;

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Handler\AuthenticationHelper;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use AppBundle\Handler\AuthenticationSuccessHandler as BaseAuthenticationSuccessHandler;

class AuthenticationSuccessHandler extends BaseAuthenticationSuccessHandler
{
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $forbidden = AuthenticationHelper::checkLoginForbidden($request);

        if ($forbidden['status'] == 'error') {
            $exception = new AuthenticationException($forbidden['message']);
            throw $exception;
        }

        if ($request->isXmlHttpRequest()) {
            $content = array(
                'success' => true,
            );

            return new JsonResponse($content, 200);
        }

        $currentUser = $this->getServiceKernel()->getCurrentUser();

        if (!$currentUser['pwdInit']) {
            $url = $this->httpUtils->generateUri($request, 'pwd_init');
            $queries = array('goto' => $this->determineTargetUrl($request));
            $url = $url.'?'.http_build_query($queries);

            return $this->httpUtils->createRedirectResponse($request, $url);
        }

        if ($this->getAuthService()->hasPartnerAuth()) {
            $url = $this->httpUtils->generateUri($request, 'partner_login');
            $queries = array('goto' => $this->determineTargetUrl($request));
            $url = $url.'?'.http_build_query($queries);

            return $this->httpUtils->createRedirectResponse($request, $url);
        }

        return parent::onAuthenticationSuccess($request, $token);
    }

    private function getAuthService()
    {
        return ServiceKernel::instance()->createService('User:AuthService');
    }
}
