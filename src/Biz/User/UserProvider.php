<?php

namespace Biz\User;

use Biz\User\Service\UserService;
use Biz\Role\Util\PermissionBuilder;
use Topxia\Service\Common\ServiceKernel;
use AppBundle\Handler\AuthenticationHelper;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class UserProvider implements UserProviderInterface
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function loadUserByUsername($username)
    {
        $user = $this->getUserService()->getUserByLoginField($username);

        if (empty($user)) {
            throw new UsernameNotFoundException(sprintf('User "%s" not found.', $username));
        } elseif (isset($user['type']) && 'system' == $user['type']) {
            throw new UsernameNotFoundException(sprintf('User "%s" not found.', $username));
        }

        $request = $this->container->get('request');

        $forbidden = AuthenticationHelper::checkLoginForbidden($request);
        if ('error' == $forbidden['status']) {
            throw new AuthenticationException($forbidden['message']);
        }

        //TODO: 兼容升级脚本执行，5.0.0之后版本移除 try catch
        try {
            $user = $this->getUserService()->getUserWithOrgScopes($user['id']);
        } catch (\Exception $e) {
            $user = $this->getUserService()->getUser($user['id']);
        }

        $user['currentIp'] = $request->getClientIp();
        $user['org'] = $this->loadOrg($request, $user);
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $currentUser->setPermissions(PermissionBuilder::instance()->getPermissionsByRoles($currentUser->getRoles()));
        $currentUser['isSecure'] = $request->isSecure();
        $biz = $this->container->get('biz');
        $biz['user'] = $currentUser;
        ServiceKernel::instance()->setCurrentUser($currentUser);

        return $currentUser;
    }

    /*
     * 如果用户有多个组织机构返回第一个组织机构
     */
    protected function loadOrg($request, $user)
    {
        $org = $request->getSession()->get('currentUserOrg', array());
        if (empty($org) || $org['orgCode'] != $user['orgCodes'][0]) {
            $org = $this->getOrgService()->getOrgByOrgCode($user['orgCodes'][0]);
            $request->getSession()->set('currentUserOrg', $org);
        }

        return $org;
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof CurrentUser) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return 'Biz\User\CurrentUser' === $class;
    }

    protected function getRoleService()
    {
        return ServiceKernel::instance()->createService('Role:RoleService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->container->get('biz')->service('User:UserService');
    }

    protected function getOrgService()
    {
        return ServiceKernel::instance()->createService('Org:OrgService');
    }
}