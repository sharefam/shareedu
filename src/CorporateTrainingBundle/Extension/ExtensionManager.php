<?php

namespace CorporateTrainingBundle\Extension;

class ExtensionManager
{
    protected $extensions = array();

    protected $projectPlanItems = array();

    protected $trainingActivities = array();

    protected $orgModules = array();

    protected $userLearnDataModules = array();

    public function addExtension(Extension $extension)
    {
        $this->projectPlanItems = array_merge($this->projectPlanItems, $extension->getProjectPlanItems());
        $this->trainingActivities = array_merge($this->trainingActivities, $extension->getTrainingActivities());
        $this->orgModules = array_merge($this->orgModules, $extension->getOrgModules());
        $this->userLearnDataModules = array_merge($this->userLearnDataModules, $extension->getUserLearnDataModules());
    }

    public function getProjectPlanItems()
    {
        return $this->projectPlanItems;
    }

    public function getTrainingActivities()
    {
        return $this->trainingActivities;
    }

    public function getOrgModules()
    {
        return $this->orgModules;
    }

    public function getUserLearnDataModules()
    {
        return $this->userLearnDataModules;
    }
}
