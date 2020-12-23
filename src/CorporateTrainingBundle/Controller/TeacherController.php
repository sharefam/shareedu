<?php

namespace CorporateTrainingBundle\Controller;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use CorporateTrainingBundle\Common\Constant\CTConst;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\TeacherController as BaseController;

class TeacherController extends BaseController
{
    public function indexAction(Request $request)
    {
        $renderData = $this->buildTeacherListData($request);

        return $this->render('teacher/index.html.twig', $renderData);
    }

    public function ajaxListAction(Request $request)
    {
        $renderData = $this->buildTeacherListData($request, 'ajax');

        return $this->render('teacher/teacher-list.html.twig', $renderData);
    }

    protected function buildTeacherListData($request, $type = '')
    {
        $conditions = $request->request->all();
        $searchProfilesConditions = $this->prepareSearchProfilesConditions($conditions);

        $teacherLevels = $this->getTeacherLevelService()->findAllLevels();
        $teacherProfessionField = $this->getTeacherProfessionFieldService()->findAllTeacherProfessionFields();
        $teacherLevels = ArrayToolkit::index($teacherLevels, 'id');
        $teacherProfessionField = ArrayToolkit::index($teacherProfessionField, 'id');

        $teacherProfiles = $this->getTeacherProfileService()->searchProfiles(
            $searchProfilesConditions,
            array(),
            0,
            PHP_INT_MAX
        );
        $teacherProfiles = ArrayToolkit::index($teacherProfiles, 'userId');

        $searchUserConditions = $this->prepareSearchConditions($conditions);
        if (!empty($searchProfilesConditions)) {
            $userIds = ArrayToolkit::column($teacherProfiles, 'userId');
            $intersectUserIds = array_intersect($userIds, $searchUserConditions['userIds']);
            $searchUserConditions['userIds'] = empty($intersectUserIds) ? array(-1) : $intersectUserIds;
        }

        $paginator = new Paginator(
            $this->get('request'),
            $this->getUserService()->countUsers($searchUserConditions),
            20
        );
        if ('ajax' != $type) {
            $paginator->setBaseUrl($this->generateUrl('teacher_ajax_list'));
        }

        $teachers = $this->getUserService()->searchUsers(
            $searchUserConditions,
            array(
                'promoted' => 'DESC',
                'promotedSeq' => 'ASC',
                'promotedTime' => 'DESC',
                'createdTime' => 'DESC',
            ),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $user = $this->getCurrentUser();
        $teacherIds = ArrayToolkit::column($teachers, 'id');
        $profiles = $this->getUserService()->findUserProfilesByIds($teacherIds);
        $myFollowings = $this->getUserService()->filterFollowingIds($user['id'], $teacherIds);
        $orgCodes = $this->getOrgService()->findOrgsByPrefixOrgCodes(array(CTConst::ROOT_ORG_CODE));
        $orgIds = ArrayToolkit::column($orgCodes, 'id');

        return array(
            'teachers' => $teachers,
            'levels' => $teacherLevels,
            'fields' => $teacherProfessionField,
            'teacherProfiles' => $teacherProfiles,
            'profiles' => $profiles,
            'paginator' => $paginator,
            'Myfollowings' => $myFollowings,
            'orgCodes' => array(CTConst::ROOT_ORG_CODE),
            'orgIds' => isset($conditions['orgIds']) ? $conditions['orgIds'] : implode(',', $orgIds),
        );
    }

    protected function prepareSearchProfilesConditions($conditions)
    {
        $searchProfilesConditions = array();
        if (isset($conditions['field']) && !empty($conditions['field'])) {
            $searchProfilesConditions['likeTeacherProfessionFieldIds'] = $conditions['field'];
        }
        if (isset($conditions['level']) && !empty($conditions['level'])) {
            $searchProfilesConditions['levelId'] = $conditions['level'];
        }

        return $searchProfilesConditions;
    }

    protected function prepareSearchConditions($conditions)
    {
        if (isset($conditions['orgIds']) && empty($conditions['orgIds'])) {
            $conditions['userIds'] = array(-1);
        }
        if (isset($conditions['orgIds']) && !empty($conditions['orgIds'])) {
            $conditions['orgIds'] = empty($conditions['orgIds']) ? explode(',', $conditions['orgIds']) : $conditions['orgIds'];

            $orgUserIds = $this->getUserOrgService()->findUserOrgsByOrgIds(explode(',', $conditions['orgIds']));
            $userIds = ArrayToolkit::column($orgUserIds, 'userId');
            $conditions['userIds'] = !empty($userIds) ? $userIds : array(-1);
        }
        if (isset($conditions['keyword'])) {
            $conditions['keyword'] = trim($conditions['keyword']);
        }
        if (isset($conditions['keywordType']) && !empty($conditions['keyword'])) {
            $conditions[$conditions['keywordType']] = $conditions['keyword'];
            unset($conditions['keywordType']);
            unset($conditions['keyword']);
        }

        $conditions['roles'] = 'ROLE_TEACHER';
        $conditions['locked'] = 0;
        unset($conditions['field'], $conditions['level'], $conditions['orgIds']);

        return $conditions;
    }

    /**
     * @return \CorporateTrainingBundle\Biz\Teacher\Service\Impl\ProfileServiceImpl
     */
    protected function getTeacherProfileService()
    {
        return $this->createService('CorporateTrainingBundle:Teacher:ProfileService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\Teacher\Service\Impl\LevelServiceImpl
     */
    protected function getTeacherLevelService()
    {
        return $this->createService('CorporateTrainingBundle:Teacher:LevelService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\TeacherProfessionField\Service\Impl\TeacherProfessionFieldServiceImpl
     */
    protected function getTeacherProfessionFieldService()
    {
        return $this->createService('CorporateTrainingBundle:TeacherProfessionField:TeacherProfessionFieldService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\User\Service\Impl\UserOrgServiceImpl
     */
    protected function getUserOrgService()
    {
        return $this->createService('CorporateTrainingBundle:User:UserOrgService');
    }
}
