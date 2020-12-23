<?php

namespace CorporateTrainingBundle\Common;

use AppBundle\Common\BlockToolkit;
use CorporateTrainingBundle\Biz\Crontab\SystemCrontabInitializer;
use Topxia\Service\Common\ServiceKernel;
use Biz\User\CurrentUser;
use CorporateTrainingBundle\Common\Exception\DuplicateException;
use AppBundle\Common\SystemInitializer as BaseSystemInitializer;

class SystemInitializer extends BaseSystemInitializer
{
    protected $container;

    public function setContainer($container)
    {
        $this->container = $container;
    }

    public function initAdminUser($fields)
    {
        $this->output->write('  创建管理员帐号');
        $fields['emailVerified'] = 1;

        $user = $this->getUserService()->getUserByNickname($fields['nickname']);
        if (!empty($user)) {
            throw new DuplicateException('用户名已经存在！');
        }

        $user = $this->getUserService()->getUserByEmail($fields['email']);
        if (!empty($user)) {
            throw new DuplicateException('邮箱已经存在！');
        }

        $user = $this->getUserService()->register($fields);
        $user = $this->getUserService()->changeUserOrgs($user['id'], array('1.'));
        $user['roles'] = array('ROLE_USER', 'ROLE_TEACHER', 'ROLE_SUPER_ADMIN');
        $user['currentIp'] = '127.0.0.1';

        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        ServiceKernel::instance()->setCurrentUser($currentUser);

        $this->getUserService()->changeUserRoles($user['id'], array('ROLE_USER', 'ROLE_TEACHER', 'ROLE_SUPER_ADMIN'));

        $this->getManagePermissionOrgService()->setUserManagePermissionOrgs($user['id'], array('1.'));

        $this->output->writeln(' ...<info>成功</info>');

        return $this->getUserService()->getUser($user['id']);
    }

    public function initRegisterSetting($user)
    {
        $this->output->write('  初始化注册设置');

        $default = array(
            'register_mode' => 'closed',
            'email_activation_title' => '',
            'email_activation_body' => '',
            'welcome_enabled' => 'closed',
            'welcome_sender' => $user['nickname'],
            'welcome_methods' => array(),
            'welcome_title' => '欢迎加入{{sitename}}',
            'welcome_body' => '您好{{nickname}}，我是{{sitename}}的管理员，欢迎加入{{sitename}}，祝您学习愉快。如有问题，随时与我联系。',
        );

        $this->getSettingService()->set('auth', $default);

        $this->output->writeln(' ...<info>成功</info>');
    }

    protected function _initBlocks()
    {
        $themeDir = ServiceKernel::instance()->getParameter('kernel.root_dir').DIRECTORY_SEPARATOR.'../web/themes';
        $this->output->write('  初始化编辑区');

        $metaFiles = array(
            'jianmo' => "{$themeDir}/jianmo/block.json",
        );

        foreach ($metaFiles as $category => $file) {
            $metas = file_get_contents($file);
            $metas = json_decode($metas, true);

            foreach ($metas as $code => $meta) {
                $data = array();

                foreach ($meta['items'] as $key => $item) {
                    $data[$key] = $item['default'];
                }

                $blockTemplate = $this->getBlockService()->getBlockTemplateByCode($code);
                if (empty($blockTemplate)) {
                    $blockTemplate = $this->getBlockService()->createBlockTemplate(array(
                        'title' => $meta['title'],
                        'mode' => 'template',
                        'templateName' => $meta['templateName'],
                        'code' => $code,
                        'meta' => $meta,
                        'data' => $data,
                        'category' => $category,
                    ));
                }

                $content = BlockToolkit::render($blockTemplate, $this->container);
                $this->getBlockService()->updateBlockTemplate($blockTemplate['id'], array(
                    'content' => $content,
                ));
            }
        }

        $this->output->writeln(' ...<info>成功</info>');
    }

    protected function _initJob()
    {
        $this->output->write('  初始化企培版CrontabJob');
        SystemCrontabInitializer::init();
        $this->getSettingService()->set('crontab_next_executed_time', time());
        $this->output->writeln(' ...<info>成功</info>');
    }

