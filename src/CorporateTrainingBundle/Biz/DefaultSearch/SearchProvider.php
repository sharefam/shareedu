<?php

namespace CorporateTrainingBundle\Biz\DefaultSearch;

use AppBundle\Extension\Extension;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class SearchProvider extends Extension implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $this->registerType($biz);
        $biz['search_factory'] = function ($biz) {
            $exporter = new SearchFactory();
            $exporter->setBiz($biz);

            return $exporter;
        };
    }

    protected function registerType($biz)
    {
        $biz['courseSearch'] = function ($biz) {
            return new CourseSearch($biz);
        };

        $biz['offlineActivitySearch'] = function ($biz) {
            return new OfflineActivitySearch($biz);
        };

        $biz['classroomSearch'] = function ($biz) {
            return new ClassroomSearch($biz);
        };

        $biz['projectPlanSearch'] = function ($biz) {
            return new ProjectPlanSearch($biz);
        };

        $biz['articleSearch'] = function ($biz) {
            return new ArticleSearch($biz);
        };

        $biz['threadSearch'] = function ($biz) {
            return new ThreadSearch($biz);
        };
    }
}
