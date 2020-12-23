<?php

namespace CorporateTrainingBundle\Command;

use AppBundle\Command\BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Inline;

class CTImporterI18nKeyCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('corporate-training:importer-i18n-key')
            ->addArgument('xlsPathCn', InputArgument::REQUIRED, 'xls中文文件路径')
            ->setDescription('导入国际化词条到根目录下keys.xls');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $parameters = array();
        $parameters['xlsPathCn'] = $input->getArgument('xlsPathCn');
        $exporter = new KeyImporter();
        $exporter->importer($parameters);
    }
}

class KeyImporter
{
    public function importer($parameters)
    {
        $objPHPExcel = \PHPExcel_IOFactory::load($parameters['xlsPathCn']);
        $sheetCount = $objPHPExcel->getSheetCount();

        $data = array();
        for ($sheet = 0; $sheet < $sheetCount; ++$sheet) {
            $result = array();
            $currentSheet = $objPHPExcel->setActiveSheetIndex($sheet);
            $title = $currentSheet->getTitle();
            $highestColumn = $currentSheet->getHighestColumn();
            $highestRow = $currentSheet->getHighestRow();

            for ($row = 2; $row <= $highestRow; ++$row) {
                $currentRawData = $this->buildCurrentRawData($currentSheet, $row, $highestColumn);
                $result[] = $currentRawData;
            }
            $data[$title] = $result;
        }

        foreach ($data  as $key => $value) {
            $path = $this->getMessageTypeByTitle($key);
            if (!empty($path)) {
                file_put_contents($path, $this->dump($data[$key]));
            }
        }
    }

    public function dump($input, $inline = 2, $indent = 0, $exceptionOnInvalidType = false, $objectSupport = false)
    {
        $output = '';
        $prefix = $indent ? str_repeat('', $indent) : '';

        if ($inline <= 0 || !is_array($input) || empty($input)) {
            $output .= $prefix.Inline::dump($input, $exceptionOnInvalidType, $objectSupport);
        } else {
            $isAHash = Inline::isHash($input);

            foreach ($input as $key => $value) {
                $willBeInlined = $inline - 1 <= 0 || !is_array($value) || empty($value);

                $output .= sprintf('%s%s%s%s',
                        $prefix,
                        $isAHash ? Inline::dump($key, $exceptionOnInvalidType, $objectSupport).':' : '',
                        $willBeInlined ? ' ' : '',
                        $this->dump($value, $inline - 1, $willBeInlined ? 0 : $indent + 2, $exceptionOnInvalidType, $objectSupport)
                    ).($willBeInlined ? "\n" : '');
            }
        }

        return $output;
    }

    /*
     * exam feature/39992-exam-locale-key-extract
     * ldap feature/39994-ldap-internationalization
     * survey feature/39964-locale-key-extract
     * postMap feature/39964-locale-key-extract
     * QA feature/39964-locale-key-extract
     * QuestionPlus feature/39964-locale-key-extract
     * RewardPoint feature/39964-locale-key-extract
     * UserImporter feature/39964-locale-key-extract
     */
    protected function buildCurrentRawData(\PHPExcel_Worksheet $currentSheet, $currentRow, $highestColumn)
    {
        $highestColumn = \PHPExcel_Cell::columnIndexFromString($highestColumn);
        $data = array();

        for ($i = 0; $i < $highestColumn; ++$i) {
            $value = $currentSheet->getCellByColumnAndRow($i, $currentRow)->getValue();
            if (!empty($value) && $value != null) {
                $data[$i] = $value;
            }
        }

        $result[$data[0]] = empty($data[2]) ? '' : $data[2];

        return $result;
    }

    protected function getMessageTypeByTitle($title)
    {
        switch ($title) {
            case 'ct-message':
                return 'src/CorporateTrainingBundle/Resources/translations/messages.en.yml';
            case 'ct-js':
                return 'src/CorporateTrainingBundle/Resources/translations/js.en.yml';
            case '用户导入导出插件-message':
                return 'plugins/UserImporterPlugin/Resources/translations/messages.en.yml';
            case '积分插件-message':
                return 'plugins/RewardPointPlugin/Resources/translations/messages.en.yml';
            case '积分插件-js':
                return 'plugins/RewardPointPlugin/Resources/translations/js.en.yml';
            case '问答频道插件-message':
                return 'plugins/QAPlugin/Resources/translations/messages.en.yml';
            case '问答频道插件-js':
                return 'plugins/QAPlugin/Resources/translations/js.en.yml';
            case '调查问卷插件-message':
                return 'plugins/SurveyPlugin/Resources/translations/messages.en.yml';
            case '调查问卷插件-js':
                return 'plugins/SurveyPlugin/Resources/translations/js.en.yml';
            case '专项考试插件-message':
                return 'plugins/ExamPlugin/Resources/translations/messages.en.yml';
            case '专项考试插件-js':
                return 'plugins/ExamPlugin/Resources/translations/js.en.yml';
            case '岗位地图插件-message':
                return 'plugins/PostMapPlugin/Resources/translations/messages.en.yml';
            case '岗位地图插件-js':
                return 'plugins/PostMapPlugin/Resources/translations/js.en.yml';
            case 'LDAP插件-message':
                return 'plugins/LDAPPlugin/Resources/translations/messages.en.yml';
            case 'base-message':
                return 'app/Resources/translations/messages.en.yml';
            case 'base-js':
                return 'app/Resources/translations/js.en.yml';
            default:
                return '';
        }
    }
}
