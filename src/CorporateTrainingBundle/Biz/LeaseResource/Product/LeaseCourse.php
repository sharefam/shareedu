<?php

namespace CorporateTrainingBundle\Biz\LeaseResource\Product;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\FileToolkit;
use Biz\CloudPlatform\Client\CloudAPIIOException;
use Biz\Course\Dao\CourseDao;
use Biz\Course\Dao\CourseMemberDao;
use Biz\File\Dao\UploadFileInitDao;
use Biz\Testpaper\Service\TestpaperService;
use CorporateTrainingBundle\Biz\ResourceScope\Service\ResourceVisibleScopeService;
use CorporateTrainingBundle\Biz\User\Service\UserService;
use Symfony\Component\HttpFoundation\File\File;

class LeaseCourse extends AbstractLeaseProduct
{
    private $excludeTaskType = array('live', 'download', 'questionnaire');

    public function createLeaseProduct($resourceCode)
    {
        $result = $this->getApiClient()->getCourseInfo($resourceCode);

        if (!empty($result['code'])) {
            throw new CloudAPIIOException($result['message']);
        }

        $leaseCourseExist = $this->getCourseSetDao()->getByResourceCode($resourceCode);
        if ($leaseCourseExist) {
            $courseSet = $this->getCourseSetDao()->update($leaseCourseExist['id'], array('deadline' => $result['courseSet']['deadline']));
            $newDeadline = date('Y-m-d H:i:s', $courseSet['deadline']);
            $oldDeadline = date('Y-m-d H:i:s', $leaseCourseExist['deadline']);

            $this->getLogService()->info('leaseCourse', 'update_deadline', "更新租赁课程《{$courseSet['title']}》(#{$courseSet['id']})的有效期， 由{$oldDeadline} 改为{$newDeadline}");

            return $courseSet;
        }

        $this->biz['db']->beginTransaction();
        try {
            $courseSet = $this->addCourseSet($result['courseSet']);
            $this->addCourseChapter($result['courseChapters'], $courseSet);
            $this->addQuestions($result['questions'], $courseSet);
            $this->addTestpapers($result['testpapers'], $courseSet);
            $this->addTestpaperItems($result['testpaperItems'], $courseSet);
            $files = $this->addUploadFiles($result['uploadFiles'], $courseSet);
            $this->addQuestionAttachments($result['attachments'], $courseSet);
            $this->addActivities($result['activities'], $courseSet, $files);
            $this->addCourseTasks($result['courseTasks'], $courseSet);

            $this->getLogService()->info('leaseCourse', 'create', "完成租赁课程创建《{$courseSet['title']}》(#{$courseSet['id']})");

            $this->biz['db']->commit();

            return $courseSet;
        } catch (\Exception $e) {
            $this->biz['db']->rollback();
            throw $e;
        }
    }

    protected function addCourseSet($courseSetParams)
    {
        if (!ArrayToolkit::requireds($courseSetParams, array('title', 'resourceCode', 'learnMode', 'deadline'))) {
            throw new CloudAPIIOException('Invalid Arguments');
        }
        $superAdmins = $this->getUserService()->searchUsers(array('roles' => 'ROLE_SUPER_ADMIN', 'locked' => 0, 'noType' => 'system'), array('id' => 'ASC'), 0, 1);
        $superAdmin = $superAdmins[0];
        $courseSetParams['teacherIds'] = array($superAdmin['id']);
        $courseSetParams['creator'] = $superAdmin['id'];

        $courseSetFields = ArrayToolkit::parts($courseSetParams, array(
            'title',
            'subtitle',
            'summary',
            'cover',
            'resourceCode',
            'deadline',
            'teacherIds',
            'creator',
        ));

        $courseSetFields['type'] = 'normal';
        $courseSetFields['belong'] = 'lease';
        $courseSetFields['status'] = 'draft';

        $courseSet = $this->getCourseSetDao()->create($courseSetFields);
        $course = $this->addCourse($courseSet, $courseSetParams);
        $courseSet = $this->getCourseSetDao()->update($courseSet['id'], array('defaultCourseId' => $course['id']));
        $this->addTeacher($course, $superAdmin['id']);

        $this->downloadCourseSetCover($courseSetParams);

        $this->getResourceVisibleService()->setResourceVisibleScope($courseSet['id'], 'courseSet', array('showable' => 1, 'publishOrg' => $courseSet['orgId']));

        return $courseSet;
    }

