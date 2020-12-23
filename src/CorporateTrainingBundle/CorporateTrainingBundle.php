<?php

namespace CorporateTrainingBundle;

use AppBundle\Common\ExtensionalBundle;
use Codeages\Biz\Framework\Dao\DaoProxy;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use CorporateTrainingBundle\DependencyInjection\Compiler\ExtensionPass;

class CorporateTrainingBundle extends ExtensionalBundle
{
    public function boot()
    {
        $biz = $this->container->get('biz');
        $directory = $this->getPath().DIRECTORY_SEPARATOR.'Migrations';
        if (is_dir($directory)) {
            $biz['migration.directories'][] = $directory;
        }

        $directory = $this->getPath().DIRECTORY_SEPARATOR.'Biz';
        if (is_dir($directory)) {
            $biz['autoload.aliases'][$this->getName()] = "{$this->getNamespace()}\\Biz";
        }

        $biz['autoload.object_maker.service'] = function ($biz) {
            return function ($namespace, $name) use ($biz) {
                $class = "{$namespace}\\Service\\Impl\\{$name}Impl";

                if (substr($namespace, 0, strlen('Biz\\')) === 'Biz\\') {
                    $ctNamespace = "{$this->getNamespace()}\\{$namespace}";
                    $ctClass = "{$ctNamespace}\\Service\\Impl\\{$name}Impl";
                    if (class_exists($ctClass)) {
                        $class = $ctClass;
                    }
                }

                return new $class($biz);
            };
        };

        $biz['autoload.object_maker.dao'] = function ($biz) {
            return function ($namespace, $name) use ($biz) {
                $class = "{$namespace}\\Dao\\Impl\\{$name}Impl";

                if (substr($namespace, 0, strlen('Biz\\')) === 'Biz\\') {
                    $ctNamespace = "{$this->getNamespace()}\\{$namespace}";
                    $ctClass = "{$ctNamespace}\\Dao\\Impl\\{$name}Impl";
                    if (class_exists($ctClass)) {
                        $class = $ctClass;
                    }
                }

                return new DaoProxy($biz, new $class($biz), $biz['dao.metadata_reader'], $biz['dao.serializer']);
            };
        };

        $this->container->get('api.resource.manager')->registerApi('CorporateTrainingBundle\Api');
    }

    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ExtensionPass());
    }

    public function getEnabledExtensions()
    {
        return array('DataTag', 'StatusTemplate', 'DataDict', 'NotificationTemplate');
    }

    public function getParent()
    {
        return 'AppBundle';
    }
}
