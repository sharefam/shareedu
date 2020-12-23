<?php

namespace CorporateTrainingBundle\Biz\User\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\User\Dao\UserDao;
use Biz\User\Service\Impl\AuthServiceImpl as BaseServiceImpl;
use CorporateTrainingBundle\Biz\User\Service\AuthService;
use Topxia\Service\Common\ServiceKernel;

class AuthServiceImpl extends BaseServiceImpl implements AuthService
{
    public function register($registration, $type = 'default')
    {
        if ($this->getUserService()->isOverMaxUsersNumber()) {
            throw $this->createServiceException(ServiceKernel::instance()->trans('user.auth.message.register_exception'));
        }

        if (isset($registration['nickname']) && !empty($registration['nickname'])
            && $this->getSensitiveService()->scanText($registration['nickname'])) {
            throw $this->createInvalidArgumentException('site.register.sensitive_words');
        }

        if ($this->registerLimitValidator($registration)) {
            throw $this->createAccessDeniedException('site.register.time_limit');
        }

        $this->getKernel()->getConnection()->beginTransaction();
        try {
            $registration = $this->refillFormData($registration, $type);
            $registration['providerType'] = $this->getAuthProvider()->getProviderName();

            $authUser = $this->getAuthProvider()->register($registration);

            if ('default' == $type) {
                if (!empty($authUser['id'])) {
                    $registration['token'] = array(
                        'userId' => $authUser['id'],
                    );
                }

                $newUser = $this->getUserService()->register(
                    $registration,
                    $this->biz['user.register.type.toolkit']->getRegisterTypes($registration)
                );
            } else {
                $newUser = $this->getUserService()->register($registration, $type);

                if (!empty($authUser['id'])) {
                    $this->getUserService()->bindUser($this->getPartnerName(), $authUser['id'], $newUser['id'], null);
                }
            }

            if (!empty($registration['orgIds']) && !empty($registration['orgCodes'])) {
                $this->getUserOrgService()->setUserOrgs($newUser['id'], $registration['orgs']);
            }

            $this->getKernel()->getConnection()->commit();

            return $newUser;
        } catch (\Exception $e) {
            $this->getKernel()->getConnection()->rollBack();
            throw $e;
        }
    }

    protected function fillOrgId($fields)
    {
        if ($this->isOrgUseable()) {
            if (empty($fields['orgCodes'])) {
                unset($fields['orgCodes']);
            } else {
                $fields['orgCodes'] = $this->filterOrgCodes($fields['orgCodes']);
                $orgs = $this->getOrgService()->findOrgsByOrgCodes($fields['orgCodes']);
                $orgs = $this->filterOrgs($orgs);

                $orgCodes = ArrayToolkit::column($orgs, 'orgCode');
                if (array_diff($fields['orgCodes'], $orgCodes)) {
                    throw $this->createNotFoundException(ServiceKernel::instance()->trans('admin.user.auth.message.fill_org_exception'));
                }
                $fields['orgCodes'] = $orgCodes;
                $fields['orgIds'] = ArrayToolkit::column($orgs, 'id');
                $fields['orgs'] = $orgs;
            }
        } else {
            unset($fields['orgCodes']);
        }

        return $fields;
    }

    protected function isOrgUseable()
    {
        $magic = $this->getSettingService()->get('magic');
        if (isset($magic['enable_org']) && $magic['enable_org']) {
            return true;
        }

        return false;
    }

    protected function filterOrgCodes($orgCodes)
    {
        if (is_array($orgCodes)) {
            return $orgCodes;
        } else {
            return explode('|', $orgCodes);
        }
    }

    protected function filterOrgs($rawOrgs)
    {
        $orgs = array();

        foreach ($rawOrgs as $rawOrg) {
            $orgs[] = array(
                'id' => $rawOrg['id'],
                'orgCode' => $rawOrg['orgCode'],
            );
        }

        return $orgs;
    }

    protected function getOrgService()
    {
        return $this->createService('Org:OrgService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\User\Service\UserOrgService
     */
    protected function getUserOrgService()
    {
        return $this->createService('CorporateTrainingBundle:User:UserOrgService');
    }

    /**
     * @return UserDao
     */
    protected function getUserDao()
    {
        return $this->createDao('User:UserDao');
    }
}
