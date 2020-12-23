<?php

namespace CorporateTrainingBundle\Biz\Classroom\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\Classroom\Service\Impl\ClassroomServiceImpl as BaseService;
use Codeages\Biz\Framework\Event\Event;
use CorporateTrainingBundle\Biz\Classroom\Service\ClassroomService;
use CorporateTrainingBundle\Biz\ResourceScope\Service\ResourceVisibleScopeService;

class ClassroomServiceImpl extends BaseService implements ClassroomService
{
    public function batchBecomeStudent($classroomId, $userIds)
    {
        $classroom = $this->getClassroom($classroomId);

        if (empty($classroom)) {
            throw $this->createNotFoundException();
        }

        if (!in_array($classroom['status'], array('published', 'closed'))) {
            throw $this->createServiceException('Cannot join unpublished classroom');
        }
        $createUserIds = array();
        $fields = array(
            'classroomId' => $classroom['id'],
            'orderId' => 0,
            'levelId' => 0,
            'role' => array('student'),
            'remark' => empty($info['note']) ? '' : $info['note'],
            'deadline' => 0,
            'refundDeadline' => 0,
        );
        $students = array();
        foreach ($userIds as $userId) {
            $isStudent = $this->isClassroomStudent($classroom['id'], $userId);
            if (!$isStudent) {
                $fields['userId'] = $userId;
                $students[] = $fields;
            }
        }
        if (empty($students)) {
            return true;
        }
        try {
            $this->beginTransaction();
            $this->batchCreateMember($classroom, $students);
            $fields = array(
            'studentNum' => $this->getClassroomStudentCount($classroom['id']),
            'auditorNum' => $this->getClassroomAuditorCount($classroom['id']),
            );
            $this->getClassroomDao()->update($classroom['id'], $fields);
            $this->getLogService()->info(
                'classroom',
                'add_student',
                "班级《{$classroom['title']}》(#{$classroom['id']})，批量添加学员"
            );
            $this->commit();

            return true;
        } catch (\Exception $e) {
            $this->getLogService()->info('classroom', 'error', $e->getTraceAsString());
            $this->rollback();

            return false;
        }
    }

    protected function batchCreateMember($classroom, $students)
    {
        $currentUser = $this->getCurrentUser();
        $currentUserProfile = $this->getUserService()->getUserProfile($currentUser['id']);
        $this->getClassroomMemberDao()->batchCreate($students);
        $userIds = ArrayToolkit::column($students, 'userId');
        $members = $this->findMembersByClassroomIdAndUserIds($classroom['id'], $userIds);
        $message = array(
            'classroomId' => $classroom['id'],
            'classroomTitle' => $classroom['title'],
            'userId' => $currentUser['id'],
            'userName' => !empty($currentUserProfile['truename']) ? $currentUserProfile['truename'] : $currentUser['nickname'],
            'type' => 'create',
        );
        $classroom['batchJoin'] = true;
        $records = array();
        foreach ($members as $member) {
            $this->getNotificationService()->notify($member['userId'], 'classroom-student', $message);
            $this->dispatchEvent(
                'classroom.join',
                new Event($classroom, array('userId' => $member['userId'], 'member' => $member))
            );
            $record = array(
                'title' => $classroom['title'],
                'user_id' => $member['userId'],
                'member_id' => $member['id'],
                'target_id' => $member['classroomId'],
                'target_type' => 'classroom',
                'operate_type' => 'join',
                'operate_time' => time(),
                'operator_id' => $currentUser['id'],
                'data' => array('member' => $member),
                'order_id' => $member['orderId'],
            );
            $records[] = $record;
        }
        $this->getMemberOperationService()->batchCreateRecord($records);
    }

    public function initOrgsRelation()
    {
        $fields = array('1', '1.');

        return $this->getClassroomDao()->initOrgsRelation($fields);
    }

    public function canUserVisitResource($id)
    {
        $member = $this->getClassroomMember($id, $this->getCurrentUser()->getId());
        if (!empty($member)) {
            return true;
        }

        $canUserAccessResource = $this->getResourceVisibleService()->canUserVisitResource('classroom', $id, $this->getCurrentUser()->getId());
        if ($canUserAccessResource) {
            return true;
        }

        return false;
    }

    /**
     * @param  $id
     * @param  $permission
     *
     * @return bool
     */
    public function canManageClassroom($id, $permission = 'admin_classroom_content_manage')
    {
        $classroom = $this->getClassroom($id);

        if (empty($classroom)) {
            return false;
        }

        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            return false;
        }

        if (!$this->getCurrentUser()->hasManagePermissionWithOrgCode($classroom['orgCode']) && ($user['id'] != $classroom['headTeacherId'])) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->hasPermission($permission)) {
            return true;
        }

        $member = $this->getClassroomMember($id, $user['id']);

        if (empty($member)) {
            return false;
        }

        if (in_array('headTeacher', $member['role'])) {
            return true;
        }

        return false;
    }

    public function isCourseBelongToUserClassroom($courseId, $user)
    {
        if (empty($courseId) || empty($user)) {
            return false;
        }

        $classrooms = $this->findClassroomIdsByCourseId($courseId);
        if (empty($classrooms)) {
            return false;
        }

        $classroomIds = ArrayToolkit::column($classrooms, 'classroomId');
        $members = $this->findMembersByUserIdAndClassroomIds($user['id'], $classroomIds);
        if (empty($members)) {
            return false;
        }

        $userIds = ArrayToolkit::column($members, 'userId');
        if (!in_array($user['id'], $userIds)) {
            return false;
        }

        return true;
    }

    public function findMembersByClassroomId($classroomId)
    {
        return $this->getClassroomMemberDao()->findByClassroomId($classroomId);
    }

    /**
     * @return ResourceVisibleScopeService
     */
    protected function getResourceVisibleService()
    {
        return $this->createService('CorporateTrainingBundle:ResourceScope:ResourceVisibleScopeService');
    }
}
