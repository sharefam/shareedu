<?php

namespace CorporateTrainingBundle\Controller\Course;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Exception\AccessDeniedException;
use Biz\Org\Service\OrgService;
use CorporateTrainingBundle\Biz\ResourceScope\Service\ResourceAccessScopeService;
use CorporateTrainingBundle\Biz\ResourceScope\Service\ResourceVisibleScopeService;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\Course\CourseSetManageController as BaseCourseSetManageController;

class CourseSetManageController extends BaseCourseSetManageController
{
    public function createAction(Request $request)
    {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();

            if (!isset($data['type'])) {
                throw $this->createNotFoundException('No course type is set');
            }
            if (empty($data['orgCode']) || !$this->getCurrentUser()->hasManagePermissionWithOrgCode($data['orgCode'])) {
                throw $this->createAccessDeniedException('您暂不可创建课程，原因：未授予权限范围');
            }
            $data = $this->buildOrgId($data);
            $courseSet = $this->getCourseSetService()->createCourseSet($data);
            $this->getResourceVisibleService()->setResourceVisibleScope($courseSet['id'], 'courseSet', array('showable' => 1, 'publishOrg' => $courseSet['orgId']));

            return $this->redirect(
                $this->generateUrl(
                    'course_set_manage_base',
                    array(
                        'id' => $courseSet['id'],
                    )
                )
            );
        }

        if (!$this->getCourseSetService()->hasCourseSetManageRole()) {
            throw $this->createAccessDeniedException('Unauthorized');
        }
        $user = $this->getUser();
        $userProfile = $this->getUserService()->getUserProfile($user->getId());
        $user = $this->getUserService()->getUser($user->getId());

        return $this->render(
            'courseset-manage/create.html.twig',
            array(
                'user' => $user,
                'userProfile' => $userProfile,
            )
        );
    }

    public function baseAction(Request $request, $id)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($id);
        $user = $this->getCurrentUser();
        $course = $this->getCourseService()->getDefaultCourseByCourseSetId($courseSet['id']);

        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            if (!empty($data['orgCode']) && !$user->hasManagePermissionWithOrgCode($data['orgCode'])) {
                throw new AccessDeniedException($this->trans('admin.manage.org_no_permission'));
            }

            $data = $this->buildOrgId($data);
            $data = $this->buildAccessScope($data);
            $this->getResourceVisibleService()->setResourceVisibleScope($id, 'courseSet', $data);
            $this->getResourceAccessService()->setResourceAccessScope($id, 'courseSet', $data);
            $courseSet = $this->getCourseSetService()->updateCourseSet($id, $data);
            $courseData = array(
                'title' => $courseSet['title'],
                'courseSetId' => $course['courseSetId'],
            );
            if ('live' == $courseSet['type']) {
                $courseData['maxStudentNum'] = $data['maxStudentNum'];
            }
            $this->getCourseService()->updateCourse($course['id'], $courseData);

            $this->setFlashMessage('success', 'course.course_manage.base.message.update_success');

            return $this->redirect($this->generateUrl('course_set_manage_base', array('id' => $id)));
        }

        if ($courseSet['locked']) {
            return $this->redirectToRoute(
                'course_set_manage_sync',
                array(
                    'id' => $id,
                    'sideNav' => 'base',
                )
            );
        }

        $tags = $this->getTagService()->findTagsByOwner(array(
            'ownerType' => 'course-set',
            'ownerId' => $id,
        ));

        return $this->render(
            'courseset-manage/base.html.twig',
            array(
                'courseSet' => $courseSet,
                'course' => $course,
                'tags' => ArrayToolkit::column($tags, 'name'),
                'canSettingOrg' => $user->hasManagePermissionWithOrgCode($courseSet['orgCode']),
            )
        );
    }

    public function lockedCourseBaseAction(Request $request, $id)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($id);
        $data = $request->request->all();
        $data = $this->buildOrgId($data);
        $data = $this->buildAccessScope($data);
        $this->getResourceVisibleService()->setResourceVisibleScope($id, 'courseSet', $data);
        $this->getResourceAccessService()->setResourceAccessScope($id, 'courseSet', $data);
        $data['title'] = $courseSet['title'];
        $data['categoryId'] = $courseSet['categoryId'];
        $data['serializeMode'] = $courseSet['serializeMode'];
        $this->getCourseSetService()->updateCourseSet($id, $data);

        return $this->createJsonResponse(true);
    }

    protected function buildOrgId($fields)
    {
        if (!empty($fields['orgCode']) && empty($fields['orgId'])) {
            $org = $this->getOrgService()->getOrgByOrgCode($fields['orgCode']);
            $fields['orgId'] = empty($org) ? 1 : $org['id'];
        }

        return $fields;
    }

    /**
     * @return OrgService
     */
    protected function getOrgService()
    {
        return $this->createService('Org:OrgService');
    }

    /**
     * @return ResourceVisibleScopeService
     */
    protected function getResourceVisibleService()
    {
        return $this->createService('CorporateTrainingBundle:ResourceScope:ResourceVisibleScopeService');
    }

    /**
     * @return ResourceAccessScopeService
     */
    protected function getResourceAccessService()
    {
        return $this->createService('CorporateTrainingBundle:ResourceScope:ResourceAccessScopeService');
    }
}
