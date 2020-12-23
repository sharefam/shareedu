<?php

namespace CorporateTrainingBundle\Command;

use AppBundle\Command\BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class CTInitUpgradeScriptCommand extends BaseCommand
{
    protected $remote = 'git@coding.codeages.net:corporate-training/upgradescripts.git';

    protected function configure()
    {
        $this->setName('corporate-training:init-upgrade-script')
            ->addArgument('version', InputArgument::REQUIRED, 'version name')
            ->setDescription('生成内训版升级脚本模版');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fileSystem = new Filesystem();
        $rootDir = $this->getContainer()->getParameter('kernel.root_dir').DIRECTORY_SEPARATOR.'../';
        $scriptsDir = $rootDir.'scripts';
        if (!$fileSystem->exists($scriptsDir)) {
            $command = "git clone $this->remote scripts";
            exec($command);
        }

        $version = $input->getArgument('version');

        if (file_exists($scriptsDir.DIRECTORY_SEPARATOR.'upgrade-'.$version.'.php')) {
            $output->writeln('<error>Script has exists</error>');
            exit;
        }

        if (!file_exists($scriptsDir.DIRECTORY_SEPARATOR.'upgrade.php.tpl')) {
            $output->writeln('<error>Template not exist</error>');
            exit;
        }

        $fileSystem->copy($scriptsDir.DIRECTORY_SEPARATOR.'upgrade.php.tpl', $scriptsDir.DIRECTORY_SEPARATOR.'upgrade-'.$version.'.php');
        $output->writeln('<info>The script has init, please take care of it</info>');
    }
}
