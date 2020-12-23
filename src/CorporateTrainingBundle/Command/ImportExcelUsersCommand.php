<?php

namespace CorporateTrainingBundle\Command;

use AppBundle\Command\BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportExcelUsersCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('util:import-excel-user')
            ->addArgument('filePath', InputArgument::REQUIRED, '导入excel的路径')
            ->setDescription('excel导入用户，模板和web端用户导入一直');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $filePath = $input->getArgument('filePath');

        if (!file_exists($filePath)) {
            $io->error('文件不存在!');

            return;
        }

        $currentUser = $this->getServiceKernel()->getCurrentUser();
        $currentUser['orgCodes'] = array('1.');
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $importer = $this->getImporterFactory('user');

        $io->success('导入初始化成功, 开始检查excel数据...');

        $checkResult = $importer->doCheck('ignore', $filePath, 50000);

        if ('error' == $checkResult['status']) {
            $errorInfo = $checkResult['errorInfo'];
            $io->error($errorInfo);

            return;
        } elseif ('danger' == $checkResult['status']) {
            $errorInfo = $checkResult['message'];
            $io->error($errorInfo);

            return;
        } else {
            $info = $checkResult['checkInfo'];
            $info[] = 'excel通过数据格式检查, 开始导入...';
            $io->success($info);
        }

        $rowCount = count($checkResult['importData']);

        $io->progressStart($rowCount);

        $row = 0;
        $step = 10;
        while ($row < $rowCount) {
            $rowData = array_slice($checkResult['importData'], $row, $step);
            $importer->doImport('ignore', $rowData);
            $io->progressAdvance(count($rowData));
            $row = $row + $step;
        }
        $io->progressFinish();
        $io->success("导入成功，共导入{$rowCount}条用户");
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }

    protected function getImporterFactory($importType)
    {
        $biz = $this->getBiz();

        return $biz["importer.{$importType}"];
    }
}