    protected function downloadCourseSetCover($courseSetParams)
    {
        if (empty($courseSetParams['coverUrls'])) {
            return;
        }

        foreach ($courseSetParams['coverUrls'] as $type => $img) {
            $imgPath = explode('://', $courseSetParams['cover'][$type]);

            $savePath = realpath($this->biz['topxia.upload.public_directory']).'/'.$imgPath[1];
            $saveTmpPath = realpath($this->biz['topxia.upload.public_directory']).'/'.basename($savePath);

            FileToolkit::downloadImg($img, $saveTmpPath);
            $file = new File($saveTmpPath);
            if (!FileToolkit::isImageFile($file)) {
                FileToolkit::remove($savePath);
            } else {
                $file->move(dirname($savePath), basename($savePath));
            }
        }
    }

    protected function addCourse($courseSet, $courseSetParams)
    {
        $defaultCourse = array(
            'courseSetId' => $courseSet['id'],
            'title' => $courseSet['title'],
            'expiryMode' => 'forever',
            'learnMode' => $courseSetParams['learnMode'],
            'courseType' => $courseSet['type'],
            'isDefault' => 1,
            'isFree' => 1,
            'serializeMode' => $courseSet['serializeMode'],
            'status' => 'draft',
            'type' => $courseSet['type'],
            'taskNum' => $courseSetParams['taskNum'],
            'compulsoryTaskNum' => $courseSetParams['compulsoryTaskNum'],
            'teacherIds' => $courseSetParams['teacherIds'],
        );

        return $this->getCourseDao()->create($defaultCourse);
    }

    protected function addTeacher($course, $superAdminId)
    {
        $teacherMember = array(
            'courseId' => $course['id'],
            'courseSetId' => $course['courseSetId'],
            'userId' => $superAdminId,
            'role' => 'teacher',
            'seq' => 0,
            'isVisible' => 1,
            'createdTime' => time(),
        );

        return $this->getMemberDao()->create($teacherMember);
    }

    protected function addQuestions($questions, $courseSet)
    {
        if (empty($questions)) {
            return null;
        }

        $groupQuestions = ArrayToolkit::group($questions, 'parentId');
        if (!empty($groupQuestions[0])) {
            foreach ($groupQuestions[0] as $question) {
                $newQuestions[] = $this->filterQuestion($question, $courseSet);
            }

            $this->getQuestionService()->batchCreateQuestions($newQuestions);
        }
        $newQuestions = $this->getQuestionService()->findQuestionsByCourseSetId($courseSet['id']);
        $newQuestions = ArrayToolkit::index($newQuestions, 'syncId');

        $childrenQuestions = array();
        foreach ($groupQuestions as $parentId => $groupQuestion) {
            if (0 == $parentId) {
                continue;
            }
            foreach ($groupQuestion as $question) {
                $childrenQuestion = $this->filterQuestion($question, $courseSet);
                $childrenQuestion['parentId'] = empty($newQuestions[$question['parentId']]) ? 0 : $newQuestions[$question['parentId']]['id'];
                $childrenQuestions[] = $childrenQuestion;
            }
        }

        if (!empty($childrenQuestions)) {
            $this->getQuestionService()->batchCreateQuestions($childrenQuestions);
        }
    }

    protected function filterQuestion($question, $courseSet)
    {
        $questionFields = ArrayToolkit::parts($question, array(
            'type',
            'stem',
            'score',
            'answer',
            'analysis',
            'metas',
            'difficulty',
            'parentId',
            'subCount',
            'isDelete',
        ));

        $questionFields['syncId'] = $question['id'];
        $questionFields['courseId'] = $courseSet['defaultCourseId'];
        $questionFields['courseSetId'] = $courseSet['id'];

        return $questionFields;
    }

    protected function addQuestionAttachments($attachments, $courseSet)
    {
        if (empty($attachments)) {
            return null;
        }

        $questions = $this->getQuestionService()->findQuestionsByCourseSetId($courseSet['id']);
        $questions = ArrayToolkit::index($questions, 'syncId');

        $files = $this->getUploadFileDao()->search(array('syncIdLT' => 0), array(), 0, PHP_INT_MAX);
        $files = ArrayToolkit::index($files, 'syncId');

        foreach ($attachments as $attachment) {
            $newAttachment = $attachment;
            $newAttachment['syncId'] = $attachment['id'];
            $newAttachment['targetId'] = empty($questions[$attachment['targetId']]) ? 0 : $questions[$attachment['targetId']]['id'];
            $newAttachment['fileId'] = empty($files[$attachment['fileId']]) ? 0 : $files[$attachment['fileId']]['id'];
            $newAttachment['createdTime'] = time();
            unset($newAttachment['id']);

            $newAttachments[] = $newAttachment;
        }

        $this->getFileUsedDao()->batchCreate($newAttachments);
    }

