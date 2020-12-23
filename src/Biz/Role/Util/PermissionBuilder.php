<?php

namespace Biz\Role\Util;

use AppBundle\Common\Tree;
use AppBundle\Common\ArrayToolkit;
use Symfony\Component\Yaml\Yaml;
use AppBundle\Common\PluginVersionToolkit;
use Topxia\Service\Common\ServiceKernel;

class PermissionBuilder
{
    private $position = 'admin';

    private static $builder;
    private $cached = array();

    private function __construct()
    {
    }

    public static function instance()
    {
        if (empty(self::$builder)) {
            self::$builder = new self();
        }

        return self::$builder;
    }

    /**
     * getOriginPermissionByCode => getPermissionByCode.
     *
     * 根据code获取某一子权限树
     *
     * @return $permissions array
     */
    public function getPermissionByCode($code)
    {
        $permissions = $this->findAllPermissions();

        return isset($permissions[$code]) ? $permissions[$code] : array();
    }

    /**
     * getPermissionByCode => getPermissionTreeByCode.
     *
     * 根据code获取某一子权限树
     *
     * @return Tree
     */
    public function getPermissionTreeByCode($code)
    {
        if (isset($this->cached['getPermissionTreeByCode'][$code])) {
            return $this->cached['getPermissionTreeByCode'][$code];
        }

        if (!isset($this->cached['getPermissionTreeByCode'])) {
            $this->cached['getPermissionTreeByCode'] = array();
        }

        $this->cached['getPermissionTreeByCode'][$code] = array();

        $userPermissionTree = $this->getUserPermissionTree();

        $codeTree = $userPermissionTree->find(
            function ($tree) use ($code) {
                return $tree->data['code'] === $code;
            }
        );

        if (is_null($codeTree)) {
            return $this->cached['getPermissionTreeByCode'][$code];
        }

        $this->cached['getPermissionTreeByCode'][$code] = $codeTree->data;

        return $this->cached['getPermissionTreeByCode'][$code];
    }

    /**
     * getParentPermissionByCode => getParentPermissionTreeByCode.
     */
    public function getParentPermissionTreeByCode($code)
    {
        $userPermissionTree = $this->getUserPermissionTree();

        $codeTree = $userPermissionTree->find(
            function ($tree) use ($code) {
                return $tree->data['code'] === $code;
            }
        );

        if (is_null($codeTree)) {
            return array();
        }

        $parent = $codeTree->getParent();

        if (is_null($parent)) {
            return array();
        }

        return $parent->data;
    }

    /**
     * 获取用户自身所拥有的权限树.
     *
     * @param bool $needDisable 树结构里是否需要包含权限管理被忽略的权限
     *
     * @return Tree
     */
    public function getUserPermissionTree()
    {
        if (isset($this->cached['getUserPermissionTree'])) {
            return $this->cached['getUserPermissionTree'];
        }

        $menus = $this->getCurrentUserPermissions();

        if (empty($menus)) {
            return new Tree();
        }

        $menus = $this->calculateWeight($menus);

        $userPermissionTree = Tree::buildWithArray($menus, null, 'code', 'parent');
        $this->cached['getUserPermissionTree'] = $userPermissionTree;

        return $this->cached['getUserPermissionTree'];
    }

    /**
     * getOriginSubPermissions => getSubPermissions.
     */
    public function getSubPermissions($code, $group = null)
    {
        if (isset($this->cached['getSubPermissions'][$code][$group])) {
            return $this->cached['getSubPermissions'][$code][$group];
        }

        if (!isset($this->cached['getSubPermissions'])) {
            $this->cached['getSubPermissions'] = array();
        }

        if (!isset($this->cached['getSubPermissions'][$code])) {
            $this->cached['getSubPermissions'][$code] = array();
        }

        $tree = $this->findAllPermissionTree(true);

        $codeTree = $tree->find(
            function ($tree) use ($code) {
                return $tree->data['code'] === $code;
            }
        );

        $permissions = array();
        if (!is_null($codeTree)) {
            foreach ($codeTree->getChildren() as $child) {
                if (empty($group) || (isset($child->data['group']) && $child->data['group'] == $group)) {
                    $permissions[] = $child->data;
                }
            }
        }

        $this->cached['getSubPermissions'][$code][$group] = $permissions;

        return $this->cached['getSubPermissions'][$code][$group];
    }

