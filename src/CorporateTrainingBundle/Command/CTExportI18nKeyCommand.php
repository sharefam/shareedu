<?php

namespace CorporateTrainingBundle\Command;

use AppBundle\Command\BaseCommand;
use CorporateTrainingBundle\Biz\Exporter\AbstractExporter;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class CTExportI18nKeyCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('corporate-training:export-i18n-key')
            ->addArgument('ymlPathCn', InputArgument::REQUIRED, 'yml中文文件路径')
            ->addArgument('ymlPathEn', InputArgument::REQUIRED, 'yml英文文件路径')
            ->setDescription('导出国际化词条到根目录下keys.xls');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $parameters = array();
        $parameters['ymlPathCn'] = $input->getArgument('ymlPathCn');
        $parameters['ymlPathEn'] = $input->getArgument('ymlPathEn');
        $exporter = new KeyExporter($this->getBiz());
        $objWriter = $exporter->writeToExcel($parameters);
        $objWriter->save($exporter->getExportFileName());
    }
}

class KeyExporter extends AbstractExporter
{
    public function canExport($parameters)
    {
        return true;
    }

    public function getExportFileName()
    {
        return 'i18n_key_'.time().'.xls';
    }

    protected function getSortedHeadingRow($parameters)
    {
        return array(
            array('code' => 'key', 'title' => '词条KEY'),
            array('code' => 'cn', 'title' => '中文'),
            array('code' => 'en', 'title' => '英文'),
        );
    }

    protected function buildExportData($parameters)
    {
        $ymlCnContent = Yaml::parse(file_get_contents($parameters['ymlPathCn']));
        $ymlEnContent = Yaml::parse(file_get_contents($parameters['ymlPathEn']));
        $data = array();
        foreach ($ymlCnContent as $key => $value) {
            $en = ' ';
            if (isset($ymlEnContent[$key])) {
                $en = $ymlEnContent[$key];
            }
            $data[] = array(
                'key' => $key,
                'cn' => $value,
                'en' => $en,
            );
        }
        $exportData[] = array(
            'data' => $data,
        );

        return $exportData;
    }
}