    protected function _initNavigations()
    {
        $this->output->write('  初始化导航');

        $topNavigations = $this->getNavigationService()->findNavigationsByType('top', 0, PHP_INT_MAX);

        $navigationArr = array(
            'home' => array(
                'name' => '首页',
                'url' => '/',
                'sequence' => 1,
                'isNewWin' => 0,
                'isOpen' => 1,
                'type' => 'top',
            ),
            'studyCenter' => array(
                'name' => '学习中心',
                'url' => '/study/center/my/task/training/list',
                'sequence' => 2,
                'isNewWin' => 0,
                'isOpen' => 1,
                'type' => 'top',
            ),
            'article' => array(
                'name' => '新闻资讯',
                'url' => '/article',
                'sequence' => 3,
                'isNewWin' => 0,
                'isOpen' => 1,
                'type' => 'top',
            ),
            'group' => array(
                'name' => '话题小组',
                'url' => '/group',
                'sequence' => 4,
                'isNewWin' => 0,
                'isOpen' => 1,
                'type' => 'top',
            ),
            'live' => array(
                'name' => '直播首页',
                'url' => '/live',
                'sequence' => 5,
                'isNewWin' => 0,
                'isOpen' => 0,
                'type' => 'top',
            ),
            'teacher' => array(
                'name' => '师资力量',
                'url' => '/teacher',
                'sequence' => 6,
                'isNewWin' => 0,
                'isOpen' => 0,
                'type' => 'top',
            ),
            'mobile' => array(
                'name' => '移动端首页',
                'url' => '/mobile',
                'sequence' => 7,
                'isNewWin' => 0,
                'isOpen' => 0,
                'type' => 'top',
            ),
            'myPage' => array(
                'name' => '个人主页',
                'url' => '/my/page/show',
                'sequence' => 8,
                'isNewWin' => 0,
                'isOpen' => 0,
                'type' => 'top',
            ),
            'courseExplore' => array(
                'name' => '课程列表页',
                'url' => '/course/explore',
                'sequence' => 9,
                'isNewWin' => 0,
                'isOpen' => 0,
                'type' => 'top',
            ),
            'classroomExplore' => array(
                'name' => '专题列表页',
                'url' => '/classroom/explore',
                'sequence' => 10,
                'isNewWin' => 0,
                'isOpen' => 0,
                'type' => 'top',
            ),
        );

        foreach ($navigationArr as $value) {
            if (!$this->isNavigationExist($topNavigations, $value['name'])) {
                $this->getNavigationService()->createNavigation($value);
            }
        }

        $this->output->writeln(' ...<info>成功</info>');
    }

    public function initMagicSetting()
    {
        $this->output->write('  初始化magic设置');
        $default = array(
            'export_allow_count' => 100000,
            'export_limit' => 10000,
            'enable_org' => 1,
        );

        $this->getSettingService()->set('magic', $default);

        $this->output->writeln(' ...<info>成功</info>');
    }

    public function initCorporateTrainingDefaultSetting()
    {
        $this->output->write('  初始化内训版默认配置');

        //默认关闭侧边栏
        $esBar = array('enabled' => '0');
        $this->getSettingService()->set('esBar', $esBar);

        //默认班级改名为专题
        $defaultClassroomSet = array(
            'name' => '专题',
            'discount_buy' => 1,
            'explore_default_orderBy' => 'createdTime',
        );
        $this->getSettingService()->set('classroom', $defaultClassroomSet);

        //默认移动端配置
        $defaultMobileSet = array(
            'enabled' => 1, // 网校状态
            'ver' => 1, //是否是新版
            'about' => '', // 网校简介
            'logo' => '', // 网校Logo
            'appname' => '',
            'appabout' => '',
            'applogo' => '',
            'appcover' => '',
            'notice' => '', //公告
            'splash1' => '', // 启动图1
            'splash2' => '', // 启动图2
            'splash3' => '', // 启动图3
            'splash4' => '', // 启动图4
            'splash5' => '', // 启动图5
        );

        $this->getSettingService()->set('mobile', $defaultMobileSet);

        $this->output->writeln(' ...<info> 成功</info> ');
    }

