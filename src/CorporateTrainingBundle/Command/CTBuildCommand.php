<?php

namespace CorporateTrainingBundle\Command;

use AppBundle\Command\BaseCommand;
use CorporateTrainingBundle\System;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

class CTBuildCommand extends BaseCommand
{
    const PROJECT_NAME = 'edusoho';

    private $input;

    private $output;

    private $fileSystem;

    private $rootDir;

    private $buildDir;

    private $distDir;

    private $packageConfig;

    private $host;

    private $database;

    private $dbUser;

    private $dbPassword;

    protected function configure()
    {
        $this->setName('corporate-training:install-package')
            ->addArgument('host', InputArgument::REQUIRED, '服务器地址')
            ->addArgument('database', InputArgument::REQUIRED, '数据库名称')
            ->addArgument('dbUser', InputArgument::REQUIRED, '数据库用户名')
            ->addArgument('dbPassword', InputArgument::REQUIRED, '数据库密码')
            ->setDescription('自动编制内训版安装包');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initServiceKernel();

        $this->intBuild($input, $output);

        $output->writeln('<info>Start build.</info>');

        $this->buildDatabase();
        $this->createRootDirs();
        $this->buildDirFiles();
        $this->buildWebBundles();
        $this->clean();

        $this->_zipPackage();
        $output->writeln('<info>End build.</info>');
    }

    protected function buildDataBase()
    {
        $this->output->writeln('<info>开始导出数据库脚本..</info>');

        if ('127.0.0.1' == trim($this->host) || 'localhost' == trim($this->host)) {
            $command = "mysqldump -u{$this->dbUser} -p{$this->dbPassword} -d {$this->database} --compact --add-drop-table | sed 's/ AUTO_INCREMENT=[0-9]*//g' > {$this->rootDir}/web/install/train.sql";

            $this->output->writeln("<info>{$command}</info>");
            exec($command);
        } else {
            $time = time();

            $command = "ssh -l root {$this->host} \"mysqldump -u{$this->dbUser} -p{$this->dbPassword} -d {$this->database} --compact --add-drop-table | sed 's/ AUTO_INCREMENT=[0-9]*//g' > edusoho_structure.{$time}.sql\"";

            $this->output->writeln("<info>{$command}</info>");
            exec($command);

            $command = "scp root@{$this->host}:~/edusoho_structure.{$time}.sql {$this->rootDir}/web/install/train.sql";
            $this->output->writeln("<info>{$command}</info>");
            exec($command);
        }
    }

    protected function intBuild(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;

        $this->host = $input->getArgument('host');
        $this->database = $input->getArgument('database');
        $this->dbUser = $input->getArgument('dbUser');
        $this->dbPassword = $input->getArgument('dbPassword');

        $this->output = $output;
        $this->fileSystem = new Filesystem();

        $this->rootDir = realpath($this->getContainer()->getParameter('kernel.root_dir').'/../');
        $this->buildDir = $this->rootDir.DIRECTORY_SEPARATOR.'build';
        if ($this->fileSystem->exists($this->buildDir)) {
            $this->fileSystem->remove($this->buildDir);
        }
        $this->distDir = $this->buildDir.DIRECTORY_SEPARATOR.self::PROJECT_NAME;

        $this->fileSystem->mkdir($this->distDir);

        $packageConfigFile = $this->rootDir.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'CorporateTrainingBundle'.DIRECTORY_SEPARATOR.'Command'.DIRECTORY_SEPARATOR.'package_config.yml';
        $this->packageConfig = Yaml::parse($packageConfigFile);
    }

    protected function createRootDirs()
    {
        $neededCreatedRootDirs = $this->packageConfig['needed_created_root_dirs'];
        foreach ($neededCreatedRootDirs as $dir) {
            $this->output->writeln("<info>  Create RootDir {$dir}</info>");
            $this->fileSystem->mkdir("{$this->distDir}/$dir");
        }
    }

    protected function buildDirFiles()
    {
        $neededBuildDirs = $this->packageConfig['needed_build_dirs'];
        foreach ($neededBuildDirs as $key => $dir) {
            if (!empty($neededBuildDirs[$key]['needed_created_dirs'])) {
                foreach ($neededBuildDirs[$key]['needed_created_dirs'] as $dir) {
                    $this->fileSystem->mkdir($this->distDir.'/'.$key.'/'.$dir);
                    $this->output->writeLn("<info>      mkdir {$dir} in {$key}</info>");
                }
            }

            if (!empty($neededBuildDirs[$key]['needed_mirrors'])) {
                foreach ($neededBuildDirs[$key]['needed_mirrors'] as $neededMirror) {
                    $this->fileSystem->mirror($this->rootDir.$neededMirror, $this->distDir.$neededMirror);
                    $this->output->writeLn("<info>      mirror {$neededMirror} in {$key}</info>");
                }
            }

            if (!empty($neededBuildDirs[$key]['needed_remove'])) {
                foreach ($neededBuildDirs[$key]['needed_remove'] as $neededRemove) {
                    $this->fileSystem->remove($this->distDir.$neededRemove);
                    $this->output->writeLn("<info>      remove {$neededRemove} in {$key}</info>");
                }
            }

            if (!empty($neededBuildDirs[$key]['needed_copy'])) {
                foreach ($neededBuildDirs[$key]['needed_copy'] as $neededCopy) {
                    $this->fileSystem->copy($this->rootDir.$neededCopy, $this->distDir.$neededCopy);
                    $this->output->writeLn("<info>      copy {$neededCopy} in {$key}</info>");
                }
            }

            if (!empty($neededBuildDirs[$key]['needed_target_copy'])) {
                foreach ($neededBuildDirs[$key]['needed_target_copy'] as $key => $targetCopy) {
                    $this->fileSystem->copy($this->distDir.$key, $this->distDir.$targetCopy);
                    $this->output->writeLn("<info>      target copy {$targetCopy} in {$key}</info>");
                }
            }

            if (!empty($neededBuildDirs[$key]['needed_touch'])) {
                foreach ($neededBuildDirs[$key]['needed_touch'] as $neededTouch) {
                    $this->fileSystem->touch($this->distDir.$neededTouch);
                    $this->output->writeLn("<info>      touch {$neededTouch} in {$key}</info>");
                }
            }

            if (!empty($neededBuildDirs[$key]['needed_chmod'])) {
                foreach ($neededBuildDirs[$key]['needed_chmod'] as $needChmod) {
                    $this->fileSystem->chmod($this->distDir.$needChmod, 0777);
                    $this->output->writeLn("<info>      chmod {$needChmod} in {$key}</info>");
                }
            }
        }

        if ($this->fileSystem->exists($this->rootDir.'/README.txt')) {
            $this->fileSystem->copy($this->rootDir.'/README.txt', $this->distDir.'/README.txt');
            $this->output->writeLn("<info>      copy README.txt in {$this->distDir}</info>");
        }
    }

