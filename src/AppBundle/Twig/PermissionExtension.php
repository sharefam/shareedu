<?php

namespace AppBundle\Twig;

use Biz\Role\Util\PermissionBuilder;
use Topxia\Service\Common\ServiceKernel;

class PermissionExtension extends \Twig_Extension
{
    protected $container;

    protected $builder = null;

    protected $loader = null;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('parent_permission', array($this, 'getParentPermission')),
        );
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('permission', array($this, 'getPermissionByCode')),
            new \Twig_SimpleFunction('sub_permissions', array($this, 'getSubPermissions')),
            new \Twig_SimpleFunction('permission_path', array($this, 'getPermissionPath'), array('needs_context' => true, 'needs_environment' => true)),
            new \Twig_SimpleFunction('grouped_permissions', array($this, 'groupedPermissions')),
            new \Twig_SimpleFunction('has_permission', array($this, 'hasPermission')),
            new \Twig_SimpleFunction('eval_expression', array($this, 'evalExpression'), array('needs_context' => true, 'needs_environment' => true)),
            new \Twig_SimpleFunction('first_child_permission', array($this, 'getFirstChild')),
            new \Twig_SimpleFunction('grouped_permissions_without_btn', array($this, 'groupedPermissionsWithoutBtn')),
            new \Twig_SimpleFunction('build_sidebar_permissions', array($this, 'buildSideBarPermissions')),
        );
    }

    public function getFirstChild($menu)
    {
        $menus = $this->getSubPermissions($menu['code']);

        if (empty($menus)) {
            $permissions = $this->createPermissionBuilder()->getSubPermissions($menu['code']);
            if (empty($permissions)) {
                return array();
            } else {
                $menus = $permissions;
            }
        }

        return current($menus);
    }

    public function getPermissionPath($env, $context, $menu)
    {
        $route = empty($menu['router_name']) ? $menu['code'] : $menu['router_name'];
        $params = empty($menu['router_params']) ? array() : $menu['router_params'];
        foreach ($params as $key => $value) {
            if (0 === strpos($value, '(')) {
                $value = $this->evalExpression($env, $context['_context'], $value);
                $params[$key] = $value;
            } else {
                $params[$key] = "{$value}";
            }
        }

        return $this->container->get('router')->generate($route, $params);
    }

    public function evalExpression($twig, $context, $code)
    {
        $code = trim($code);
        if (0 === strpos($code, '(')) {
            $code = substr($code, 1, strlen($code) - 2);
        } else {
            $code = "'{$code}'";
        }

        $loader = new \Twig_Loader_Array(array(
            'expression.twig' => '{{'.$code.'}}',
        ));

        $loader = new \Twig_Loader_Chain(array($loader, $twig->getLoader()));

        $twig->setLoader($loader);

        return $twig->render('expression.twig', $context);
    }

    public function getPermissionByCode($code)
    {
        return $this->createPermissionBuilder()->getPermissionByCode($code);
    }

    public function hasPermission($code)
    {
        $currentUser = ServiceKernel::instance()->getCurrentUser();

        return $currentUser->hasPermission($code);
    }

    public function getSubPermissions($code, $group = null)
    {
        $permission = $this->getPermissionByCode($code);
        if (isset($permission['disable']) && $permission['disable']) {
            return $this->createPermissionBuilder()->getSubPermissions($code, $group);
        } else {
            return $this->createPermissionBuilder()->getUserSubPermissions($code, $group);
        }
    }

    public function groupedPermissions($code)
    {
        return $this->createPermissionBuilder()->groupedPermissions($code);
    }

    public function groupedPermissionsWithoutBtn($code)
    {
        $group = $this->createPermissionBuilder()->groupedPermissions($code);
        $permission = $this->getPermissionByCode($code);

        $permissionMenus = $this->buildChildPermissionMenus($group);
        $permissionMenus['name'] = ServiceKernel::instance()->trans($permission['name'], array(), 'menu');

        return $permissionMenus;
    }

    protected function buildChildPermissionMenus($childrens, $grade = 0)
    {
        $permissions = array();
        foreach ($childrens as $key => &$children) {
            if (isset($children['visible']) && !$this->evalExpression($this->container->get('twig'), array(), $children['visible'])) {
                unset($childrens[$key]);
                continue;
            }

            $childrenInfo = array();
            $childrenInfo['code'] = $children['code'];
            $childrenInfo['url'] = $this->getPermissionPath(array(), array(), $this->getFirstChild($children));
            $childrenInfo['name'] = ServiceKernel::instance()->trans($children['name'], array(), 'menu');
            if (isset($children['group'])) {
                $childrenInfo['grade'] = $grade + 1;
                if (isset($permissions[$children['group']])) {
                    $permissions[$children['group']]['children'][] = $childrenInfo;
                } else {
                    $group = array();
                    $group['code'] = $children['groupName'];
                    $group['name'] = ServiceKernel::instance()->trans($children['groupName'], array(), 'menu');
                    $group['grade'] = $grade;
                    $group['children'][] = $childrenInfo;
                    $permissions[$children['group']] = $group;
                }
            } else {
                if (isset($permissions[$key])) {
                    $childrenInfo['grade'] = $grade;
                    $index = $key + 1;
                    $permissions[$index] = $childrenInfo;
                } else {
                    $childrenInfo['grade'] = $grade;
                    $permissions[$key] = $childrenInfo;
                }
            }
        }

        return array('children' => $permissions);
    }

    public function getParentPermission($code)
    {
        $permission = $this->createPermissionBuilder()->getPermissionByCode($code);

        if (isset($permission['disable']) && $permission['disable']) {
            $parent = $this->createPermissionBuilder()->getPermissionTreeByCode($permission['parent']);
        } else {
            $parent = $this->createPermissionBuilder()->getParentPermissionTreeByCode($code);
        }

        return $parent;
    }

    private function createPermissionBuilder()
    {
        return PermissionBuilder::instance();
    }

    public function getName()
    {
        return 'permission.permission_extension';
    }
}
