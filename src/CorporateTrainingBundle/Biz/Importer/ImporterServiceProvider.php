<?php

namespace CorporateTrainingBundle\Biz\Importer;

use AppBundle\Extension\Extension;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ImporterServiceProvider extends Extension implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $biz['importer.batch-grade'] = function ($biz) {
            return new BatchGradeImporter($biz);
        };

        $biz['importer.user-group-member'] = function ($biz) {
            return new UserGroupMemberImporter($biz);
        };

        $biz['importer.offline-exam-result'] = function ($biz) {
            return new OfflineExamResultImporter($biz);
        };

        $biz['importer.advanced-user-select'] = function ($biz) {
            return new AdvancedUserSelectImporter($biz);
        };

        $biz['importer.org'] = function ($biz) {
            return new OrgImporter($biz);
        };
    }
}