    /**
     * getSubPermissions => getUserSubPermissions.
     */
    public function getUserSubPermissions($code, $group)
    {
        if (isset($this->cached['getUserSubPermissions'][$code][$group])) {
            return $this->cached['getUserSubPermissions'][$code][$group];
        }

        if (!isset($this->cached['getUserSubPermissions'])) {
            $this->cached['getUserSubPermissions'] = array();
        }

        if (!isset($this->cached['getUserSubPermissions'][$code])) {
            $this->cached['getUserSubPermissions'][$code] = array();
        }

        $userPermissionTree = $this->getUserPermissionTree();

        $codeTree = $userPermissionTree->find(
            function ($tree) use ($code) {
                return $tree->data['code'] === $code;
            }
        );

        if (is_null($codeTree)) {
            return $this->cached['getUserSubPermissions'][$code];
        }

        $children = array();
        foreach ($codeTree->getChildren() as $child) {
            if (empty($group) || (isset($child->data['group']) && $child->data['group'] == $group)) {
                $children[] = $child->data;
            }
        }
        $childrenCodes = ArrayToolkit::column($children, 'code');
        $subPermission = $this->getSubPermissions($code);

        foreach ($subPermission as $value) {
            $issetDisable = isset($value['disable']) && $value['disable'];
            $isGroup = empty($group) || (isset($value['group']) && $value['group'] == $group);
            $isExist = in_array($value['code'], $childrenCodes, true);
            if ($issetDisable && $isGroup && !$isExist) {
                $children[] = $value;
            }
        }

        $this->cached['getUserSubPermissions'][$code][$group] = $children;

        return $this->cached['getUserSubPermissions'][$code][$group];
    }

    /**
     * getOriginPermissionTree => findAllPermissionTree.
     *
     * @param bool $needDisable 树结构里是否需要包含权限管理被忽略的权限
     *
     * @return Tree
     */
    public function findAllPermissionTree($needDisable = false)
    {
        $index = (int) $needDisable;
        if (isset($this->cached['findAllPermissionTree'][$index])) {
            return $this->cached['findAllPermissionTree'][$index];
        }

        $permissions = $this->findAllPermissions();

        if (!$needDisable) {
            $permissions = array_filter(
                $permissions,
                function ($permission) {
                    return !(isset($permission['disable']) && $permission['disable']);
                }
            );
        }

        $tree = Tree::buildWithArray($permissions, null, 'code', 'parent');

        $this->cached['findAllPermissionTree'][$index] = $tree;

        return $tree;
    }

    /**
     * @param array $roles 角色
     *
     * @return array $permissions[]
     */
    public function getPermissionsByRoles(array $roles)
    {
        if (empty($roles)) {
            return array();
        }

        $originPermissions = $this->findAllPermissions();

        if (in_array('ROLE_SUPER_ADMIN', $roles)) {
            $permissions = $originPermissions;
        } else {
            $roleService = ServiceKernel::instance()->createService('Role:RoleService');

            $permissionCode = array();
            foreach ($roles as $code) {
                $role = $roleService->getRoleByCode($code);

                if (empty($role['data'])) {
                    $role['data'] = array();
                }

                $permissionCode = array_merge($permissionCode, $role['data']);
            }

            $permissions = array();
            foreach ($permissionCode as $code) {
                if (isset($originPermissions[$code])) {
                    $permissions[$code] = $originPermissions[$code];
                }
            }
        }

        return $permissions;
    }

    /**
     * getOriginPermissions => findAllPermissions.
     *
     * 获取所有权限
     */
    public function findAllPermissions()
    {
        if (isset($this->cached['findAllPermissions'])) {
            return $this->cached['findAllPermissions'];
        }

        $environment = ServiceKernel::instance()->getEnvironment();
        $cacheDir = ServiceKernel::instance()->getParameter('kernel.cache_dir');
        $cacheFile = $cacheDir.'/menus_cache_'.$this->position.'.php';
        if ('dev' != $environment && file_exists($cacheFile)) {
            $this->cached['findAllPermissions'] = include $cacheFile;

            return $this->cached['findAllPermissions'];
        }

        $permissions = $this->buildPermissions();
        $this->cached['findAllPermissions'] = $permissions;

        if (in_array($environment, array('test', 'dev'))) {
            return $permissions;
        }

        $cache = "<?php \nreturn ".var_export($permissions, true).';';
        file_put_contents($cacheFile, $cache);

        return $permissions;
    }

