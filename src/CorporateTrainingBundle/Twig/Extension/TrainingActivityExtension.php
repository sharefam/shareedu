<?php

namespace CorporateTrainingBundle\Twig\Extension;

use Codeages\Biz\Framework\Context\Biz;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TrainingActivityExtension extends \Twig_Extension
{
    /**
     * @var Biz
     */
    protected $biz;

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container, Biz $biz)
    {
        $this->container = $container;
        $this->biz = $biz;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('training_activities', array($this, 'getTrainingActivities')),
        );
    }

    public function getTrainingActivities()
    {
        $projectPlans = $this->container->get('corporatetraining.extension.manager')->getTrainingActivities();

        $projectPlans = array_map(function ($projectPlans) {
            return $projectPlans['meta'];
        }, $projectPlans);

        return $projectPlans;
    }

    public function getName()
    {
        return 'ct_training_activity_twig';
    }
}