    protected function buildWebBundles()
    {
        $finder = new Finder();
        $finder->directories()->in("{$this->rootDir}/web/bundles")->depth('== 0');
        $needs = array(
            'sensiodistribution',
            'topxiaadmin',
            'framework',
            'topxiaweb',
            'customweb',
            'customadmin',
            'topxiamobilebundlev2',
            'classroom',
            'sensitiveword',
            'materiallib',
            'org',
            'permission',
            'bazingajstranslation',
            'translations',
            'corporatetraining',
        );

        foreach ($finder as $dir) {
            if (!in_array($dir->getFilename(), $needs)) {
                continue;
            }

            $this->fileSystem->mirror($dir->getRealpath(), "{$this->distDir}/web/bundles/{$dir->getFilename()}");
        }
    }

    private function _zipPackage()
    {
        $this->output->writeln('build installation package  use: zip -r edusoho-ct-'.System::VERSION.'.zip edusoho/');
        $this->output->writeln('packaging...');
        chdir($this->buildDir);
        $command = 'zip -r edusoho-ct-'.System::CT_VERSION.'.zip edusoho/';
        exec($command);
    }

    protected function clean()
    {
        $this->revertDBConfigToDefault();
        $this->cleanMacOsDirectory();
        $this->cleanVendor();
    }

    protected function revertDBConfigToDefault()
    {
        $this->fileSystem->copy($this->distDir.'/app/config/parameters.yml.dist', $this->distDir.'/app/config/parameters.yml');
    }

    protected function cleanMacOsDirectory()
    {
        $finder = new Finder();
        $finder->files()->in($this->distDir)->ignoreDotFiles(false);

        foreach ($finder as $dir) {
            if ('.DS_Store' == $dir->getBasename()) {
                $this->fileSystem->remove($dir->getRealpath());
                $this->output->writeln('<info>clean .DS_Store</info>');
            }
        }
    }

    protected function cleanVendor()
    {
        $buildVendorDir = $this->distDir.DIRECTORY_SEPARATOR.'vendor';
        $finder = new Finder();
        $finder->directories()->in($buildVendorDir);

        $targetDirs = array();
        foreach ($finder as $dir) {
            $vendorDir = substr($dir, strpos($dir, 'vendor') + strlen('vendor'.DIRECTORY_SEPARATOR));
            $targetDir = $buildVendorDir.DIRECTORY_SEPARATOR.$vendorDir;
            if ($dir->isDir() && $dir->isReadable()) {
                $targetDirs[] = $targetDir;
            }
        }

        $unneededFiles = array(
            'appveyor.yml',
            'CONTRIBUTING.md',
            'CONTRIBUTORS.md',
            'phpunit',
            '.gitignore',
            '.travis.yml',
            'CHANGELOG.md',
            'composer.json',
            'phpunit.xml.dist',
            'Gemfile',
            'README.md',
            'UPGRADE.md',
            'VERSION',
            'CHANGES',
            '.gitattributes',
            '.DS_Store',
        );

        $needRemove = array(
            $this->buildDir.DIRECTORY_SEPARATOR.'composer/installed.json',
        );

        $finder = new Finder();
        $finder->directories()->in($this->distDir.DIRECTORY_SEPARATOR.'vendor');
        $this->output->writeln('<info>      removing vendor unneeded dir </info>');
        foreach ($finder as $dir) {
            $dirName = $dir->getFilename();
            if ('tests' === lcfirst($dirName)) {
                $needRemove[] = $dir->getRealPath();
            }
        }

        $finder = new Finder();
        $finder->files()->ignoreDotFiles(false)->in($targetDirs);
        $this->output->writeln('<info>      removing vendor unneeded file </info>');
        foreach ($finder as $file) {
            if (in_array($file->getFilename(), $unneededFiles, true)) {
                $needRemove[] = $file->getRealPath();
            }
        }

        $this->fileSystem->remove($needRemove);
    }
}