    public function initCourseSetting()
    {
        $this->output->write('  初始化课程设置');

        $default = array(
            'welcome_message_enabled' => '0',
            'welcome_message_body' => '{{nickname}},欢迎加入课程{{course}}',
            'teacher_manage_marketing' => '1',
            'teacher_search_order' => '0',
            'teacher_manage_student' => '1',
            'teacher_export_student' => '1',
            'student_download_media' => '0',
            'explore_default_orderBy' => 'recommendedSeq',
            'free_course_nologin_view' => '1',
            'relatedCourses' => '1',
            'coursesPrice' => '0',
            'allowAnonymousPreview' => '0',
            'show_student_num_enabled' => '1',
            'copy_enabled' => '1',
            'testpaperCopy_enabled' => '1',
        );

        $this->getSettingService()->set('course', $default);
        $this->output->writeln(' ...<info>成功</info>');
    }

    public function initClassroomSetting()
    {
        $this->output->write('  初始化专题设置');

        $default = array(
            'discount_buy' => '1',
            'explore_default_orderBy' => 'createdTime',
        );

        $this->getSettingService()->set('classroom', $default);
        $this->output->writeln(' ...<info>成功</info>');
    }

    public function initThemeConfig()
    {
        $config = ServiceKernel::instance()->getParameter('theme_jianmo_default');

        $this->getThemeService()->saveCurrentThemeConfig($config);

        $this->getThemeService()->saveConfirmConfig();
    }

