<?php

namespace CorporateTrainingBundle\Twig;

use Codeages\PluginBundle\System\PluginableHttpKernelInterface;

class TwigLoader extends \Twig_Loader_Filesystem
{
    /**
     * @var PluginableHttpKernelInterface
     */
    private $kernel;

    public function __construct(PluginableHttpKernelInterface $kernel)
    {
        $this->kernel = $kernel;
        parent::__construct(array());
    }

    public function findTemplate($name, $throw = true)
    {
        $logicalName = (string) $name;

        if (isset($this->cache[$logicalName])) {
            return $this->cache[$logicalName];
        }

        $file = $this->getCustomFile($logicalName);

        if (is_null($file)) {
            $file = $this->getThemeFile($logicalName);
        }

        if (is_null($file)) {
            $file = $this->getTrainingBundleResourceFile($logicalName);
        }

        if ($file === false || null === $file) {
            if ($throw) {
                throw new \Twig_Error_Loader(sprintf('Unable to find template "%s".', $logicalName));
            }

            return false;
        }

        return $this->cache[$logicalName] = $file;
    }

    protected function getThemeFile($file)
    {
        if ($this->isAppResourceFile($file)) {
            $themeDir = $this->kernel->getPluginConfigurationManager()->getActiveThemeDirectory();
            $file = $themeDir.'/views/'.$file;
        }

        if (is_file($file)) {
            return $file;
        }

        return;
    }

    protected function getTrainingBundleResourceFile($file)
    {
        if ($this->isAppResourceFile($file)) {
            $file = $this->kernel->getRootDir().'/../src/CorporateTrainingBundle/Resources/views/'.$file;
        }

        if (is_file($file)) {
            return $file;
        }

        return;
    }

    protected function getCustomFile($file)
    {
        if ($this->isAppResourceFile($file)) {
            $file = $this->kernel->getRootDir().'/../src/CustomBundle/Resources/views/'.$file;
        }

        if (is_file($file)) {
            return $file;
        }

        return;
    }

    protected function isAppResourceFile($file)
    {
        return strpos((string) $file, '@') !== 0 && strpos((string) $file, ':') === false;
    }
}
