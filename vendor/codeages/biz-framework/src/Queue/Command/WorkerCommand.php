<?php

namespace Codeages\Biz\Framework\Queue\Command;

use AppBundle\Common\ArrayToolkit;
use Biz\Role\Util\PermissionBuilder;
use Biz\User\CurrentUser;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Context\AbstractCommand;
use Codeages\Biz\Framework\Queue\Worker;
use CorporateTrainingBundle\Biz\Org\Service\OrgService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Topxia\Service\Common\ServiceKernel;

class WorkerCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('queue:work')
            ->setDescription('Start processing jobs on the queue')
            ->addArgument('name', InputArgument::REQUIRED, 'Queue name')
            ->addArgument('process-no', InputArgument::REQUIRED, 'Process No.')
            ->addOption('once', null, InputOption::VALUE_NONE, 'Only process the next job on the queue')
            ->addOption('tries', null, InputOption::VALUE_OPTIONAL, 'The number of seconds a child process can run', 0)
            ->addOption('stop-when-idle', null, InputOption::VALUE_NONE, 'Worker stop when no jobs');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $queueName = $input->getArgument('name') ?: 'default';

        $queue = $this->biz['queue.connection.'.$queueName];

        $options = array(
            'once' => $input->getOption('once'),
            'stop_when_idle' => $input->getOption('stop-when-idle'),
            'tries' => (int) $input->getOption('tries'),
            'lock_file' => sprintf('%s/queue-worker-%s-%s.lock', $this->biz['run_dir'], $queueName, $input->getArgument('process-no')),
        );

        $this->initServiceKernel();
        $lock = $this->biz['lock.factory']->createLock(sprintf('queue-worker-%s-%s', $queueName, $input->getArgument('process-no')));

        $worker = new Worker($queue, $this->biz['queue.failer'], $lock, $this->biz['logger'], $options);
        $worker->run();
    }

    protected function initServiceKernel()
    {
        $_SERVER['HTTP_HOST'] = '127.0.0.1';
        $serviceKernel = ServiceKernel::create('dev', false);
//        $serviceKernel->setParameterBag($this->getContainer()->getParameterBag());
        $serviceKernel->setBiz($this->biz);

        $currentUser = new CurrentUser();
        $systemUser = $this->getUserService()->getUserByType('system');
        $systemUser['currentIp'] = '127.0.0.1';
//        $systemUser['accessibleOrgIds'] = $this->getUserAccessibleOrgIds($systemUser);
        $currentUser->fromArray($systemUser);
        $currentUser->setPermissions(PermissionBuilder::instance()->getPermissionsByRoles($currentUser->getRoles()));
        $serviceKernel->setCurrentUser($currentUser);
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }
}