    protected function addTestpapers($testpapers, $courseSet)
    {
        if (empty($testpapers)) {
            return null;
        }

        foreach ($testpapers as $testpaper) {
            $newTestpaper = ArrayToolkit::parts($testpaper, array(
                'name',
                'description',
                'pattern',
                'status',
                'score',
                'passedCondition',
                'itemCount',
                'metas',
                'type',
            ));
            $newTestpaper['courseId'] = $courseSet['defaultCourseId'];
            $newTestpaper['courseSetId'] = $courseSet['id'];
            $newTestpaper['syncId'] = $testpaper['id'];

            $newTestpapers[] = $newTestpaper;
        }

        $this->getTestpaperService()->batchCreateTestpaper($newTestpapers);
    }

    protected function addTestpaperItems($testpaperItems, $courseSet)
    {
        if (empty($testpaperItems)) {
            return null;
        }

        $testpapers = $this->getTestpaperService()->searchTestpapers(array('courseSetId' => $courseSet['id']), array(), 0, PHP_INT_MAX);
        $testpapers = ArrayToolkit::index($testpapers, 'syncId');

        $questions = $this->getQuestionService()->findQuestionsByCourseSetId($courseSet['id']);
        $questions = ArrayToolkit::index($questions, 'syncId');

        foreach ($testpaperItems as $item) {
            $newItem = array(
                'testId' => empty($testpapers[$item['testId']]) ? 0 : $testpapers[$item['testId']]['id'],
                'seq' => $item['seq'],
                'questionId' => empty($questions[$item['questionId']]) ? 0 : $questions[$item['questionId']]['id'],
                'questionType' => $item['questionType'],
                'parentId' => $item['parentId'] > 0 && !empty($questions[$item['parentId']]) ? $questions[$item['parentId']]['id'] : 0,
                'score' => $item['score'],
                'missScore' => $item['missScore'],
                'type' => $item['type'],
            );

            $newItems[] = $newItem;
        }

        $this->getTestpaperService()->batchCreateItems($newItems);
    }

    protected function addCourseChapter($courseChapters, $courseSet)
    {
        if (empty($courseChapters)) {
            return null;
        }

        foreach ($courseChapters as $chapter) {
            $newChapter = ArrayToolkit::parts($chapter, array('type', 'number', 'seq', 'title'));
            $newChapter['courseId'] = $courseSet['defaultCourseId'];
            $newChapter['syncId'] = $chapter['id'];

            $newChapters[] = $newChapter;
        }

        $this->getCourseChapterDao()->batchCreate($newChapters);
    }

    protected function addActivities($activities, $courseSet, $files)
    {
        if (empty($activities)) {
            return null;
        }

        $testpapers = $this->getTestpaperService()->searchTestpapers(array('courseSetId' => $courseSet['id']), array(), 0, PHP_INT_MAX);
        $testpapers = ArrayToolkit::index($testpapers, 'syncId');

        $files = ArrayToolkit::index($files, 'syncId');

        foreach ($activities as $activity) {
            if (in_array($activity['mediaType'], $this->excludeTaskType)) {
                continue;
            }
            $newActivity = $activity;
            $newActivity['syncId'] = $activity['id'];
            $newActivity['fromCourseId'] = $courseSet['defaultCourseId'];
            $newActivity['fromCourseSetId'] = $courseSet['id'];
            $newActivity['fromUserId'] = 0;
            $newActivity['createdTime'] = time();
            unset($newActivity['id']);
            unset($newActivity['ext']);

            if (!empty($activity['ext'])) {
                $newExt = $this->getActivityConfig($activity['mediaType'])->copyFromResourcePlatform($activity['ext'], array('testpapers' => $testpapers, 'files' => $files));
                $newActivity['mediaId'] = $newExt['id'];
            }
            if (in_array($activity['mediaType'], array('exercise', 'homework'))) {
                $newActivity['mediaId'] = empty($testpapers[$activity['mediaId']]) ? 0 : $testpapers[$activity['mediaId']]['id'];
            }

            $newActivities[] = $newActivity;
        }

        $this->getActivityDao()->batchCreate($newActivities);
    }

    protected function addCourseTasks($courseTasks, $courseSet)
    {
        if (empty($courseTasks)) {
            return null;
        }

        $newActivities = $this->getActivityDao()->findByCourseId($courseSet['defaultCourseId']);
        $newActivities = ArrayToolkit::index($newActivities, 'syncId');
        $compulsoryTaskNum = 0;
        foreach ($courseTasks as $task) {
            if (in_array($task['type'], $this->excludeTaskType)) {
                continue;
            }
            $newTask = $task;
            $newTask['syncId'] = $task['id'];
            $newTask['courseId'] = $courseSet['defaultCourseId'];
            $newTask['fromCourseSetId'] = $courseSet['id'];
            $newTask['createdTime'] = time();
            $newTask['updatedTime'] = 0;
            $newTask['createdUserId'] = 0;
            $newTask['activityId'] = $newActivities[$task['activityId']]['id'];
            unset($newTask['id']);
            if (empty($newTask['isOptional'])) {
                ++$compulsoryTaskNum;
            }
            $newTasks[] = $newTask;
        }

        $this->getTaskService()->batchCreateTasks($newTasks);
        $this->getCourseDao()->update($courseSet['defaultCourseId'], array('compulsoryTaskNum' => $compulsoryTaskNum, 'taskNum' => count($newTasks)));
    }