    public function initArea()
    {
        $this->output->write('  初始化地域信息');

        $biz = ServiceKernel::instance()->getBiz();
        $db = $biz['db'];

        $db->exec("
        INSERT INTO `area` (`id`, `name`, `parentId`) VALUES
            (1, '北京', 0),
            (2, '广东', 0),
            (3, '山东', 0),
            (4, '江苏', 0),
            (5, '河南', 0),
            (6, '上海', 0),
            (7, '河北', 0),
            (8, '浙江', 0),
            (9, '陕西', 0),
            (10, '湖南', 0),
            (11, '重庆', 0),
            (12, '福建', 0),
            (13, '天津', 0),
            (14, '云南', 0),
            (15, '四川', 0),
            (16, '广西', 0),
            (17, '安徽', 0),
            (18, '海南', 0),
            (19, '江西', 0),
            (20, '湖北', 0),
            (21, '山西', 0),
            (22, '辽宁', 0),
            (23, '黑龙江', 0),
            (24, '内蒙古', 0),
            (25, '贵州', 0),
            (26, '甘肃', 0),
            (27, '青海', 0),
            (28, '新疆', 0),
            (29, '西藏', 0),
            (30, '吉林', 0),
            (31, '宁夏', 0),
            (32, '香港', 0),
            (33, '澳门', 0),
            (34, '台湾', 0),
            (35, '东城区', 1),
            (36, '西城区', 1),
            (37, '朝阳区', 1),
            (38, '崇文区', 1),
            (39, '海淀区', 1),
            (40, '宣武区', 1),
            (41, '石景山区', 1),
            (42, '门头沟区', 1),
            (43, '丰台区', 1),
            (44, '房山区', 1),
            (45, '大兴区', 1),
            (46, '通州区', 1),
            (47, '顺义区', 1),
            (48, '平谷区', 1),
            (49, '昌平区', 1),
            (50, '怀柔区', 1),
            (51, '延庆县', 1),
            (52, '密云县', 1),
            (53, '东莞市', 2),
            (54, '广州市', 2),
            (55, '中山市', 2),
            (56, '深圳市', 2),
            (57, '惠州市', 2),
            (58, '江门市', 2),
            (59, '珠海市', 2),
            (60, '汕头市', 2),
            (61, '佛山市', 2),
            (62, '湛江市', 2),
            (63, '济南市', 3),
            (64, '青岛市', 3),
            (65, '临沂市', 3),
            (66, '济宁市', 3),
            (67, '菏泽市', 3),
            (68, '烟台市', 3),
            (69, '淄博市', 3),
            (70, '泰安市', 3),
            (71, '潍坊市', 3),
            (72, '日照市', 3),
            (73, '威海市', 3),
            (74, '滨州市', 3),
            (75, '东营市', 3),
            (76, '聊城市', 3),
            (77, '德州市', 3),
            (78, '莱芜市', 3),
            (79, '枣庄市', 3),
            (80, '苏州市', 4),
            (81, '徐州市', 4),
            (82, '盐城市', 4),
            (83, '无锡市', 4),
            (84, '南京市', 4),
            (85, '南通市', 4),
            (86, '连云港市', 4),
            (87, '常州市', 4),
            (88, '镇江市', 4),
            (89, '扬州市', 4),
            (90, '淮安市', 4),
            (91, '泰州市', 4),
            (92, '宿迁市', 4),
            (93, '郑州市', 5),
            (94, '南阳市', 5),
            (95, '新乡市', 5),
            (96, '安阳市', 5),
            (97, '洛阳市', 5),
            (98, '信阳市', 5),
            (99, '平顶山市', 5),
            (100, '周口市', 5),
            (101, '商丘市', 5),
            (102, '开封市', 5),
            (103, '焦作市', 5),
            (104, '驻马店市', 5),
            (105, '濮阳市', 5),
            (106, '三门峡市', 5),
            (107, '漯河市', 5),
            (108, '许昌市', 5),
            (109, '鹤壁市', 5),
            (110, '济源市', 5),
            (111, '黄浦区', 6),
            (112, '徐汇区', 6),
            (113, '长宁区', 6),
            (114, '静安区', 6),
            (115, '普陀区', 6),
            (116, '虹口区', 6),
            (117, '杨浦区', 6),
            (118, '闵行区', 6),
            (119, '宝山区', 6),
            (120, '嘉定区', 6),
            (121, '浦东新区', 6),
            (122, '金山区', 6),
            (123, '松江区', 6),
            (124, '青浦区', 6),
            (125, '奉贤区', 6),
            (126, '石家庄市', 7),
            (127, '唐山市', 7),
            (128, '保定市', 7),
            (129, '邯郸市', 7),
            (130, '邢台市', 7),
            (131, '沧州市', 7),
            (132, '秦皇岛市', 7),
            (133, '张家口市', 7),
            (134, '衡水市', 7),
            (135, '廊坊市', 7),
            (136, '承德市', 7),
            (137, '温州市', 8),
            (138, '宁波市', 8),
            (139, '杭州市', 8),
            (140, '台州市', 8),
            (141, '嘉兴市', 8),
            (142, '金华市', 8),
            (143, '湖州市', 8),
            (144, '绍兴市', 8),
            (145, '舟山市', 8),
            (146, '丽水市', 8),
            (147, '衢州市', 8),
            (148, '西安市', 9),
            (149, '咸阳市', 9),
            (150, '宝鸡市', 9),
            (151, '汉中市', 9),
            (152, '渭南市', 9),
            (153, '安康市', 9),
            (154, '榆林市', 9),
            (155, '商洛市', 9),
            (156, '延安市', 9),
            (157, '铜川市', 9),
            (158, '长沙市', 10),
            (159, '邵阳市', 10),
            (160, '常德市', 10),
            (161, '衡阳市', 10),
            (162, '株洲市', 10),
            (163, '湘潭市', 10),
            (164, '永州市', 10),
            (165, '岳阳市', 10),
            (166, '怀化市', 10),
            (167, '郴州市', 10),
            (168, '娄底市', 10),
            (169, '益阳市', 10),
            (170, '张家界市', 10),
            (171, '湘西土家族苗族自治州', 10),
            (172, '重庆市', 11),
            (173, '漳州市', 12),
            (174, '厦门市', 12),
            (175, '泉州市', 12),
            (176, '福州市', 12),
            (177, '莆田市', 12),
            (178, '宁德市', 12),
            (179, '三明市', 12),
            (180, '南平市', 12),
            (181, '龙岩市', 12),
            (182, '天津市', 13),
            (183, '昆明市', 14),
            (184, '红河哈尼族彝族自治州', 14),
            (185, '大理白族自治州', 14),
            (186, '文山壮族苗族自治州', 14),
            (187, '德宏傣族景颇族自治州', 14),
            (188, '曲靖市', 14),
            (189, '昭通市', 14),
            (190, '楚雄彝族自治州', 14),
            (191, '保山市', 14),
            (192, '玉溪市', 14),
            (193, '丽江市', 14),
            (194, '临沧市', 14),
            (195, '西双版纳州', 14),
            (196, '怒江傈僳族自治州', 14),
            (197, '迪庆藏族自治州', 14),
            (198, '普洱市', 14),
            (199, '成都市', 15),
            (200, '绵阳市', 15),
            (201, '广元市', 15),
            (202, '达州市', 15),
            (203, '南充市', 15),
            (204, '德阳市', 15),
            (205, '广安市', 15),
            (206, '阿坝藏族羌族自治州', 15),
            (207, '巴中市', 15),
            (208, '遂宁市', 15),
            (209, '内江市', 15),
            (210, '凉山彝族自治州', 15),
            (211, '攀枝花市', 15),
            (212, '乐山市', 15),
            (213, '自贡市', 15),
            (214, '泸州市', 15),
            (215, '雅安市', 15),
            (216, '宜宾市', 15),
            (217, '资阳市', 15),
            (218, '眉山市', 15),
            (219, '甘孜藏族自治州', 15),
            (220, '贵港市', 16),
            (221, '玉林市', 16),
            (222, '北海市', 16),
            (223, '南宁市', 16),
            (224, '柳州市', 16),
            (225, '桂林市', 16),
            (226, '梧州市', 16),
            (227, '钦州市', 16),
            (228, '来宾市', 16),
            (229, '河池市', 16),
            (230, '百色市', 16),
            (231, '贺州市', 16),
            (232, '崇左市', 16),
            (233, '防城港市', 16),
            (234, '芜湖市', 17),
            (235, '合肥市', 17),
            (236, '六安市', 17),
            (237, '宿州市', 17),
            (238, '阜阳市', 17),
            (239, '安庆市', 17),
            (240, '马鞍山市', 17),
            (241, '蚌埠市', 17),
            (242, '淮北市', 17),
            (243, '淮南市', 17),
            (244, '宣城市', 17),
            (245, '黄山市', 17),
            (246, '铜陵市', 17),
            (247, '亳州市', 17),
            (248, '池州市', 17),
            (249, '滁州市', 17),
            (250, '三亚市', 18),
            (251, '海口市', 18),
            (252, '三沙市', 18),
            (253, '琼海市', 18),
            (254, '文昌市', 18),
            (255, '东方市', 18),
            (256, '昌江黎族自治县', 18),
            (257, '陵水黎族自治县', 18),
            (258, '乐东黎族自治县', 18),
            (259, '保亭黎族苗族自治县', 18),
            (260, '五指山市', 18),
            (261, '澄迈县', 18),
            (262, '万宁市', 18),
            (263, '儋州市', 18),
            (264, '临高县', 18),
            (265, '白沙黎族自治县', 18),
            (266, '定安县', 18),
            (267, '琼中黎族苗族自治县', 18),
            (268, '屯昌县', 18),
            (269, '南昌市', 19),
            (270, '赣州市', 19),
            (271, '上饶市', 19),
            (272, '吉安市', 19),
            (273, '九江市', 19),
            (274, '新余市', 19),
            (275, '抚州市', 19),
            (276, '宜春市', 19),
            (277, '景德镇市', 19),
            (278, '萍乡市', 19),
            (279, '鹰潭市', 19),
            (280, '武汉市', 20),
            (281, '宜昌市', 20),
            (282, '襄樊市', 20),
            (283, '荆州市', 20),
            (284, '恩施土家族苗族自治州', 20),
            (285, '黄冈市', 20),
            (286, '孝感市', 20),
            (287, '十堰市', 20),
            (288, '咸宁市', 20),
            (289, '黄石市', 20),
            (290, '仙桃市', 20),
            (291, '天门市', 20),
            (292, '随州市', 20),
            (293, '荆门市', 20),
            (294, '潜江市', 20),
            (295, '鄂州市', 20),
            (296, '神农架林区', 20),
            (297, '太原市', 21),
            (298, '大同市', 21),
            (299, '运城市', 21),
            (300, '长治市', 21),
            (301, '晋城市', 21),
            (302, '忻州市', 21),
            (303, '临汾市', 21),
            (304, '吕梁市', 21),
            (305, '晋中市', 21),
            (306, '阳泉市', 21),
            (307, '朔州市', 21),
            (308, '大连市', 22),
            (309, '沈阳市', 22),
            (310, '丹东市', 22),
            (311, '辽阳市', 22),
            (312, '葫芦岛市', 22),
            (313, '锦州市', 22),
            (314, '朝阳市', 22),
            (315, '营口市', 22),
            (316, '鞍山市', 22),
            (317, '抚顺市', 22),
            (318, '阜新市', 22),
            (319, '盘锦市', 22),
            (320, '本溪市', 22),
            (321, '铁岭市', 22),
            (322, '齐齐哈尔市', 23),
            (323, '哈尔滨市', 23),
            (324, '大庆市', 23),
            (325, '佳木斯市', 23),
            (326, '双鸭山市', 23),
            (327, '牡丹江市', 23),
            (328, '鸡西市', 23),
            (329, '黑河市', 23),
            (330, '绥化市', 23),
            (331, '鹤岗市', 23),
            (332, '伊春市', 23),
            (333, '大兴安岭地区', 23),
            (334, '七台河市', 23),
            (335, '赤峰市', 24),
            (336, '包头市', 24),
            (337, '通辽市', 24),
            (338, '呼和浩特市', 24),
            (339, '鄂尔多斯市', 24),
            (340, '乌海市', 24),
            (341, '呼伦贝尔市', 24),
            (342, '兴安盟', 24),
            (343, '巴彦淖尔市', 24),
            (344, '乌兰察布市', 24),
            (345, '锡林郭勒盟', 24),
            (346, '阿拉善盟', 24),
            (347, '贵阳市', 25),
            (348, '黔东南苗族侗族自治州', 25),
            (349, '黔南布依族苗族自治州', 25),
            (350, '遵义市', 25),
            (351, '黔西南布依族苗族自治州', 25),
            (352, '毕节市', 25),
            (353, '铜仁市', 25),
            (354, '安顺市', 25),
            (355, '六盘水市', 25),
            (356, '兰州市', 26),
            (357, '天水市', 26),
            (358, '庆阳市', 26),
            (359, '武威市', 26),
            (360, '酒泉市', 26),
            (361, '张掖市', 26),
            (362, '陇南市', 26),
            (363, '白银市', 26),
            (364, '定西市', 26),
            (365, '平凉市', 26),
            (366, '嘉峪关市', 26),
            (367, '临夏回族自治州', 26),
            (368, '金昌市', 26),
            (369, '甘南藏族自治州', 26),
            (370, '西宁市', 27),
            (371, '海西蒙古族藏族自治州', 27),
            (372, '海东市', 27),
            (373, '海北藏族自治州', 27),
            (374, '果洛藏族自治州', 27),
            (375, '玉树藏族自治州', 27),
            (376, '黄南藏族自治州', 27),
            (377, '海南藏族自治州', 27),
            (378, '乌鲁木齐市', 28),
            (379, '伊犁哈萨克自治州', 28),
            (380, '昌吉回族自治州', 28),
            (381, '石河子市', 28),
            (382, '哈密市', 28),
            (383, '阿克苏地区', 28),
            (384, '巴音郭楞蒙古自治州', 28),
            (385, '喀什地区', 28),
            (386, '塔城地区', 28),
            (387, '克拉玛依市', 28),
            (388, '和田地区', 28),
            (389, '阿勒泰地区', 28),
            (390, '吐鲁番市', 28),
            (391, '阿拉尔市', 28),
            (392, '博尔塔拉蒙古自治州', 28),
            (393, '五家渠市', 28),
            (394, '克孜勒苏柯尔克孜自治州', 28),
            (395, '图木舒克市', 28),
            (396, '北屯市', 28),
            (397, '铁门关市', 28),
            (398, '双河市', 28),
            (399, '可克达拉市', 28),
            (400, '昆玉市', 28),
            (401, '拉萨市', 29),
            (402, '山南市', 29),
            (403, '林芝市', 29),
            (404, '日喀则市', 29),
            (405, '阿里地区', 29),
            (406, '昌都市', 29),
            (407, '那曲地区', 29),
            (408, '吉林市', 30),
            (409, '长春市', 30),
            (410, '白山市', 30),
            (411, '延边朝鲜族自治州', 30),
            (412, '白城市', 30),
            (413, '松原市', 30),
            (414, '辽源市', 30),
            (415, '通化市', 30),
            (416, '四平市', 30),
            (417, '银川市', 31),
            (418, '吴忠市', 31),
            (419, '中卫市', 31),
            (420, '石嘴山市', 31),
            (421, '固原市', 31),
            (422, '中西区', 32),
            (423, '东区', 32),
            (424, '南区', 32),
            (425, '湾仔区', 32),
            (426, '九龙区', 32),
            (427, '观塘区', 32),
            (428, '深水埗区', 32),
            (429, '黄大仙区', 32),
            (430, '油尖旺区', 32),
            (431, '离岛区', 32),
            (432, '葵青区', 32),
            (433, '北区', 32),
            (434, '西贡区', 32),
            (435, '沙田区', 32),
            (436, '大埔区', 32),
            (437, '荃湾区', 32),
            (438, '屯门区', 32),
            (439, '元朗区', 32),
            (440, '花地玛堂区', 33),
            (441, '圣安多尼堂区', 33),
            (442, '大堂区', 33),
            (443, '望德堂区', 33),
            (444, '风顺堂区', 33),
            (445, '嘉模堂区', 33),
            (446, '圣方济各堂区', 33),
            (447, '台北市', 34),
            (448, '新北市', 34),
            (449, '桃园市', 34),
            (450, '台中市', 34),
            (451, '台南市', 34),
            (452, '高雄市', 34),
            (453, '基隆市', 34),
            (454, '新竹市', 34),
            (455, '嘉义市', 34),
            (456, '新竹县', 34),
            (457, '苗栗县', 34),
            (458, '彰化县', 34),
            (459, '南投县', 34),
            (460, '云林县', 34),
            (461, '嘉义县', 34),
            (462, '屏东县', 34),
            (463, '宜兰县', 34),
            (464, '花莲县', 34),
            (465, '台东县', 34),
            (466, '澎湖县', 34);
        ");

        $this->output->writeln(' ...<info>成功</info>');
    }

    public function initDiscoveryColumn()
    {
        $this->output->write('  初始化移动端发现页信息');

        $publicCourse = array('title' => '公共课程', 'type' => 'publicCourse', 'categoryId' => 0, 'orderType' => 'new', 'showCount' => 4, 'createdTime' => time());
        $departmentCourse = array('title' => '部门课程', 'type' => 'departmentCourse', 'categoryId' => 0, 'orderType' => 'new', 'showCount' => 4, 'createdTime' => time());
        $offlineActivity = array('title' => '活动报名', 'type' => 'offlineActivity', 'categoryId' => 0, 'orderType' => 'new', 'showCount' => 4, 'createdTime' => time());
        $projectPlan = array('title' => '培训项目', 'type' => 'projectPlan', 'categoryId' => 0, 'orderType' => 'new', 'showCount' => 4, 'createdTime' => time());
        $this->getDiscoveryColumnService()->addDiscoveryColumn($publicCourse);
        $this->getDiscoveryColumnService()->addDiscoveryColumn($departmentCourse);
        $this->getDiscoveryColumnService()->addDiscoveryColumn($offlineActivity);
        $this->getDiscoveryColumnService()->addDiscoveryColumn($projectPlan);

        $this->output->writeln(' ...<info>成功</info>');
    }

    public function initSiteInfoSetting()
    {
        $default = array(
            'companyName' => 'EduSoho企培版',
            'province' => '',
            'city' => '',
            'industry' => '',
            'employeeNumber' => 0,
            'applicationVersion' => '',
            'domainName' => '',
            'status' => true,
            'accessKey' => '',
        );

        $this->getSettingService()->set('site_info', $default);
    }

    protected function _initCategory()
    {
        parent::_initCategory();

        $offlineActivityGroup = $this->getCategoryService()->getGroupByCode('offlineActivity');

        if (!$offlineActivityGroup) {
            $offlineActivityGroup = $this->getCategoryService()->addGroup(array(
                'name' => '线下活动',
                'code' => 'offlineActivity',
                'depth' => 1,
            ));
        }

        $offlineActivityCategory = $this->getCategoryService()->getCategoryByCode('offlineActivityDefault');

        if (!$offlineActivityCategory) {
            $this->getCategoryService()->createCategory(array(
                'name' => '默认分类',
                'code' => 'offlineActivityDefault',
                'weight' => 100,
                'groupId' => $offlineActivityGroup['id'],
                'parentId' => 0,
            ));
        }

        $projectPlanGroup = $this->getCategoryService()->getGroupByCode('projectPlan');

        if (!$projectPlanGroup) {
            $projectPlanGroup = $this->getCategoryService()->addGroup(array(
                'name' => '培训项目',
                'code' => 'projectPlan',
                'depth' => 1,
            ));
        }

        $projectPlanCategory = $this->getCategoryService()->getCategoryByCode('projectPlanDefault');

        if (!$projectPlanCategory) {
            $this->getCategoryService()->createCategory(array(
                'name' => '默认分类',
                'code' => 'projectPlanDefault',
                'weight' => 100,
                'groupId' => $projectPlanGroup['id'],
                'parentId' => 0,
            ));
        }
    }

    protected function isNavigationExist($navigations, $name)
    {
        $exist = false;

        foreach ($navigations as $navigation) {
            if ($name === $navigation['name']) {
                $exist = true;
            }
        }

        return $exist;
    }

    protected function _initSiteSetting()
    {
        $this->output->write('  初始化站点设置');

        $default = array(
            'name' => 'EduSoho企培版',
            'slogan' => '助力先进组织变革',
            'url' => '',
            'logo' => '',
            'seo_keywords' => 'edusoho, 在线教育软件, 在线在线教育解决方案',
            'seo_description' => 'edusoho是强大的在线教育开源软件',
            'master_email' => 'test@edusoho.com',
            'icp' => ' 浙ICP备13006852号-1',
            'analytics' => '',
            'status' => 'open',
            'closed_note' => '',
        );

        $this->getSettingService()->set('site', $default);
        $this->output->writeln(' ...<info>成功</info>');
    }

    public function initQueueuJob()
    {
        $this->output->write('  DataBase消息队列初始化');
        try {
            SystemQueueCrontabinitializer::init();
            $this->output->writeln(' ...<info>成功</info>');
        } catch (\Exception $e) {
            $this->output->writeln(' ...<info>失败</info>'.$e->getMessage());
        }
    }

    /**
     * @return \Biz\System\Service\Impl\SettingServiceImpl
     */
    private function getSettingService()
    {
        return ServiceKernel::instance()->getBiz()->service('System:SettingService');
    }

    /**
     * @return \Biz\User\Service\Impl\UserServiceImpl
     */
    private function getUserService()
    {
        return ServiceKernel::instance()->getBiz()->service('User:UserService');
    }

    /**
     * @return \Biz\Theme\Service\Impl\ThemeServiceImpl
     */
    private function getThemeService()
    {
        return ServiceKernel::instance()->getBiz()->service('Theme:ThemeService');
    }

    /**
     * @return \Biz\Taxonomy\Service\Impl\CategoryServiceImpl
     */
    protected function getCategoryService()
    {
        return ServiceKernel::instance()->getBiz()->service('Taxonomy:CategoryService');
    }

    /**
     * @return \Biz\DiscoveryColumn\Service\Impl\DiscoveryColumnServiceImpl
     */
    protected function getDiscoveryColumnService()
    {
        return ServiceKernel::instance()->getBiz()->service('DiscoveryColumn:DiscoveryColumnService');
    }

    /**
     * @return \Codeages\Biz\Framework\Scheduler\Service\Impl\SchedulerServiceImpl;
     */
    protected function getSchedulerService()
    {
        return ServiceKernel::instance()->getBiz()->service('Scheduler:SchedulerService');
    }

    /**
     * @return \CorporateTrainingBundle\Biz\ManagePermission\Service\ManagePermissionOrgService
     */
    protected function getManagePermissionOrgService()
    {
        return ServiceKernel::instance()->getBiz()->service('CorporateTrainingBundle:ManagePermission:ManagePermissionOrgService');
    }
}
