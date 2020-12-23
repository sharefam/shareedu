<?php

namespace CorporateTrainingBundle\Handler;

use AppBundle\Common\ArrayToolkit;
use Biz\User\Dao\UserProfileDao;
use Biz\User\CurrentUser;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use CorporateTrainingBundle\Component\EIMClient\UserFactory;
use CorporateTrainingBundle\Component\EIMClient\DepartmentFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class LoginSyncDepartmentHandler
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Biz
     */
    private $biz;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->biz = $this->container->get('biz');
    }

    /**
     * Do the magic.
     *
     * @param InteractiveLoginEvent $event
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $currentUser = $this->biz['user'];
        $userBinds = $this->getUserService()->findBindsByUserId($currentUser['id']);
        $request = $event->getRequest();

        if (empty($userBinds)) {
            return;
        }

        $syncSetting = $this->getSettingService()->get('sync_department_setting', array());

        if (empty($syncSetting)) {
            return;
        } else {
            if (!$syncSetting['enable']) {
                return;
            }
        }

        $fromId = 0;
        if ('dingtalk' == $syncSetting['type']) {
            foreach ($userBinds as $userBind) {
                if ('dingtalk' == $userBind['type']) {
                    $fromId = $userBind['fromId'];
                    break;
                }
            }
        }

        $userClient = UserFactory::create($syncSetting);
        $userId = $userClient->getUserIdByUnionId($fromId);
        if (!$userId) {
            return;
        }
        $user = $userClient->get($userId);

        if (0 != $user['errcode']) {
            return;
        }

        $departments = array();
        $departmentClient = DepartmentFactory::create($syncSetting);
        foreach ($user['department'] as $departmentId) {
            array_push($departments, $departmentClient->get($departmentId));
        }

        $departmentIds = ArrayToolkit::column($departments, 'id');
        $orgs = $this->getOrgService()->findOrgsBySyncIds($departmentIds);

        if (!empty($orgs)) {
            if ($currentUser['orgIds'] != ArrayToolkit::column($orgs, 'id')) {
                $this->getUserDao()->update($currentUser['id'], array(
                        'orgIds' => ArrayToolkit::column($orgs, 'id'),
                    )
                );
                $this->getUserOrgService()->setUserOrgs($currentUser['id'], $orgs);
            }

            if ($currentUser['orgCodes'] != ArrayToolkit::column($orgs, 'orgCode')) {
                $this->getUserDao()->update($currentUser['id'], array(
                        'orgCodes' => ArrayToolkit::column($orgs, 'orgCode'),
                    )
                );
            }
        }

        if (!empty($user['mobile'])) {
            if ($currentUser['verifiedMobile'] != $user['mobile']) {
                $this->getUserDao()->update($currentUser['id'], array(
                    'verifiedMobile' => $user['mobile'],
                ));
            }
        }

        $user['email'] = isset($user['email']) ? trim($user['email']) : '';
        if (!empty($user['email'])) {
            $emailIsExist = $this->getUserService()->getUserByEmail($user['email']);
            if ($currentUser['email'] != $user['email'] && !$emailIsExist) {
                $user = $this->getUserDao()->update($currentUser['id'], array(
                    'email' => $user['email'],
                ));

                $this->kickUserLogout($currentUser['id']);

                $currentUser = new CurrentUser();
                $currentUser->fromArray($user);
                $this->switchUser($request, $currentUser);
            }
        }

        if (!empty($user['name'])) {
            $userProfile = $this->getUserService()->getUserProfile($currentUser['id']);
            if ($userProfile['truename'] != $user['name']) {
                $this->getUserProfileDao()->update($currentUser['id'], array(
                    'truename' => $user['name'],
                ));
            }
        }
    }

    protected function kickUserLogout($userId)
    {
        $this->getSessionService()->clearByUserId($userId);
        $tokens = $this->getTokenService()->findTokensByUserIdAndType($userId, 'mobile_login');
        if (!empty($tokens)) {
            foreach ($tokens as $token) {
                $this->getTokenService()->destoryToken($token['token']);
            }
        }
    }

    /**
     * switch current user.
     *
     * @param Request     $request
     * @param CurrentUser $user
     *
     * @return CurrentUser
     */
    protected function switchUser($request, CurrentUser $user)
    {
        $user['currentIp'] = $request->getClientIp();

        $token = new UsernamePasswordToken($user, null, 'main', $user['roles']);
        $this->container->get('security.token_storage')->setToken($token);

        $biz = $this->biz;
        $biz['user'] = $user;

        return $user;
    }

    /**
     * @return UserDao
     */
    protected function getUserDao()
    {
        return $this->biz->dao('User:UserDao');
    }

    /**
     * @return UserProfileDao
     */
    protected function getUserProfileDao()
    {
        return $this->biz->dao('User:UserProfileDao');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }

    /**
     * @return SessionService
     */
    protected function getSessionService()
    {
        return $this->biz->service('System:SessionService');
    }

    /**
     * @return TokenService
     */
    protected function getTokenService()
    {
        return $this->biz->service('User:TokenService');
    }

    /**
     * @return SettingService
     */
    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }

    /**
     * @return UserOrgService
     */
    protected function getUserOrgService()
    {
        return $this->biz->service('User:UserOrgService');
    }

    /**
     * @return OrgService
     */
    protected function getOrgService()
    {
        return $this->biz->service('Org:OrgService');
    }
}
