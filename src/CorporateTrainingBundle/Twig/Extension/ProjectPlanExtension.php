<?php

namespace CorporateTrainingBundle\Twig\Extension;

use Codeages\Biz\Framework\Context\Biz;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProjectPlanExtension extends \Twig_Extension
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
            new \Twig_SimpleFunction('projectPlan_metas', array($this, 'getProjectPlanMetas')),
        );
    }

    public function getProjectPlanMetas()
    {
        $projectPlans = $this->container->get('corporatetraining.extension.manager')->getProjectPlanItems();

        $projectPlans = array_map(function ($projectPlans) {
            return $projectPlans['meta'];
        }, $projectPlans);

        return $projectPlans;
    }

    public function getName()
    {
        return 'ct_projectPlan_twig';
    }
}
