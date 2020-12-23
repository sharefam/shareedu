<?php

namespace CorporateTrainingBundle\Biz\DiscoveryColumn;

use AppBundle\Extension\Extension;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class DiscoveryColumnServiceProvider extends Extension implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $this->registerColumnType($biz);
        $biz['column_factory'] = function ($biz) {
            $column = new DiscoveryColumnFactory();
            $column->setBiz($biz);

            return $column;
        };
    }

    protected function registerColumnType($biz)
    {
        $biz['column.course'] = function ($biz) {
            return new CourseColumn($biz);
        };
        $biz['column.live'] = function ($biz) {
            return new CourseColumn($biz);
        };
        $biz['column.classroom'] = function ($biz) {
            return new ClassroomColumn($biz);
        };
        $biz['column.publicCourse'] = function ($biz) {
            return new PublicCourseColumn($biz);
        };
        $biz['column.departmentCourse'] = function ($biz) {
            return new DepartmentCourseColumn($biz);
        };
        $biz['column.offlineActivity'] = function ($biz) {
            return new OfflineActivityColumn($biz);
        };
        $biz['column.projectPlan'] = function ($biz) {
            return new ProjectPlanColumn($biz);
        };
    }
}
