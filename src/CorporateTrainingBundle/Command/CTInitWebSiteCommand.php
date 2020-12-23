<?php

namespace CorporateTrainingBundle\Command;

use AppBundle\Command\BaseCommand;
use AppBundle\Common\BlockToolkit;
use Biz\User\CurrentUser;
use CorporateTrainingBundle\Common\TrialInitializer;
use Symfony\Component\Console\Input\InputOption;
use Topxia\Service\Common\ServiceKernel;
use CorporateTrainingBundle\Common\SystemInitializer;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PDO;

class CTInitWebSiteCommand extends BaseCommand
{
    private $dbHost;
    private $dbPort;
    private $dbUser;
    private $dbPassword;
    private $dbName;

    protected function configure()
    {
        $this->setName('corporate-training:init-website')
            ->addArgument('nickname', InputArgument::REQUIRED, '初始超管用户名')
            ->addArgument('email', InputArgument::REQUIRED, '初始超管邮箱')
            ->addArgument('password', InputArgument::REQUIRED, '初始超管密码')
            ->addArgument('accessKey', InputArgument::OPTIONAL, '教育云accessKey')
            ->addArgument('secretKey', InputArgument::OPTIONAL, '教育云secretKey')
            ->addOption('dev', null, InputOption::VALUE_NONE, '初始化本地开发环境')
            ->addOption('trial', null, InputOption::VALUE_NONE, '初始化试用站配置')
            ->setDescription('初始化企培系统');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $initDbMode = $input->getOption('dev') ? 'dev' : 'prod';
        $this->initTableSchema($initDbMode, $output);
        $this->initServiceKernel();
        $initializer = new SystemInitializer($output);
        $initializer->setContainer($this->getContainer());

        $fields = array(
            'email' => $input->getArgument('email'),
            'nickname' => $input->getArgument('nickname'),
            'password' => $input->getArgument('password'),
            'createdIp' => '127.0.0.1',
        );

        $initializer->init();
        $user = $initializer->initAdminUser($fields);
        $initializer->initMagicSetting();
        $initializer->initFolders();
        $initializer->initLockFile();
        $initializer->initRegisterSetting($user);
        $initializer->initCourseSetting();
        $initializer->initClassroomSetting();

        $initializer->initCorporateTrainingDefaultSetting();
        $initializer->initArea();
        $initializer->initDiscoveryColumn();

        $initializer->initThemeConfig();
        $initializer->initSiteInfoSetting();
        $initializer->initQueueuJob();
        $this->getContainer()->get('kernel')->getPluginConfigurationManager()->setActiveThemeName('jianmo')->save();
        $accessKey = $input->getArgument('accessKey');
        $secretKey = $input->getArgument('secretKey');
        if (!empty($accessKey) && !empty($secretKey)) {
            $this->initCloud($accessKey, $secretKey);
        }

        $this->initTrialSetting($input->getOption('trial'));
    }

    protected function initTableSchema($mode, $output)
    {
        $this->logger('开始初始化...', $output);
        $result = 'dev' === $mode ? $this->devInit() : $this->prodinit();
        if ('success' === $result['status']) {
            $this->logger('数据库创建成功', $output);
        } else {
            $this->logger($result['message'], $output);
        }
    }

    private function devInit()
    {
        exec('./bin/phpmig migrate', $log, $status);
        if (0 === $status) {
            return array('status' => 'success');
        }

        return array('status' => 'error', 'message' => $log);
    }

    private function prodinit()
    {
        $this->dbHost = $this->getContainer()->getParameter('database_host');
        $this->dbPort = $this->getContainer()->getParameter('database_port');
        $this->dbName = $this->getContainer()->getParameter('database_name');
        $this->dbUser = $this->getContainer()->getParameter('database_user');
        $this->dbPassword = $this->getContainer()->getParameter('database_password');
        $result = $this->initDb();

        if (true === $result) {
            return array('status' => 'success');
        }

        return array('status' => 'error', 'message' => $result);
    }

    protected function initDb()
    {
        try {
            $pdo = new PDO(
                "mysql:dbname={$this->dbName};host={$this->dbHost};port={$this->dbPort}",
                "{$this->dbUser}",
                "{$this->dbPassword}"
            );
            $sqlFile = $this->getContainer()->getParameter('kernel.root_dir').'/../web/install/train.sql';
            $sql = file_get_contents($sqlFile);
            $result = $pdo->exec($sql);
            $pdo = null;
            if (false === $result) {
                return '创建数据库表结构失败，请删除数据库后重试';
            } else {
                $pdo = null;

                return true;
            }
        } catch (\PDOException $e) {
            return '数据库连接错误';
        }
    }

    protected function logger($message, $output)
    {
        $time = date('Y-m-d H:i:s');
        $log = "{$time}, {$message}";
        $loggerFile = $this->getContainer()->getParameter('kernel.root_dir').'/logs/edusoho-init.log';
        file_put_contents($loggerFile, $log.PHP_EOL, FILE_APPEND);
        $output->writeln($log);
    }

    protected function initCloud($accessKey, $secretKey)
    {
        $setting = array(
            'storage' => array(
                'upload_mode' => 'local',
                'cloud_access_key' => $accessKey,
                'cloud_secret_key' => $secretKey,
                'cloud_key_applied' => 1,
            ),
        );

        $this->initSetting($setting);
    }

    protected function initSetting($data)
    {
        foreach ($data as $key => $value) {
            $originValue = $this->getSettingService()->get($key, array());
            $value = array_merge($originValue, $value);
            $this->getSettingService()->set($key, $value);
        }
    }

    protected function initBlock($code)
    {
        $blockTemplate = $this->getBlockService()->getBlockTemplateByCode($code);
        $html = BlockToolkit::render($blockTemplate, $this->getContainer());
        $fields = array(
            'data' => $blockTemplate['data'],
            'content' => $html,
            'userId' => 1,
            'blockTemplateId' => $blockTemplate['id'],
            'code' => $code,
            'mode' => $blockTemplate['mode'],
        );
        $this->getBlockService()->createBlock($fields);
    }

    protected function initServiceKernel()
    {
        $serviceKernel = ServiceKernel::create('dev', true);
        $currentUser = new CurrentUser();
        $currentUser->fromArray(
            array(
                'id' => 0,
                'nickname' => '游客',
                'currentIp' => '127.0.0.1',
                'roles' => array('ROLE_SUPER_ADMIN'),
                'orgId' => 1,
            )
        );
        $serviceKernel->setCurrentUser($currentUser);
    }

    private function initTrialSetting($isTrial)
    {
        if (!$isTrial) {
            return;
        }

        $trialInitializer = new TrialInitializer();
        $trialInitializer->init();
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System:SettingService');
    }

    protected function getBlockService()
    {
        return $this->getServiceKernel()->createService('Content:BlockService');
    }
}
