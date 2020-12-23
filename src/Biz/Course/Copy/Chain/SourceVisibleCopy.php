<?php

namespace Biz\Course\Copy\Chain;

use AppBundle\Common\ArrayToolkit;
use Biz\Course\Copy\AbstractEntityCopy;

class SourceVisibleCopy extends AbstractEntityCopy
{
    protected function copyEntity($source, $config = array())
    {
        return $this->doCopyCourseSetVisible($config);
    }

    protected function getFields()
    {
        return array(
            'resourceType',
            'resourceId',
            'scope',
        );
    }

    private function doCopyCourseSetVisible($config)
    {
        $newCourseSet = $config['newCourseSet'];
        $newCourseSetId = $newCourseSet['id'];
        $classroomId = $config['classroomId'];
        $visibleScopeTypes = $this->getStrategyContext()->getVisibleScopeTypes();
        foreach ($visibleScopeTypes as $visibleScopeType) {
            $strategy = $this->getStrategyContext()->createStrategy($visibleScopeType);
            $visibleScopes = $strategy->findVisibleScopesByResourceTypeAndResourceId('classroom', $classroomId);
            if (!empty($visibleScopes)) {
                $data = implode(',', ArrayToolkit::column($visibleScopes, 'scope'));
                $strategy->setResourceVisibleScope($newCourseSetId, 'courseSet', $data);
            }
        }
    }

    protected function getStrategyContext()
    {
        return $this->biz['resource_scope_strategy_context'];
    }
}