    /**
     * loadPermissionsFromAllConfig => buildPermissions.
     *
     * 构建所有权限树
     *
     * @return $permissions array
     */
    public function buildPermissions()
    {
        $configs = $this->findMenusConfigs();
        $permissions = array();
        foreach ($configs as $config) {
            if (!file_exists($config)) {
                continue;
            }
            $menus = Yaml::parse(file_get_contents($config));
            if (empty($menus)) {
                continue;
            }

            $permissions = array_merge($permissions, $this->buildPermissionsFromMenusConfig($menus));
        }

        return $permissions;
    }

    /**
     * loadPermissionsFromConfig => buildPermissionsFromMenusConfig.
     *
     * 根据menus参数构建权限树
     */
    protected function buildPermissionsFromMenusConfig($menus)
    {
        $permissions = array();

        foreach ($menus as $key => $value) {
            $value['code'] = $key;
            $permissions[$key] = $value;

            if (isset($value['children'])) {
                $childrenMenu = $value['children'];

                unset($value['children']);

                foreach ($childrenMenu as $childKey => $childValue) {
                    $childValue['parent'] = $key;
                    $permissions = array_merge($permissions, $this->buildPermissionsFromMenusConfig(array($childKey => $childValue)));
                }
            }
        }

        return $permissions;
    }

    /**
     * getPermissionConfig => getMenusConfigPath.
     *
     * 获取Bundle和Plugin的menus_admin配置文件路径
     *
     * @return $configPaths array
     */
    public function findMenusConfigs()
    {
        $configPaths = array();
        $position = $this->position;

        $rootDir = ServiceKernel::instance()->getParameter('kernel.root_dir');
        $filePaths = array(
            $rootDir.'/../src/CorporateTrainingBundle/Resources/config/menus_admin.yml',
            $rootDir.'/../src/CustomBundle/Resources/config/menus_admin.yml',
        );

        foreach ($filePaths as $filepath) {
            if (is_file($filepath)) {
                $configPaths[] = $filepath;
            }
        }

        $configPaths = $this->findPluginsMenusConfig($rootDir, $configPaths, $position);

        return $configPaths;
    }

    public function findPluginsMenusConfig($rootDir, $configPaths, $position)
    {
        $count = $this->getAppService()->findAppCount();
        $apps = $this->getAppService()->findApps(0, $count);

        foreach ($apps as $app) {
            if ('plugin' != $app['type']) {
                continue;
            }

            if ('MAIN' !== $app['code'] && $app['protocol'] < 3) {
                continue;
            }

            if (!PluginVersionToolkit::dependencyVersion($app['code'], $app['version'])) {
                continue;
            }

            $code = ucfirst($app['code']);
            $configPaths[] = "{$rootDir}/../plugins/{$code}Plugin/Resources/config/menus_{$position}.yml";
        }

        return $configPaths;
    }

    public function groupedPermissions($code)
    {
        if (isset($this->cached['groupedPermissions'][$code])) {
            return $this->cached['groupedPermissions'][$code];
        }

        $this->cached['groupedPermissions'][$code] = array();

        $userPermissionTree = $this->getUserPermissionTree();

        $codeTree = $userPermissionTree->find(
            function ($tree) use ($code) {
                return $tree->data['code'] === $code;
            }
        );

        if (is_null($codeTree)) {
            return $this->cached['groupedPermissions'][$code];
        }

        $grouped = array();

        foreach ($codeTree->getChildren() as $child) {
            $grouped[] = $child->data;
        }

        $this->cached['groupedPermissions'][$code] = $grouped;

        return $this->cached['groupedPermissions'][$code];
    }

    protected function calculateWeight($menus)
    {
        $i = 1;
        foreach ($menus as $code => &$menu) {
            $menu['code'] = $code;
            $menu['weight'] = $i * 100;

            ++$i;
            unset($menu);
        }

        foreach ($menus as &$menu) {
            if (!empty($menu['before']) && !empty($menus[$menu['before']]['weight'])) {
                $menu['weight'] = $menus[$menu['before']]['weight'] - 1;
            } elseif (!empty($menu['after']) && !empty($menus[$menu['after']]['weight'])) {
                $menu['weight'] = $menus[$menu['after']]['weight'] + 1;
            }

            unset($menu);
        }

        uasort(
            $menus,
            function ($a, $b) {
                return $a['weight'] > $b['weight'] ? 1 : -1;
            }
        );

        return $menus;
    }

    private function getCurrentUserPermissions()
    {
        $user = $this->getServiceKernel()->getCurrentUser();

        return $user->getPermissions();
    }

    protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform:AppService');
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
