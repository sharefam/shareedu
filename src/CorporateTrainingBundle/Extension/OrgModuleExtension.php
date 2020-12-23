<?php

namespace CorporateTrainingBundle\Extension;

class OrgModuleExtension extends Extension
{
    public function getOrgModules()
    {
        return array(
            'user' => array(
                'displayKey' => 'org.module.display_name.user',
                'service' => 'User:UserService',
                'countMethod' => 'countUsers',
            ),
            'courseSet' => array(
                'displayKey' => 'org.module.display_name.courseset',
                'service' => 'Course:CourseSetService',
                'countMethod' => 'countCourseSets',
            ),
            'classroom' => array(
                'displayKey' => 'org.module.display_name.classroom',
                'service' => 'Classroom:ClassroomService',
                'countMethod' => 'countClassrooms',
            ),
            'article' => array(
                'displayKey' => 'org.module.display_name.article',
                'service' => 'Article:ArticleService',
                'countMethod' => 'countArticles',
            ),
            'announcement' => array(
                'displayKey' => 'org.module.display_name.announcement',
                'service' => 'Announcement:AnnouncementService',
                'countMethod' => 'countAnnouncements',
            ),
            'openCourse' => array(
                'displayKey' => 'org.module.display_name.openCourse',
                'service' => 'OpenCourse:OpenCourseService',
                'countMethod' => 'countCourses',
            ),
            'project' => array(
                'displayKey' => 'org.module.display_name.project',
                'service' => 'ProjectPlan:ProjectPlanService',
                'countMethod' => 'countProjectPlans',
            ),
        );
    }
}
