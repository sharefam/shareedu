<?php

namespace CorporateTrainingBundle\Biz\Course\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\Impl\CourseSetServiceImpl as BaseCourseSetServiceImpl;
use Codeages\Biz\Framework\Event\Event;
use CorporateTrainingBundle\Biz\Course\Dao\Impl\CourseSetDaoImpl;
use CorporateTrainingBundle\Biz\Course\Service\CourseSetService;
use CorporateTrainingBundle\Biz\Org\Service\OrgService;

class CourseSetServiceImpl extends BaseCourseSetServiceImpl implements CourseSetService
{
    public function createCourseSet($courseSet)
    {
        if (!$this->hasCourseSetManageRole() || (!empty($courseSet['orgCode']) && !$this->getCurrentUser()->hasManagePermissionWithOrgCode($courseSet['orgCode']))) {
            throw $this->createAccessDeniedException('You have no access to Course Set Management');
        }

        $created = $this->addCourseSet($courseSet);
        $defaultCourse = $this->addDefaultCourse($courseSet, $created);

        //update courseSet defaultId
        $this->getCourseSetDao()->update($created['id'], array('defaultCourseId' => $defaultCourse['id']));
        $this->getLogService()->info('course', 'create', sprintf('创建课程《%s》(#%s)', $created['title'], $created['id']));

        return $created;
    }

    /**
     * 内训更新课程时添加orgId.
     */
    public function updateCourseSet($id, $fields)
    {
        if (!ArrayToolkit::requireds($fields, array('title', 'categoryId', 'serializeMode'))) {
            throw $this->createInvalidArgumentException('Lack of required fields');
        }
        if (!in_array($fields['serializeMode'], array('none', 'serialized', 'finished'))) {
            throw $this->createInvalidArgumentException('Invalid Param: serializeMode');
        }

        if (!empty($fields['orgCode']) && !$this->getCurrentUser()->hasManagePermissionWithOrgCode($fields['orgCode'])) {
            throw $this->createAccessDeniedException('You have no access to Course Set Management');
        }

        $courseSet = $this->getCourseSet($id);

        $fields = ArrayToolkit::parts(
            $fields,
            array(
                'title',
                'subtitle',
                'tags',
                'categoryId',
                'serializeMode',
                // 'summary',
                'smallPicture',
                'middlePicture',
                'largePicture',
                'teacherIds',
                'orgCode',
                'orgId',
                'showable',
                'conditionalAccess',
            )
        );

        if (isset($fields['tags']) && null !== $fields['tags']) {
            $tags = explode(',', $fields['tags']);
            $tags = $this->getTagService()->findTagsByNames($tags);
            $tagIds = ArrayToolkit::column($tags, 'id');
            $fields['tags'] = $tagIds;
        }

        $fields = $this->filterFields($fields);

        if (isset($fields['summary'])) {
            $fields['summary'] = $this->purifyHtml($fields['summary'], true);
        }

        $this->updateCourseSerializeMode($courseSet, $fields);
        if (empty($fields['subtitle'])) {
            $fields['subtitle'] = null;
        }

        $courseSet = $this->getCourseSetDao()->update($courseSet['id'], $fields);

        $this->getLogService()->info('course', 'update', "修改课程《{$courseSet['title']}》(#{$courseSet['id']})");
        $this->dispatchEvent('course-set.update', new Event($courseSet));

        return $courseSet;
    }

    public function sumStudentNumByCourseSetIds($courseSetIds)
    {
        return $this->getCourseSetDao()->sumStudentNumByConditions(array('ids' => $courseSetIds));
    }

    public function findCourseSetsByCategoryId($categoryId)
    {
        return $this->getCourseSetDao()->findByCategoryId($categoryId);
    }

    /**
     * @Custom 后台改造
     * 权限判断现在完全走路由permission,抛弃tryManage判断权限方法
     */
    public function tryManageCourseSet($id)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException('user not login');
        }

        $courseSet = $this->getCourseSetDao()->get($id);

        if (empty($courseSet)) {
            throw $this->createNotFoundException("CourseSet#{$id} Not Found");
        }

        $course = $this->getCourseService()->getDefaultCourseByCourseSetId($courseSet['id']);

        $teachers = $this->getCourseMemberService()->findCourseTeachers($course['id']);
        $course['teacherIds'] = ArrayToolkit::column($teachers, 'userId');

        //企培版增加权限控制，只能管理自己及以下组织机构的课程
        if (!$this->getCurrentUser()->hasManagePermissionWithOrgCode($courseSet['orgCode']) && !in_array($user['id'], $course['teacherIds'])) {
            throw $this->createAccessDeniedException($this->trans('admin.manage.org_no_permission'));
        }
        if (!$this->hasCourseSetManageRole($id)) {
            throw $this->createAccessDeniedException('can not access');
        }

        return $courseSet;
    }

    public function hasCourseSetManageRole($courseSetId = 0)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return false;
        }

        if ($this->hasAdminRole()) {
            return true;
        }

        if ($user->isTrainingAdmin()) {
            return true;
        }

        if (empty($courseSetId)) {
            return $user->isTeacher();
        }

        $courseSet = $this->getCourseSetDao()->get($courseSetId);
        if (empty($courseSet)) {
            return false;
        }

        if ($courseSet['creator'] == $user->getId()) {
            return true;
        }

        $teachers = $this->getCourseMemberService()->findCourseSetTeachers($courseSetId);
        $teacherIds = ArrayToolkit::column($teachers, 'userId');

        $courses = $this->getCourseService()->findCoursesByCourseSetId($courseSetId);
        foreach ($courses as $course) {
            if (in_array($user->getId(), $teacherIds)) {
                $canManageRole = $this->getCourseService()->hasCourseManagerRole($course['id']);
                if ($canManageRole) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * 内训创建课程时添加orgId.
     */
    protected function addCourseSet($courseSet)
    {
        if (!ArrayToolkit::requireds($courseSet, array('title', 'type'))) {
            throw $this->createInvalidArgumentException('Lack of required fields');
        }

        if (!in_array($courseSet['type'], static::courseSetTypes())) {
            throw $this->createInvalidArgumentException('Invalid Param: type');
        }

        $courseSet = ArrayToolkit::parts(
            $courseSet,
            array(
                'type',
                'title',
                'orgCode',
                'orgId',
            )
        );

        $courseSet['status'] = 'draft';
        $courseSet['creator'] = $this->getCurrentUser()->getId();

        return $this->getCourseSetDao()->create($courseSet);
    }

    protected function generateDefaultCourse($created)
    {
        $defaultCourse = array(
            'courseSetId' => $created['id'],
            'title' => $created['title'],
            'expiryMode' => 'forever',
            'learnMode' => empty($created['learnMode']) ? CourseService::FREE_LEARN_MODE : $created['learnMode'],
            'courseType' => empty($created['courseType']) ? CourseService::DEFAULT_COURSE_TYPE : $created['courseType'],
            'isDefault' => 1,
            'isFree' => 1,
            'serializeMode' => $created['serializeMode'],
            'status' => 'draft',
            'type' => $created['type'],
        );

        return $defaultCourse;
    }

    public function initOrgsRelation()
    {
        $fields = array('1', '1.');

        return $this->getCourseSetDao()->initOrgsRelation($fields);
    }

    /**
     * @return OrgService
     */
    protected function getOrgService()
    {
        return $this->createService('Org:OrgService');
    }

    /**
     * @return CourseSetDaoImpl
     */
    protected function getCourseSetDao()
    {
        return $this->createDao('CorporateTrainingBundle:Course:CourseSetDao');
    }
}
