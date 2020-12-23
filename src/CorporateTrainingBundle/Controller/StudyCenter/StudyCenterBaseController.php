<?php

namespace CorporateTrainingBundle\Controller\StudyCenter;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\BaseController;

class StudyCenterBaseController extends BaseController
{
    protected function mergeConditionsByCategory($conditions, $category)
    {
        $categoryArray = array();
        $subCategory = empty($conditions['subCategory']) ? null : $conditions['subCategory'];

        $thirdLevelCategory = empty($conditions['selectedthirdLevelCategory']) ? null : $conditions['selectedthirdLevelCategory'];

        if (!empty($conditions['subCategory']) && empty($conditions['selectedthirdLevelCategory'])) {
            $conditions['code'] = $subCategory;
        } elseif (!empty($conditions['selectedthirdLevelCategory'])) {
            $conditions['code'] = $thirdLevelCategory;
        } else {
            $conditions['code'] = $category;
        }

        if (!empty($conditions['code'])) {
            $categoryArray = $this->getCategoryService()->getCategoryByCode($conditions['code']);

            $conditions['categoryId'] = $categoryArray['id'];
        }

        $category = array(
            'category' => $category,
            'subCategory' => $subCategory,
            'thirdLevelCategory' => $thirdLevelCategory,
        );

        if (!$categoryArray) {
            $categoryArrayDescription = array();
        } else {
            $categoryArrayDescription = $categoryArray['description'];
            $categoryArrayDescription = strip_tags($categoryArrayDescription, '');
            $categoryArrayDescription = preg_replace('/ /', '', $categoryArrayDescription);
            $categoryArrayDescription = substr($categoryArrayDescription, 0, 100);
        }

        if (!$categoryArray) {
            $categoryParent = '';
        } else {
            if (!$categoryArray['parentId']) {
                $categoryParent = '';
            } else {
                $categoryParent = $this->getCategoryService()->getCategory($categoryArray['parentId']);
            }
        }

        return array($conditions, $categoryArray, $category, $categoryArrayDescription, $categoryParent);
    }

    protected function mergeConditionsByTag($conditions, $type)
    {
        $tags = array();
        $selectedTag = '';
        $selectedTagGroupId = '';

        if (!empty($conditions['tag'])) {
            if (!empty($conditions['tag']['tags'])) {
                $tags = $conditions['tag']['tags'];
            }

            if (!empty($conditions['tag']['selectedTag'])) {
                $selectedTag = $conditions['tag']['selectedTag']['tag'];
                $selectedTagGroupId = $conditions['tag']['selectedTag']['group'];
            }
        }

        $flag = false;

        foreach ($tags as $groupId => $tagId) {
            if ($groupId == $selectedTagGroupId && $tagId != $selectedTag) {
                $tags[$groupId] = $selectedTag;
                $flag = true;
                break;
            }

            if ($groupId == $selectedTagGroupId && $tagId == $selectedTag) {
                unset($tags[$groupId]);
                $flag = true;
                break;
            }
        }

        if (!$flag) {
            $tags[$selectedTagGroupId] = $selectedTag;
        }

        $tags = array_filter($tags);

        if (!empty($tags)) {
            $conditions['tagIds'] = array_values($tags);
            $conditions['tagIds'] = array_unique($conditions['tagIds']);
            $conditions['tagIds'] = array_filter($conditions['tagIds']);
            $conditions['tagIds'] = array_merge($conditions['tagIds']);

            $tagIdsNum = count($conditions['tagIds']);

            $tagOwnerRelations = $this->getTagService()->findTagOwnerRelationsByTagIdsAndOwnerType(
                $conditions['tagIds'],
                $type
            );
            $ids = ArrayToolkit::column($tagOwnerRelations, 'ownerId');
            $flag = array_count_values($ids);

            $ids = array_unique($ids);

            foreach ($ids as $key => $setId) {
                if ($flag[$setId] != $tagIdsNum) {
                    unset($ids[$key]);
                }
            }

            if (empty($ids)) {
                $conditions['ids'] = array(0);
            } else {
                $conditions['ids'] = $ids;
            }

            unset($conditions['tagIds']);
        }

        unset($conditions['tag']);

        return array($conditions, $tags);
    }

    protected function getOrgCodes($orgCodes)
    {
        if (count($orgCodes) > 1 && in_array('1.', $orgCodes)) {
            foreach ($orgCodes as $key => $orgCode) {
                if ('1.' == $orgCode) {
                    unset($orgCodes[$key]);
                    break;
                }
            }
        }

        return $orgCodes;
    }

    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    protected function getPostService()
    {
        return $this->createService('CorporateTrainingBundle:Post:PostService');
    }

    protected function getPostCourseService()
    {
        return $this->createService('CorporateTrainingBundle:PostCourse:PostCourseService');
    }

    protected function getThreadService()
    {
        return $this->createService('Course:ThreadService');
    }

    protected function getOrgService()
    {
        return $this->createService('Org:OrgService');
    }

    protected function getMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    protected function getTagService()
    {
        return $this->createService('Taxonomy:TagService');
    }

    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    protected function getResourceVisibleScopeService()
    {
        return $this->createService('ResourceScope:ResourceVisibleScopeService');
    }
}
