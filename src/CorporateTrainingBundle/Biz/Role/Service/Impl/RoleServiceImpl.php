<?php

namespace CorporateTrainingBundle\Biz\Role\Service\Impl;

use AppBundle\Common\Tree;
use Biz\Role\Service\Impl\RoleServiceImpl as BaseService;
use Biz\Role\Util\PermissionBuilder;

class RoleServiceImpl extends BaseService
{
    public function refreshRoles()
    {
        $permissions = PermissionBuilder::instance()->buildPermissions();
        $tree = Tree::buildWithArray($permissions, null, 'code', 'parent');

        $superAdminRoles = $this->getSuperAdminRoles($tree);
        $adminRoles = $this->getAdminRoles($tree);
        $teacherRoles = $this->getTeacherRoles($tree);
        $departmentRoles = $this->getDepartmentRoles($tree);
        $trainingRoles = $this->getTrainingRoles($tree);

        $roles = array(
            'ROLE_USER' => array(),
            'ROLE_TEACHER' => $teacherRoles,
            'ROLE_ADMIN' => $adminRoles,
            'ROLE_DEPARTMENT_ADMIN' => $departmentRoles,
            'ROLE_TRAINING_ADMIN' => $trainingRoles,
            'ROLE_SUPER_ADMIN' => $superAdminRoles,
        );

        foreach ($roles as $key => $value) {
            $userRole = $this->getRoleDao()->getByCode($key);

            if (empty($userRole)) {
                $this->initCreateRole($key, array_values($value));
            } else {
                $this->getRoleDao()->update($userRole['id'], array('data' => array_values($value)));
            }
        }
    }

    protected function getSuperAdminRoles($tree)
    {
        return $tree->column('code');
    }

    protected function getAdminRoles($tree)
    {
        $adminForbidRoles = array(
            'admin_user_avatar',
            'admin_user_change_password',
            'admin_my_cloud',
            'admin_cloud_video_setting',
            'admin_edu_cloud_sms',
            'admin_edu_cloud_search_setting',
            'admin_setting_cloud_attachment',
            'admin_setting_cloud',
            'admin_system',
        );

        $getAdminForbidRoles = array();
        foreach ($adminForbidRoles as $adminForbidRole) {
            $adminRole = $tree->find(function ($tree) use ($adminForbidRole) {
                return $tree->data['code'] === $adminForbidRole;
            });

            if (is_null($adminRole)) {
                continue;
            }

            $getAdminForbidRoles = array_merge($adminRole->column('code'), $getAdminForbidRoles);
        }

        return array_diff($this->getSuperAdminRoles($tree), $getAdminForbidRoles);
    }

    protected function getTeacherRoles($tree)
    {
        return array(
            'admin',
            'admin_train',
            'admin_train_teach_manage_my_teaching_courses_manage',
            'admin_train_teach_manage_my_teaching_courses',
            'admin_train_teach_manage_my_teaching_classrooms_manage',
            'admin_train_teach_manage_my_teaching_classrooms',
            'admin_train_teach_manage_project_plan_teaching_manage',
            'admin_train_teach_manage_project_plan_teaching',
        );
    }

    protected function getDepartmentRoles($tree)
    {
        $departmentRoleCode = 'admin_data';
        $departmentRoles = $tree->find(function ($tree) use ($departmentRoleCode) {
            return $tree->data['code'] === $departmentRoleCode;
        });

        return array_merge($departmentRoles->column('code'), array('admin'));
    }

    protected function getTrainingRoles($tree)
    {
        $trainingRoleCodes = array(
            'admin_data',
            'admin_train',
        );

        $trainingRoles = array();
        foreach ($trainingRoleCodes as $code) {
            $trainingRole = $tree->find(function ($tree) use ($code) {
                return $tree->data['code'] === $code;
            });

            if (is_null($trainingRole)) {
                continue;
            }

            $trainingRoles = array_merge($trainingRole->column('code'), $trainingRoles);
        }

        return array_merge($trainingRoles, array('admin'));
    }

    protected function initCreateRole($code, $role)
    {
        $userRoles = array(
            'ROLE_SUPER_ADMIN' => array('name' => '超级管理员', 'code' => 'ROLE_SUPER_ADMIN'),
            'ROLE_ADMIN' => array('name' => '管理员', 'code' => 'ROLE_ADMIN'),
            'ROLE_TEACHER' => array('name' => '讲师', 'code' => 'ROLE_TEACHER'),
            'ROLE_USER' => array('name' => '学员', 'code' => 'ROLE_USER'),
            'ROLE_DEPARTMENT_ADMIN' => array('name' => '部门管理员', 'code' => 'ROLE_DEPARTMENT_ADMIN'),
            'ROLE_TRAINING_ADMIN' => array('name' => '培训管理员', 'code' => 'ROLE_TRAINING_ADMIN'),
        );
        $userRole = $userRoles[$code];

        $userRole['data'] = $role;
        $userRole['createdTime'] = time();
        $userRole['createdUserId'] = $this->getCurrentUser()->getId();
        $this->getLogService()->info('role', 'init_create_role', '初始化四个角色"'.$userRole['name'].'"', $userRole);

        return $this->getRoleDao()->create($userRole);
    }
}