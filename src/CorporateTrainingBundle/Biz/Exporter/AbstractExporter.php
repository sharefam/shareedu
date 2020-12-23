<?php

namespace CorporateTrainingBundle\Biz\Exporter;

use AppBundle\Common\ArrayToolkit;
use Codeages\Biz\Framework\Context\Biz;
use Codeages\PluginBundle\System\PluginConfigurationManager;
use CorporateTrainingBundle\Biz\Course\Service\CourseService;
use CorporateTrainingBundle\Biz\Org\Service\Impl\OrgServiceImpl;
use CorporateTrainingBundle\Biz\User\Service\UserOrgService;
use Topxia\Service\Common\ServiceKernel;

abstract class AbstractExporter
{
    protected $biz;

    protected $startRow = 1;

    protected $objExcel;

    protected $activeSheet;

    protected $titleColNum;

    protected $serviceContainer;

    abstract public function canExport($parameters);

    abstract public function getExportFileName();

    abstract protected function getSortedHeadingRow($parameters);

    abstract protected function buildExportData($parameters);

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    public function writeToExcel($parameters)
    {
        $this->setTitleColNumber($parameters);
        $exportData = $this->buildExportData($parameters);

        $excel = new Excel();
        $this->objExcel = $excel->createPHPExcelObject();

        foreach ($exportData as $key => $sheetExportData) {
            $this->objExcel->createSheet();
            $this->activeSheet = $this->objExcel->setActiveSheetIndex($key);
            $this->writeExcelHeadingRow($parameters);
            $this->writeExportDataToSheet($sheetExportData['data']);
            if (!empty($sheetExportData['sheetName'])) {
                if (\PHPExcel_Shared_String::CountCharacters($sheetExportData['sheetName']) > 31) {
                    $sheetExportData['sheetName'] = \PHPExcel_Shared_String::Substring($sheetExportData['sheetName'], 0,
                        31);
                }
                $invalidCharacters = $this->activeSheet->getInvalidCharacters();
                $sheetTitle = str_replace($invalidCharacters, '', $sheetExportData['sheetName']);
                $this->activeSheet->setTitle($sheetTitle);
            }
        }
        $this->objExcel->setActiveSheetIndex(0);

        return $excel->createWriter($this->objExcel);
    }

    public function getServiceContainer()
    {
        return $this->serviceContainer;
    }

    public function setServiceContainer($container)
    {
        return $this->serviceContainer = $container;
    }

    protected function writeExportDataToSheet($sheetExportData)
    {
        foreach ($sheetExportData as $key => $exportData) {
            foreach ($exportData as $k => $v) {
                if (array_key_exists($k, $this->titleColNum)) {
                    $colNum = $this->titleColNum[$k]['colNumber'];
                    $rowNum = $this->startRow + $key + 1;
                    $v = $this->filter_Emoji($v);
                    $this->activeSheet->setCellValue($colNum.$rowNum, $v);
                }
            }
        }
    }

    protected function writeExcelHeadingRow($parameters)
    {
        $sortedHeadingRows = $this->getSortedHeadingRow($parameters);
        foreach ($sortedHeadingRows as $row) {
            $colNum = $this->titleColNum[$row['code']]['colNumber'];
            $title = $this->filter_Emoji($row['title']);
            $this->activeSheet->setCellValue($colNum.$this->startRow, $title);
        }
    }

    protected function setTitleColNumber($parameters)
    {
        $sortedHeadingRows = $this->getSortedHeadingRow($parameters);

        $titleColNum = array();

        foreach ($sortedHeadingRows as $key => $headingRow) {
            $titleColNum[] = array(
                'code' => $headingRow['code'],
                'colNumber' => \PHPExcel_Cell::stringFromColumnIndex($key),
            );
        }

        $this->titleColNum = ArrayToolkit::index($titleColNum, 'code');
    }

    protected function filter_Emoji($str)
    {
        $str = preg_replace_callback(
            '/./u',
            function (array $match) {
                return strlen($match[0]) >= 4 ? '[emoji]' : $match[0];
            },
            $str);

        return $str;
    }

    protected function trans($message, $arguments = array(), $domain = null, $locale = null)
    {
        return ServiceKernel::instance()->trans($message, $arguments, $domain, $locale);
    }

    protected function createService($alias)
    {
        return $this->biz->service($alias);
    }

    protected function isPluginInstalled($pluginName)
    {
        $pluginManager = new PluginConfigurationManager(ServiceKernel::instance()->getParameter('kernel.root_dir'));

        return $pluginManager->isPluginInstalled($pluginName);
    }

    protected function fillOrgCode($conditions)
    {
        global $kernel;

        if (isset($conditions['orgCode']) && !$this->biz['user']->hasManagePermissionWithOrgCode($conditions['orgCode'])) {
            $conditions['likeOrgCode'] = '-1';

            return $conditions;
        }

        if ($kernel->getContainer()->get('web.twig.extension')->getSetting('magic.enable_org', null)) {
            if (!isset($conditions['orgCode'])) {
                $orgCodes = $this->biz['user']->getManageOrgCodes();
                $conditions['likeOrgCode'] = empty($orgCodes) ? '-1' : $orgCodes[0];
            } else {
                $conditions['likeOrgCode'] = $conditions['orgCode'];
                unset($conditions['orgCode']);
            }
        } else {
            if (isset($conditions['orgCode'])) {
                unset($conditions['orgCode']);
            }
        }

        return $conditions;
    }

    protected function buildUsersData($userIds)
    {
        $userProfiles = $this->getUserService()->findUserProfilesByIds($userIds);
        $userProfiles = ArrayToolkit::index($userProfiles, 'id');

        $users = $this->getUserService()->findUsersByIds($userIds);
        $users = ArrayToolkit::index($users, 'id');

        $orgIds = array();

        foreach ($users as $user) {
            $orgIds = array_merge($orgIds, $user['orgIds']);
        }
        $orgIds = array_values(array_unique($orgIds));
        $orgs = $this->getOrgService()->findOrgsByIds($orgIds);
        $orgs = ArrayToolkit::index($orgs, 'id');

        $posts = $this->getPostService()->findPostsByIds(ArrayToolkit::column($users, 'postId'));
        $posts = ArrayToolkit::index($posts, 'id');

        return array($users, $userProfiles, $orgs, $posts);
    }

    /**
     * @return \CorporateTrainingBundle\Biz\Post\Service\Impl\PostServiceImpl
     */
    protected function getPostService()
    {
        return $this->createService('CorporateTrainingBundle:Post:PostService');
    }

    /**
     * @return UserOrgService
     */
    protected function getUserOrgService()
    {
        return $this->createService('CorporateTrainingBundle:User:UserOrgService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\User\Service\Impl\UserServiceImpl
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return OrgServiceImpl
     */
    protected function getOrgService()
    {
        return $this->createService('Org:OrgService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\Course\Service\Impl\CourseSetServiceImpl
     */
    protected function getCourseSetService()
    {
        return $this->createService('CorporateTrainingBundle:Course:CourseSetService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ProjectPlan\Service\Impl\ProjectPlanServiceImpl
     */
    protected function getProjectPlanService()
    {
        return $this->createService('CorporateTrainingBundle:ProjectPlan:ProjectPlanService');
    }

    /**
     * @return \Biz\Testpaper\Service\Impl\TestpaperServiceImpl
     */
    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }
}