    protected function addUploadFiles($uploadFiles, $courseSet)
    {
        if (empty($uploadFiles)) {
            return array();
        }

        $existsFiles = $this->getUploadFileDao()->findAllLeaseCloudFiles();
        $existsFiles = ArrayToolkit::index($existsFiles, 'hashId');

        $newUploadFiles = array();
        $syncIds = array();
        foreach ($uploadFiles as $uploadFile) {
            $syncIds[] = $uploadFile['id'];

            if (!empty($existsFiles[$uploadFile['hashId']])) {
                $existsFile = $existsFiles[$uploadFile['hashId']];
                array_push($existsFile['belongResourceCodes'], $courseSet['resourceCode']);
                $this->getUploadFileDao()->update($existsFile['id'], array('belongResourceCodes' => array_unique($existsFile['belongResourceCodes'])));
                continue;
            }

            $newUploadFile = $uploadFile;
            $newUploadFile['createdUserId'] = 0;
            $newUploadFile['syncId'] = $uploadFile['id'];
            $newUploadFile['targetId'] = $courseSet['id'];
            $newUploadFile['createdTime'] = time();
            $newUploadFile['convertStatus'] = 'success';
            $newUploadFile['belongResourceCodes'] = array($courseSet['resourceCode']);

            unset($newUploadFile['id']);
            unset($newUploadFile['updatedUserId']);
            unset($newUploadFile['updatedTime']);

            $uploadFileInit = $this->filterUploadFileInit($newUploadFile);
            $uploadFileInit = $this->getUploadFileInitDao()->create($uploadFileInit);

            $newUploadFile['id'] = $uploadFileInit['id'];
            $newUploadFiles[] = $newUploadFile;
        }

        if ($newUploadFiles) {
            $this->getUploadFileDao()->batchCreate($newUploadFiles);
        }

        return $this->getUploadFileDao()->search(array('syncIds' => $syncIds), array(), 0, PHP_INT_MAX);
    }

    protected function filterUploadFileInit($uploadFile)
    {
        return ArrayToolkit::parts($uploadFile, array(
            'globalId',
            'status',
            'hashId',
            'targetId',
            'targetType',
            'filename',
            'ext',
            'fileSize',
            'etag',
            'length',
            'convertHash',
            'convertStatus',
            'metas',
            'metas2',
            'type',
            'storage',
            'convertParams',
            'createdUserId',
            'createdTime',
        ));
    }

    /**
     * @param $type
     *
     * @return Activity
     */
    private function getActivityConfig($type)
    {
        return $this->biz["activity_type.{$type}"];
    }

    protected function getCourseSetDao()
    {
        return $this->biz->dao('Course:CourseSetDao');
    }

    protected function getCourseDao()
    {
        return $this->biz->dao('Course:CourseDao');
    }

    /**
     * @return CourseMemberDao
     */
    protected function getMemberDao()
    {
        return $this->biz->dao('Course:CourseMemberDao');
    }

    protected function getCourseChapterDao()
    {
        return $this->biz->dao('Course:CourseChapterDao');
    }

    /**
     * @return UploadFileDao
     */
    protected function getUploadFileDao()
    {
        return $this->biz->dao('File:UploadFileDao');
    }

    protected function getActivityDao()
    {
        return $this->biz->dao('Activity:ActivityDao');
    }

    protected function getFileUsedDao()
    {
        return $this->biz->dao('File:FileUsedDao');
    }

    /**
     * @return UploadFileInitDao
     */
    protected function getUploadFileInitDao()
    {
        return $this->biz->dao('File:UploadFileInitDao');
    }

    protected function getLogService()
    {
        return $this->biz->service('System:LogService');
    }

    protected function getQuestionService()
    {
        return $this->biz->service('Question:QuestionService');
    }

    /**
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->biz->service('Testpaper:TestpaperService');
    }

    protected function getTaskService()
    {
        return $this->biz->service('Task:TaskService');
    }

    /**
     * @return ResourceVisibleScopeService
     */
    protected function getResourceVisibleService()
    {
        return $this->biz->service('CorporateTrainingBundle:ResourceScope:ResourceVisibleScopeService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }
}
