<?php

namespace CorporateTrainingBundle\Command;

use AppBundle\Command\BaseCommand;
use AppBundle\Common\ArrayToolkit;
use Biz\Task\Dao\TaskResultDao;
use Biz\Task\Service\TaskResultService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckTaskResultCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('check:task-result');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $results = $this->getTaskResultService()->searchTaskResults(
            array(),
            array('createdTime' => 'DESC'),
            0,
            PHP_INT_MAX
        );

        $userTaskResults = ArrayToolkit::group($results, 'userId');
        foreach ($userTaskResults as $key => $userTaskResult) {
            $taskResults = ArrayToolkit::group($userTaskResult, 'courseTaskId');
            foreach ($taskResults as $task => $taskResult) {
                $recordsCount = count($taskResult);
                if ($recordsCount > 1) {
                    $neededDeleteIds = $this->pickUnneededTaskResultIds($taskResult);
                    foreach ($neededDeleteIds as $id) {
                        $this->getTaskResultDao()->delete($id);
                    }
                    $neededDeleteIds = implode($neededDeleteIds, ',');
                    $output->writeln("<info>Need Delete RecordId: #{$neededDeleteIds}</info>");
                    $output->writeln("<info>User #{$key}, courseTaskId #{$task} Repeat!, recordCount {$recordsCount}</info>");
                }
            }
        }

        $output->writeln('Success!');
    }

    protected function pickUnneededTaskResultIds($taskResults)
    {
        $ids = ArrayToolkit::column($taskResults, 'id');
        $has = false;
        foreach ($taskResults as $taskResult) {
            if (!empty($taskResult['finishedTime']) || !empty($taskResult['time']) || !empty($taskResult['watchTime']) || 'finish' === $taskResult) {
                $key = array_search($taskResult['id'], $ids);
                unset($ids[$key]);
                $has = true;
            }
        }

        if (!$has) {
            $neededId = min($ids);
            $key = array_search($neededId, $ids);
            unset($ids[$key]);
        }

        return $ids;
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }

    /**
     * @return TaskResultDao
     */
    protected function getTaskResultDao()
    {
        return $this->getBiz()->dao('Task:TaskResultDao');
    }
}
