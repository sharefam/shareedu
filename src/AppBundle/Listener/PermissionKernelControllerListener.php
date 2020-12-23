<?php

namespace AppBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

class PermissionKernelControllerListener
{
    protected $paths;

    public function __construct(ContainerInterface $container, $path)
    {
        $this->container = $container;
        $this->path = $path;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        if ($event->getRequestType() != HttpKernelInterface::MASTER_REQUEST) {
            return;
        }

        $request = $event->getRequest();
        $currentUser = ServiceKernel::instance()->getCurrentUser();
        $requestPath = $request->getPathInfo();

        if (!in_array('ROLE_SUPER_ADMIN', $currentUser['roles'])) {
            $route = $this->container
                ->get('router')
                ->getMatcher()
                ->match($request->getPathInfo());

            $permissions = empty($route['_permission']) ? array() : $route['_permission'];

            if (empty($permissions)) {
                return;
            }

            foreach ($permissions as $permission) {
                if ($currentUser->hasPermission($permission)) {
                    return;
                }
            }

            $self = $this;

            if (!preg_match('/^\/admin/', $requestPath)) {
                $event->setController(function () use ($self, $request) {
                    return $self->container->get('templating')->renderResponse('role/permission-error.html.twig');
                });
            } else {
                $event->setController(function () use ($self, $request) {
                    return $self->container->get('templating')->renderResponse('admin/role/permission-error.html.twig');
                });
            }
        }
    }
}
