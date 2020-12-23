DROP TABLE IF EXISTS `activity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `title` varchar(255) NOT NULL COMMENT '标题',
  `remark` text COMMENT '正文',
  `mediaId` int(10) unsigned DEFAULT '0' COMMENT '教学活动详细信息Id，如：视频id, 教室id',
  `mediaType` varchar(50) NOT NULL COMMENT '活动类型',
  `content` text COMMENT '活动描述',
  `length` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '若是视频类型，则表示时长；若是ppt，则表示页数；由具体的活动业务来定义',
  `fromCourseId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属教学计划',
  `fromCourseSetId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属的课程',
  `fromUserId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建者的ID',
  `copyId` int(10) NOT NULL DEFAULT '0' COMMENT '复制来源activity的id',
  `startTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
  `endTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  `syncId` int(11) NOT NULL DEFAULT '0' COMMENT '租赁资源ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `activity_audio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_audio` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `mediaId` int(10) DEFAULT NULL COMMENT '媒体文件ID',
  `hasText` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否包含图文',
  `syncId` int(11) NOT NULL DEFAULT '0' COMMENT '租赁资源ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='视频活动扩展表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `activity_doc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_doc` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mediaId` int(11) NOT NULL,
  `finishType` varchar(32) NOT NULL DEFAULT '' COMMENT 'click, detail',
  `finishDetail` varchar(32) DEFAULT '0' COMMENT '至少观看X分钟',
  `createdTime` int(10) NOT NULL,
  `createdUserId` int(11) NOT NULL,
  `updatedTime` int(11) DEFAULT NULL,
  `syncId` int(11) NOT NULL DEFAULT '0' COMMENT '租赁资源ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `activity_download`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_download` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `mediaCount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '资料数',
  `fileIds` varchar(1024) DEFAULT NULL COMMENT '下载资料Ids',
  `createdTime` int(10) unsigned NOT NULL,
  `updatedTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `activity_flash`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_flash` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mediaId` int(11) NOT NULL,
  `finishType` varchar(32) NOT NULL DEFAULT '' COMMENT 'click, time',
  `finishDetail` varchar(32) DEFAULT '0' COMMENT '至少观看X分钟',
  `createdTime` int(10) NOT NULL,
  `createdUserId` int(11) NOT NULL,
  `updatedTime` int(11) DEFAULT NULL,
  `syncId` int(11) NOT NULL DEFAULT '0' COMMENT '租赁资源ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `activity_learn_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_learn_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `activityId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '教学活动id',
  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `event` varchar(32) NOT NULL COMMENT '事件类型',
  `data` text,
  `watchTime` int(10) unsigned NOT NULL DEFAULT '0',
  `courseTaskId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '教学活动id',
  `learnedTime` int(11) DEFAULT '0',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `mediaType` varchar(32) NOT NULL COMMENT '活动类型',
  PRIMARY KEY (`id`),
  KEY `activityid_userid_event` (`activityId`,`userId`,`event`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `activity_live`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_live` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `liveId` int(11) NOT NULL COMMENT '直播间ID',
  `liveProvider` int(11) NOT NULL COMMENT '直播供应商',
  `replayStatus` enum('ungenerated','generating','generated','videoGenerated') NOT NULL DEFAULT 'ungenerated' COMMENT '回放状态',
  `mediaId` int(11) unsigned DEFAULT '0' COMMENT '视频文件ID',
  `roomCreated` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '直播教室是否已创建',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `activity_ppt`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_ppt` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mediaId` int(11) NOT NULL,
  `playMode` enum('animation','images') NOT NULL DEFAULT 'images' COMMENT '播放模式',
  `finishType` varchar(32) NOT NULL DEFAULT '' COMMENT 'end, time',
  `finishDetail` varchar(32) DEFAULT '0' COMMENT '至少观看X分钟',
  `createdTime` int(11) unsigned NOT NULL DEFAULT '0',
  `createdUserId` int(11) NOT NULL,
  `updatedTime` int(11) unsigned NOT NULL DEFAULT '0',
  `syncId` int(11) NOT NULL DEFAULT '0' COMMENT '租赁资源ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `activity_testpaper`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_testpaper` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '关联activity表的ID',
  `mediaId` int(10) NOT NULL DEFAULT '0' COMMENT '试卷ID',
  `doTimes` smallint(6) NOT NULL DEFAULT '0' COMMENT '考试次数',
  `redoInterval` float(10,1) NOT NULL DEFAULT '0.0' COMMENT '重做时间间隔(小时)',
  `limitedTime` int(10) NOT NULL DEFAULT '0' COMMENT '考试时间',
  `checkType` text,
  `finishCondition` text,
  `requireCredit` int(10) NOT NULL DEFAULT '0' COMMENT '参加考试所需的学分',
  `testMode` varchar(50) NOT NULL DEFAULT 'normal' COMMENT '考试模式',
  `syncId` int(11) NOT NULL DEFAULT '0' COMMENT '租赁资源ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `activity_text`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_text` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `finishType` varchar(32) NOT NULL DEFAULT '' COMMENT 'click, time',
  `finishDetail` varchar(32) DEFAULT '0' COMMENT '至少观看X分钟',
  `createdTime` int(10) NOT NULL,
  `createdUserId` int(11) NOT NULL,
  `updatedTime` int(11) DEFAULT NULL,
  `syncId` int(11) NOT NULL DEFAULT '0' COMMENT '租赁资源ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `activity_video`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_video` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `mediaSource` varchar(32) NOT NULL DEFAULT '' COMMENT '媒体文件来源(self:本站上传,youku:优酷)',
  `mediaId` int(10) NOT NULL DEFAULT '0' COMMENT '媒体文件ID',
  `mediaUri` text COMMENT '媒体文件资UR',
  `finishType` varchar(32) NOT NULL DEFAULT 'end' COMMENT '完成类型',
  `finishDetail` varchar(32) NOT NULL DEFAULT '0' COMMENT '完成条件',
  `syncId` int(11) NOT NULL DEFAULT '0' COMMENT '租赁资源ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='视频活动扩展表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `announcement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `announcement` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '课程公告ID',
  `userId` int(10) unsigned NOT NULL COMMENT '公告发布人ID',
  `targetType` varchar(64) NOT NULL DEFAULT 'course' COMMENT '公告类型',
  `url` varchar(255) NOT NULL,
  `startTime` int(10) unsigned NOT NULL DEFAULT '0',
  `endTime` int(10) unsigned NOT NULL DEFAULT '0',
  `targetId` int(10) unsigned NOT NULL COMMENT '所属ID',
  `content` text NOT NULL COMMENT '公告内容',
  `orgId` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '组织机构ID',
  `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构内部编码',
  `copyId` int(11) NOT NULL DEFAULT '0' COMMENT '复制的公告ID',
  `createdTime` int(10) NOT NULL COMMENT '公告创建时间',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '公告最后更新时间',
  PRIMARY KEY (`id`),
  KEY `announcement_targetType_startTime_endTime_index` (`targetType`,`startTime`,`endTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `app`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '应用ID',
  `name` varchar(255) NOT NULL COMMENT '应用名称',
  `code` varchar(16) NOT NULL COMMENT '应用编码',
  `type` enum('plugin','theme') NOT NULL DEFAULT 'plugin' COMMENT '应用类型(plugin插件应用, theme主题应用)',
  `description` varchar(1024) NOT NULL DEFAULT '' COMMENT '应用描述',
  `icon` varchar(255) NOT NULL DEFAULT '' COMMENT '应用图标',
  `version` varchar(16) NOT NULL COMMENT '应用当前版本',
  `author` varchar(255) NOT NULL DEFAULT '' COMMENT '应用开发者名称',
  `created_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '应用安装时间',
  `updated_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '应用最后更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='已安装的应用';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `area`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `area` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `parentId` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='地理信息表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `article`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `article` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '文章ID',
  `title` varchar(255) NOT NULL COMMENT '文章标题',
  `categoryId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '栏目',
  `tagIds` tinytext COMMENT 'tag标签',
  `source` varchar(1024) DEFAULT '' COMMENT '来源',
  `sourceUrl` varchar(1024) DEFAULT '' COMMENT '来源URL',
  `publishedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发布时间',
  `body` text COMMENT '正文',
  `thumb` varchar(255) NOT NULL DEFAULT '' COMMENT '缩略图',
  `originalThumb` varchar(255) NOT NULL DEFAULT '' COMMENT '缩略图原图',
  `picture` varchar(255) NOT NULL DEFAULT '' COMMENT '文章头图，文章编辑／添加时，自动取正文的第１张图',
  `status` enum('published','unpublished','trash') NOT NULL DEFAULT 'unpublished' COMMENT '状态',
  `hits` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点击量',
  `featured` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否头条',
  `promoted` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '推荐',
  `sticky` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否置顶',
  `postNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '回复数',
  `upsNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点赞数',
  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文章发布人的ID',
  `orgId` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '组织机构ID',
  `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构内部编码',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  PRIMARY KEY (`id`),
  KEY `updatedTime` (`updatedTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `article_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `article_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '栏目名称',
  `code` varchar(64) NOT NULL COMMENT 'URL目录名称',
  `weight` int(11) NOT NULL DEFAULT '0',
  `publishArticle` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '是否允许发布文章',
  `seoTitle` varchar(1024) NOT NULL DEFAULT '' COMMENT '栏目标题',
  `seoKeyword` varchar(1024) NOT NULL DEFAULT '' COMMENT 'SEO 关键字',
  `seoDesc` varchar(1024) NOT NULL DEFAULT '' COMMENT '栏目描述（SEO）',
  `published` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '是否启用（1：启用 0：停用)',
  `parentId` int(10) unsigned NOT NULL DEFAULT '0',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `article_like`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `article_like` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '系统id',
  `articleId` int(10) unsigned NOT NULL COMMENT '资讯id',
  `userId` int(10) unsigned NOT NULL COMMENT '用户id',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点赞时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='资讯点赞表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `batch_notification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `batch_notification` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '群发通知id',
  `type` enum('text','image','video','audio') NOT NULL DEFAULT 'text' COMMENT '通知类型',
  `title` text NOT NULL COMMENT '通知标题',
  `fromId` int(10) unsigned NOT NULL COMMENT '发送人id',
  `content` text NOT NULL COMMENT '通知内容',
  `targetType` text NOT NULL COMMENT '通知发送对象group,global,course,classroom等',
  `targetId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '通知发送对象ID',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发送通知时间',
  `published` int(10) NOT NULL DEFAULT '0' COMMENT '是否已经发送',
  `sendedTime` int(10) NOT NULL DEFAULT '0' COMMENT '群发通知的发送时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='群发通知表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `biz_online`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `biz_online` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `sess_id` varbinary(128) NOT NULL,
  `active_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后活跃时间',
  `deadline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '离线时间',
  `is_login` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '在线用户的id, 0代表游客',
  `ip` varchar(32) NOT NULL DEFAULT '' COMMENT '客户端ip',
  `user_agent` varchar(1024) NOT NULL DEFAULT '',
  `source` varchar(32) NOT NULL DEFAULT 'unknown' COMMENT '当前在线用户的来源，例如：app, pc, mobile',
  `created_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `deadline` (`deadline`),
  KEY `is_login` (`is_login`),
  KEY `active_time` (`active_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `biz_order`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `biz_order` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(1024) NOT NULL DEFAULT '' COMMENT '订单标题',
  `sn` varchar(64) NOT NULL COMMENT '订单号',
  `price_amount` bigint(16) unsigned NOT NULL COMMENT '订单总价',
  `price_type` varchar(32) NOT NULL COMMENT '订单总价的类型，现金支付or虚拟币；money, coin',
  `pay_amount` bigint(16) unsigned NOT NULL COMMENT '应付金额',
  `user_id` int(10) unsigned NOT NULL COMMENT '购买者',
  `seller_id` int(10) unsigned DEFAULT '0' COMMENT '卖家id',
  `status` varchar(32) NOT NULL DEFAULT 'created' COMMENT '订单状态',
  `trade_sn` varchar(64) DEFAULT NULL COMMENT '支付交易号，支付成功后记录',
  `paid_cash_amount` bigint(16) unsigned NOT NULL DEFAULT '0' COMMENT '付款的现金金额，支付成功后记录',
  `paid_coin_amount` bigint(16) unsigned NOT NULL DEFAULT '0' COMMENT '付款的虚拟币金额，支付成功后记录',
  `pay_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '支付时间，支付成功后记录',
  `payment` varchar(32) NOT NULL DEFAULT '' COMMENT '支付类型，支付成功后记录',
  `finish_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '交易成功时间',
  `close_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '交易关闭时间',
  `close_data` text COMMENT '交易关闭描述',
  `close_user_id` int(10) unsigned DEFAULT '0' COMMENT '关闭交易的用户',
  `expired_refund_days` int(10) unsigned DEFAULT '0' COMMENT '退款的到期天数',
  `refund_deadline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '申请退款截止日期',
  `success_data` text COMMENT '交易成功的扩展信息字段',
  `fail_data` text COMMENT '交易失败的扩展信息字段',
  `created_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单的创建者',
  `create_extra` text COMMENT '创建时的自定义字段，json方式存储',
  `created_reason` text COMMENT '订单创建原因, 例如：导入，购买等',
  `callback` text COMMENT '商品中心的异步回调信息',
  `device` varchar(32) DEFAULT NULL COMMENT '下单设备（pc、mobile、app）',
  `source` varchar(16) NOT NULL DEFAULT 'self' COMMENT '订单来源：网校本身、微营销、第三方系统',
  `created_time` int(10) unsigned NOT NULL DEFAULT '0',
  `updated_time` int(10) unsigned NOT NULL DEFAULT '0',
  `invoice_sn` varchar(64) DEFAULT '' COMMENT '申请开票sn',
  PRIMARY KEY (`id`),
  UNIQUE KEY `sn` (`sn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `biz_order_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `biz_order_item` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(1024) NOT NULL COMMENT '商品名称',
  `detail` text COMMENT '商品描述',
  `sn` varchar(64) NOT NULL COMMENT '编号',
  `order_id` int(10) unsigned NOT NULL COMMENT '订单id',
  `num` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '数量',
  `unit` varchar(16) DEFAULT NULL COMMENT '单位',
  `status` varchar(32) NOT NULL DEFAULT 'created' COMMENT '商品状态',
  `refund_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最新退款id',
  `refund_status` varchar(32) NOT NULL DEFAULT '' COMMENT '退款状态',
  `price_amount` bigint(16) unsigned NOT NULL COMMENT '商品总价格',
  `pay_amount` bigint(16) unsigned NOT NULL COMMENT '商品应付金额',
  `target_id` int(10) unsigned NOT NULL COMMENT '商品id',
  `target_type` varchar(32) NOT NULL COMMENT '商品类型',
  `pay_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '支付时间',
  `finish_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '交易成功时间',
  `close_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '交易关闭时间',
  `user_id` int(10) unsigned NOT NULL COMMENT '购买者',
  `seller_id` int(10) unsigned DEFAULT '0' COMMENT '卖家id',
  `snapshot` text COMMENT '商品快照',
  `create_extra` text COMMENT '创建时的自定义字段，json方式存储',
  `created_time` int(10) unsigned NOT NULL DEFAULT '0',
  `updated_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `sn` (`sn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `biz_order_item_deduct`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `biz_order_item_deduct` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL COMMENT '订单id',
  `detail` text COMMENT '描述',
  `item_id` int(10) unsigned NOT NULL COMMENT '商品id',
  `deduct_type` varchar(32) NOT NULL DEFAULT '' COMMENT '促销类型',
  `deduct_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '对应的促销活动id',
  `deduct_amount` bigint(16) unsigned NOT NULL COMMENT '扣除的价格',
  `status` varchar(32) NOT NULL DEFAULT 'created' COMMENT '商品状态',
  `user_id` int(10) unsigned NOT NULL COMMENT '购买者',
  `seller_id` int(10) unsigned DEFAULT '0' COMMENT '卖家id',
  `snapshot` text COMMENT '促销快照',
  `created_time` int(10) unsigned NOT NULL DEFAULT '0',
  `updated_time` int(10) unsigned NOT NULL DEFAULT '0',
  `deduct_type_name` varchar(255) NOT NULL DEFAULT '' COMMENT '优惠类型',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `biz_order_item_refund`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `biz_order_item_refund` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_refund_id` int(10) unsigned NOT NULL COMMENT '退款订单id',
  `order_id` int(10) unsigned NOT NULL COMMENT '订单id',
  `order_item_id` int(10) unsigned NOT NULL COMMENT '订单中的商品的id',
  `target_id` int(10) unsigned NOT NULL COMMENT '商品id',
  `target_type` varchar(32) NOT NULL COMMENT '商品类型',
  `user_id` int(10) unsigned NOT NULL COMMENT '退款人',
  `amount` bigint(16) unsigned NOT NULL DEFAULT '0' COMMENT '涉及金额',
  `coin_amount` bigint(16) unsigned NOT NULL DEFAULT '0' COMMENT '涉及虚拟币金额',
  `status` varchar(32) NOT NULL DEFAULT 'created' COMMENT '退款状态',
  `created_user_id` int(10) unsigned NOT NULL COMMENT '申请者',
  `created_time` int(10) unsigned NOT NULL DEFAULT '0',
  `updated_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `biz_order_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `biz_order_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单id',
  `status` varchar(32) NOT NULL COMMENT '订单状态',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建用户',
  `deal_data` text COMMENT '处理数据',
  `order_refund_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '退款id',
  `ip` varchar(32) NOT NULL DEFAULT '' COMMENT 'ip',
  `created_time` int(10) unsigned NOT NULL DEFAULT '0',
  `updated_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `biz_order_refund`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `biz_order_refund` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(1024) NOT NULL DEFAULT '' COMMENT '退款单标题',
  `order_id` int(10) unsigned NOT NULL COMMENT '订单id',
  `order_item_id` int(10) unsigned NOT NULL COMMENT '退款商品的id',
  `sn` varchar(64) NOT NULL COMMENT '退款订单编号',
  `user_id` int(10) unsigned NOT NULL COMMENT '退款人',
  `reason` text COMMENT '退款的理由',
  `amount` bigint(16) unsigned NOT NULL COMMENT '退款总金额',
  `currency` varchar(32) NOT NULL DEFAULT 'money' COMMENT '货币类型: coin, money',
  `deal_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '处理时间',
  `deal_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '处理人',
  `status` varchar(32) NOT NULL DEFAULT 'created' COMMENT '退款状态',
  `deal_reason` text COMMENT '处理理由',
  `refund_cash_amount` bigint(16) unsigned NOT NULL DEFAULT '0' COMMENT '退款的现金金额',
  `refund_coin_amount` bigint(16) unsigned NOT NULL DEFAULT '0' COMMENT '退款的虚拟币金额',
  `created_user_id` int(10) unsigned NOT NULL COMMENT '申请者',
  `created_time` int(10) unsigned NOT NULL DEFAULT '0',
  `updated_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `sn` (`sn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `biz_pay_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `biz_pay_account` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL COMMENT '所属用户',
  `password` varchar(64) NOT NULL DEFAULT '' COMMENT '密码',
  `salt` varchar(64) NOT NULL DEFAULT '',
  `created_time` int(10) unsigned NOT NULL DEFAULT '0',
  `updated_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `biz_pay_cashflow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `biz_pay_cashflow` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(1024) NOT NULL DEFAULT '' COMMENT '标题',
  `sn` varchar(64) NOT NULL COMMENT '账目流水号',
  `parent_sn` varchar(64) DEFAULT NULL COMMENT '本次交易的上一个账单的流水号',
  `user_id` int(10) unsigned NOT NULL COMMENT '账号ID，即用户ID',
  `user_balance` bigint(16) NOT NULL DEFAULT '0' COMMENT '账单生成后的对应账户的余额，若amount_type为coin，对应的是虚拟币账户，amount_type为money，对应的是现金庄户余额',
  `buyer_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '买家',
  `type` enum('inflow','outflow') NOT NULL COMMENT '流水类型',
  `action` varchar(32) NOT NULL DEFAULT '' COMMENT 'refund, purchase, recharge',
  `amount` bigint(16) unsigned NOT NULL DEFAULT '0' COMMENT '金额',
  `amount_type` varchar(32) NOT NULL COMMENT 'ammount的类型：coin, money',
  `currency` varchar(32) NOT NULL COMMENT '支付的货币: coin, CNY...',
  `order_sn` varchar(64) NOT NULL COMMENT '订单号',
  `trade_sn` varchar(64) NOT NULL COMMENT '交易号',
  `platform` varchar(32) NOT NULL DEFAULT 'none' COMMENT '支付平台：none, alipay, wxpay...',
  `created_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sn` (`sn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='帐目流水';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `biz_pay_security_answer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `biz_pay_security_answer` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL COMMENT '所属用户',
  `question_key` varchar(64) NOT NULL DEFAULT '' COMMENT '安全问题的key',
  `answer` varchar(64) NOT NULL DEFAULT '',
  `salt` varchar(64) NOT NULL DEFAULT '',
  `created_time` int(10) unsigned NOT NULL DEFAULT '0',
  `updated_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`question_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `biz_pay_trade`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `biz_pay_trade` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(1024) NOT NULL COMMENT '标题',
  `trade_sn` varchar(64) NOT NULL COMMENT '交易号',
  `order_sn` varchar(64) NOT NULL COMMENT '客户订单号',
  `status` varchar(32) NOT NULL DEFAULT 'created' COMMENT '交易状态',
  `amount` bigint(16) unsigned NOT NULL DEFAULT '0' COMMENT '订单的需支付金额',
  `price_type` varchar(32) NOT NULL COMMENT '标价类型，现金支付or虚拟币；money, coin',
  `currency` varchar(32) NOT NULL DEFAULT '' COMMENT '支付的货币类型',
  `coin_amount` bigint(16) unsigned NOT NULL DEFAULT '0' COMMENT '虚拟币支付金额',
  `cash_amount` bigint(16) unsigned NOT NULL DEFAULT '0' COMMENT '现金支付金额',
  `rate` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '虚拟币和现金的汇率',
  `type` varchar(32) NOT NULL DEFAULT 'purchase' COMMENT '交易类型：purchase，recharge，refund',
  `seller_id` int(10) unsigned DEFAULT '0' COMMENT '卖家id',
  `user_id` int(10) unsigned NOT NULL COMMENT '买家id',
  `pay_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '交易时间',
  `apply_refund_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '申请退款时间',
  `refund_success_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '成功退款时间',
  `notify_data` text,
  `platform` varchar(32) NOT NULL DEFAULT '' COMMENT '第三方支付平台',
  `platform_sn` varchar(64) NOT NULL DEFAULT '' COMMENT '第三方支付平台的交易号',
  `platform_type` text COMMENT '在第三方系统中的支付方式',
  `platform_created_result` text,
  `platform_created_params` text COMMENT '在第三方系统创建支付订单时的参数信息',
  `updated_time` int(10) unsigned NOT NULL DEFAULT '0',
  `created_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `trade_sn` (`trade_sn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `biz_pay_user_balance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `biz_pay_user_balance` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL COMMENT '用户',
  `amount` bigint(16) NOT NULL DEFAULT '0' COMMENT '账户的虚拟币余额',
  `cash_amount` bigint(16) unsigned NOT NULL DEFAULT '0' COMMENT '现金余额',
  `locked_amount` bigint(16) unsigned NOT NULL DEFAULT '0' COMMENT '冻结虚拟币金额',
  `recharge_amount` bigint(16) unsigned NOT NULL DEFAULT '0' COMMENT '充值总额',
  `purchase_amount` bigint(16) unsigned NOT NULL DEFAULT '0' COMMENT '消费总额',
  `updated_time` int(10) unsigned NOT NULL DEFAULT '0',
  `created_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `biz_queue_failed_job`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `biz_queue_failed_job` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(32) COLLATE utf8_unicode_ci NOT NULL COMMENT '队列名',
  `body` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '任务消息体',
  `class` varchar(1024) COLLATE utf8_unicode_ci NOT NULL COMMENT '队列执行者的类名',
  `timeout` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务执行超时时间',
  `priority` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务优先级',
  `reason` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '失败原因',
  `failed_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务执行失败时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `biz_queue_job`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `biz_queue_job` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(32) COLLATE utf8_unicode_ci NOT NULL COMMENT '队列名',
  `body` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '任务消息体',
  `class` varchar(1024) COLLATE utf8_unicode_ci NOT NULL COMMENT '队列执行者的类名',
  `timeout` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务执行超时时间',
  `priority` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务优先级',
  `executions` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '执行次数',
  `available_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务可执行时的时间戳',
  `reserved_time` int(10) unsigned DEFAULT '0' COMMENT '任务被捕获开始执行的时间戳',
  `expired_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务执行超时的时间戳',
  PRIMARY KEY (`id`),
  KEY `idx_queue_reserved_time` (`queue`,`reserved_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `biz_scheduler_job`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `biz_scheduler_job` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
  `name` varchar(128) NOT NULL COMMENT '任务名称',
  `pool` varchar(64) NOT NULL DEFAULT 'default' COMMENT '所属组',
  `source` varchar(64) NOT NULL DEFAULT 'MAIN' COMMENT '来源',
  `expression` varchar(128) NOT NULL DEFAULT '' COMMENT '任务触发的表达式',
  `class` varchar(128) NOT NULL COMMENT '任务的Class名称',
  `args` text COMMENT '任务参数',
  `priority` int(10) unsigned NOT NULL DEFAULT '50' COMMENT '优先级',
  `pre_fire_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务下次执行的时间',
  `next_fire_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务下次执行的时间',
  `misfire_threshold` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '触发过期的阈值(秒)',
  `misfire_policy` varchar(32) NOT NULL COMMENT '触发过期策略: missed, executing',
  `enabled` tinyint(1) DEFAULT '1' COMMENT '是否启用',
  `creator_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务创建人',
  `updated_time` int(10) unsigned NOT NULL COMMENT '修改时间',
  `created_time` int(10) unsigned NOT NULL COMMENT '任务创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `biz_scheduler_job_fired`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `biz_scheduler_job_fired` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
  `job_id` int(10) NOT NULL COMMENT 'jobId',
  `job_name` varchar(128) NOT NULL DEFAULT '' COMMENT '任务名称',
  `fired_time` int(10) unsigned NOT NULL COMMENT '触发时间',
  `priority` int(10) unsigned NOT NULL DEFAULT '50' COMMENT '优先级',
  `retry_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '重试次数',
  `status` varchar(32) NOT NULL DEFAULT 'acquired' COMMENT '状态：acquired, executing, success, missed, ignore, failure',
  `peak_memory` bigint(15) unsigned NOT NULL DEFAULT '0' COMMENT '内存峰值/byte',
  `start_time` bigint(15) unsigned NOT NULL DEFAULT '0' COMMENT '起始时间/毫秒',
  `end_time` bigint(15) unsigned NOT NULL DEFAULT '0' COMMENT '终止时间/毫秒',
  `cost_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '花费时间/毫秒',
  `process_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'jobProcessId',
  `failure_msg` text,
  `updated_time` int(10) unsigned NOT NULL COMMENT '修改时间',
  `created_time` int(10) unsigned NOT NULL COMMENT '任务创建时间',
  `job_detail` text NOT NULL COMMENT 'job的详细信息，是biz_job表中冗余数据',
  PRIMARY KEY (`id`),
  KEY `job_fired_id_and_status` (`job_id`,`status`),
  KEY `job_fired_time_and_status` (`fired_time`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `biz_scheduler_job_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `biz_scheduler_job_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
  `job_id` int(10) unsigned NOT NULL COMMENT '任务编号',
  `job_fired_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '激活的任务编号',
  `hostname` varchar(128) NOT NULL DEFAULT '' COMMENT '执行的主机',
  `name` varchar(128) NOT NULL COMMENT '任务名称',
  `pool` varchar(64) NOT NULL DEFAULT 'default' COMMENT '所属组',
  `source` varchar(64) NOT NULL COMMENT '来源',
  `class` varchar(128) NOT NULL COMMENT '任务的Class名称',
  `args` text COMMENT '任务参数',
  `priority` int(10) unsigned NOT NULL DEFAULT '50' COMMENT '优先级',
  `status` varchar(32) NOT NULL DEFAULT 'waiting' COMMENT '任务执行状态',
  `created_time` int(10) unsigned NOT NULL COMMENT '任务创建时间',
  `message` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT '日志信息',
  `trace` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT '异常追踪信息',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `biz_scheduler_job_pool`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `biz_scheduler_job_pool` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `name` varchar(128) NOT NULL DEFAULT 'default' COMMENT '组名',
  `max_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最大数',
  `num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '已使用的数量',
  `timeout` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '执行超时时间',
  `updated_time` int(10) unsigned NOT NULL COMMENT '更新时间',
  `created_time` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `biz_scheduler_job_process`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `biz_scheduler_job_process` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` varchar(32) NOT NULL DEFAULT '' COMMENT '进程组ID',
  `start_time` bigint(15) unsigned NOT NULL DEFAULT '0' COMMENT '起始时间/毫秒',
  `end_time` bigint(15) unsigned NOT NULL DEFAULT '0' COMMENT '终止时间/毫秒',
  `cost_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '花费时间/毫秒',
  `peak_memory` bigint(15) unsigned NOT NULL DEFAULT '0' COMMENT '内存峰值/byte',
  `created_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `biz_session`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `biz_session` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `sess_id` varbinary(128) NOT NULL,
  `sess_data` blob NOT NULL,
  `sess_time` int(10) unsigned NOT NULL,
  `sess_deadline` int(10) unsigned NOT NULL,
  `created_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sess_id` (`sess_id`),
  KEY `sess_deadline` (`sess_deadline`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `biz_setting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `biz_setting` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `data` longtext NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `biz_targetlog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `biz_targetlog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `target_type` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '日志对象类型',
  `target_id` varchar(48) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '日志对象ID',
  `action` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '日志行为',
  `level` smallint(6) NOT NULL DEFAULT '0' COMMENT '日志等级',
  `message` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '日志信息',
  `context` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '日志上下文',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '操作人ID',
  `ip` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '操作人IP',
  `created_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_target` (`target_type`(8),`target_id`(8)),
  KEY `idx_level` (`level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `biz_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `biz_token` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `place` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '使用场景',
  `_key` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'KEY',
  `data` text COLLATE utf8_unicode_ci NOT NULL COMMENT '数据',
  `expired_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '过期时间',
  `times` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最多可被校验的次数',
  `remaining_times` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '剩余可被校验的次数',
  `created_time` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `_key` (`_key`),
  KEY `expired_time` (`expired_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `biz_xapi_statement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `biz_xapi_statement` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(64) NOT NULL,
  `version` varchar(32) NOT NULL DEFAULT '' COMMENT '版本号',
  `data` text NOT NULL COMMENT '数据',
  `push_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上报时间',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属用户',
  `status` varchar(16) NOT NULL DEFAULT 'created' COMMENT '状态: created, pushing, pushed',
  `created_time` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `blacklist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blacklist` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `userId` int(10) unsigned NOT NULL COMMENT '名单拥有者id',
  `blackId` int(10) unsigned NOT NULL COMMENT '黑名单用户id',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '加入黑名单时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='黑名单表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `block`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `block` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL COMMENT '用户Id',
  `blockTemplateId` int(11) NOT NULL COMMENT '模版ID',
  `orgId` int(11) NOT NULL DEFAULT '1' COMMENT '组织机构Id',
  `content` text COMMENT '编辑区的内容',
  `code` varchar(255) NOT NULL DEFAULT '' COMMENT '编辑区编码',
  `meta` text COMMENT '编辑区元信息',
  `data` text COMMENT '编辑区内容',
  `createdTime` int(11) unsigned NOT NULL,
  `updateTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `block_code_orgId_index` (`code`,`orgId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `block_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `block_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `blockId` int(11) NOT NULL COMMENT 'blockId',
  `templateData` text COMMENT '模板历史数据',
  `data` text COMMENT 'block数据',
  `content` text COMMENT 'content',
  `userId` int(11) NOT NULL COMMENT 'userId',
  `createdTime` int(11) unsigned NOT NULL COMMENT 'createdTime',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='历史表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `block_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `block_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '模版ID',
  `title` varchar(255) NOT NULL COMMENT '标题',
  `mode` enum('html','template') NOT NULL DEFAULT 'html' COMMENT '模式',
  `template` text COMMENT '模板',
  `templateName` varchar(255) DEFAULT NULL COMMENT '编辑区模板名字',
  `templateData` text COMMENT '模板数据',
  `content` text COMMENT '默认内容',
  `data` text COMMENT '编辑区内容',
  `code` varchar(255) NOT NULL DEFAULT '' COMMENT '编辑区编码',
  `meta` text COMMENT '编辑区元信息',
  `tips` varchar(255) DEFAULT NULL,
  `category` varchar(60) NOT NULL DEFAULT 'system' COMMENT '分类(系统/主题)',
  `createdTime` int(11) unsigned NOT NULL COMMENT '创建时间',
  `updateTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='编辑区模板';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '缓存ID',
  `name` varchar(128) NOT NULL DEFAULT '' COMMENT '缓存名称',
  `data` longblob COMMENT '缓存数据',
  `serialized` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '缓存是否为序列化的标记位',
  `expiredTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '缓存过期时间',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '缓存创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `expiredTime` (`expiredTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `card`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `card` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `cardId` varchar(255) NOT NULL DEFAULT '' COMMENT '卡的ID',
  `cardType` varchar(255) NOT NULL DEFAULT '' COMMENT '卡的类型',
  `deadline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '到期时间',
  `useTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '使用时间',
  `status` enum('used','receive','invalid','deleted') NOT NULL DEFAULT 'receive' COMMENT '使用状态',
  `userId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '使用者',
  `createdTime` int(10) unsigned NOT NULL COMMENT '领取时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cash_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cash_account` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL,
  `cash` float(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cash_change`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cash_change` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL,
  `amount` double(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cash_flow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cash_flow` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL COMMENT '帐号ID，即用户ID',
  `sn` bigint(20) unsigned NOT NULL COMMENT '账目流水号',
  `type` enum('inflow','outflow') NOT NULL COMMENT '流水类型',
  `amount` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
  `cashType` enum('RMB','Coin') NOT NULL DEFAULT 'Coin' COMMENT '账单类型',
  `cash` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '账单生成后的余额',
  `parentSn` bigint(20) DEFAULT NULL COMMENT '上一个账单的流水号',
  `name` varchar(1024) NOT NULL DEFAULT '' COMMENT '帐目名称',
  `orderSn` varchar(40) NOT NULL COMMENT '订单号',
  `category` varchar(128) NOT NULL DEFAULT '' COMMENT '帐目类目',
  `payment` varchar(32) DEFAULT '',
  `note` text COMMENT '备注',
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tradeNo` (`sn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='帐目流水';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cash_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cash_orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sn` varchar(32) NOT NULL COMMENT '订单号',
  `status` enum('created','paid','cancelled') NOT NULL,
  `title` varchar(255) NOT NULL,
  `amount` float(10,2) unsigned NOT NULL DEFAULT '0.00',
  `payment` varchar(32) NOT NULL DEFAULT 'none',
  `paidTime` int(10) unsigned NOT NULL DEFAULT '0',
  `note` varchar(255) NOT NULL DEFAULT '',
  `targetType` varchar(64) NOT NULL DEFAULT 'coin' COMMENT '订单类型',
  `token` varchar(50) DEFAULT NULL COMMENT '令牌',
  `data` text COMMENT '订单业务数据',
  `userId` int(10) unsigned NOT NULL DEFAULT '0',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `sn` (`sn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cash_orders_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cash_orders_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `orderId` int(10) unsigned NOT NULL,
  `message` text,
  `data` text,
  `userId` int(10) unsigned NOT NULL DEFAULT '0',
  `ip` varchar(255) NOT NULL,
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
  `type` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '分类ID',
  `code` varchar(64) NOT NULL DEFAULT '' COMMENT '分类编码',
  `name` varchar(255) NOT NULL COMMENT '分类名称',
  `icon` varchar(255) NOT NULL DEFAULT '' COMMENT '图标',
  `path` varchar(255) NOT NULL DEFAULT '' COMMENT '分类完整路径',
  `weight` int(11) NOT NULL DEFAULT '0' COMMENT '分类权重',
  `groupId` int(10) unsigned NOT NULL COMMENT '分类组ID',
  `parentId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父分类ID',
  `orgId` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '组织机构ID',
  `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构内部编码',
  `description` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uri` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `category_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `category_group` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '分类组ID',
  `code` varchar(64) NOT NULL COMMENT '分类组编码',
  `name` varchar(255) NOT NULL COMMENT '分类组名称',
  `depth` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '该组下分类允许的最大层级数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `classroom`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `classroom` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL COMMENT '标题',
  `status` enum('closed','draft','published') NOT NULL DEFAULT 'draft' COMMENT '状态关闭，未发布，发布',
  `about` text COMMENT '简介',
  `categoryId` int(10) NOT NULL DEFAULT '0' COMMENT '分类id',
  `description` text COMMENT '课程说明',
  `price` float(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '价格',
  `vipLevelId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '支持的vip等级',
  `smallPicture` varchar(255) NOT NULL DEFAULT '' COMMENT '小图',
  `middlePicture` varchar(255) NOT NULL DEFAULT '' COMMENT '中图',
  `largePicture` varchar(255) NOT NULL DEFAULT '' COMMENT '大图',
  `headTeacherId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '班主任ID',
  `teacherIds` varchar(255) NOT NULL DEFAULT '' COMMENT '教师IDs',
  `assistantIds` text COMMENT '助教Ids',
  `hitNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点击数',
  `auditorNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '旁听生数',
  `studentNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学员数',
  `courseNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课程数',
  `lessonNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课时数',
  `threadNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '话题数',
  `noteNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '班级笔记数量',
  `postNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '回复数',
  `income` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '收入',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `service` varchar(255) DEFAULT NULL COMMENT '班级服务',
  `private` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否封闭班级',
  `recommended` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否为推荐班级',
  `recommendedSeq` int(10) unsigned NOT NULL DEFAULT '100' COMMENT '推荐序号',
  `recommendedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐时间',
  `rating` float unsigned NOT NULL DEFAULT '0' COMMENT '排行数值',
  `ratingNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '投票人数',
  `hotSeq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最热班级排序',
  `maxRate` tinyint(3) unsigned NOT NULL DEFAULT '100' COMMENT '最大抵扣百分比',
  `showable` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否开放展示',
  `buyable` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否开放购买',
  `conversationId` varchar(255) NOT NULL DEFAULT '0',
  `orgId` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '组织机构ID',
  `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构内部编码',
  `expiryMode` varchar(32) NOT NULL DEFAULT 'forever' COMMENT '学习有效期模式：date、days、forever',
  `expiryValue` int(10) NOT NULL DEFAULT '0' COMMENT '有效期',
  `creator` int(10) NOT NULL DEFAULT '0' COMMENT '班级创建者',
  `conditionalAccess` int(11) NOT NULL DEFAULT '0' COMMENT '是否开启加入条件',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `classroom_courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `classroom_courses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `classroomId` int(10) unsigned NOT NULL COMMENT '班级ID',
  `courseId` int(10) unsigned NOT NULL COMMENT '课程ID',
  `parentCourseId` int(10) unsigned NOT NULL COMMENT '父课程Id',
  `disabled` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否禁用',
  `seq` int(5) unsigned NOT NULL DEFAULT '0' COMMENT '班级课程顺序',
  `courseSetId` int(10) NOT NULL DEFAULT '0' COMMENT '课程ID',
  PRIMARY KEY (`id`),
  KEY `classroom_courses_courseId_index` (`courseId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `classroom_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `classroom_member` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `classroomId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '班级ID',
  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `orderId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单ID',
  `levelId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '等级',
  `noteNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '笔记数',
  `threadNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '话题数',
  `locked` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '学员是否被锁定',
  `remark` text COMMENT '备注',
  `role` varchar(255) NOT NULL DEFAULT 'auditor' COMMENT '角色',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `lastLearnTime` int(10) DEFAULT NULL COMMENT '最后学习时间',
  `learnedNum` int(10) DEFAULT NULL COMMENT '已学课时数',
  `updatedTime` int(10) NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  `deadline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '到期时间',
  `refundDeadline` int(10) NOT NULL DEFAULT '0' COMMENT '退款截止时间',
  `deadlineNotified` int(10) NOT NULL DEFAULT '0' COMMENT '有效期通知',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `classroom_review`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `classroom_review` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `classroomId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '班级ID',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
  `content` text NOT NULL COMMENT '内容',
  `rating` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评分0-5',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `parentId` int(10) NOT NULL DEFAULT '0' COMMENT '回复ID',
  `updatedTime` int(10) DEFAULT NULL,
  `meta` text COMMENT '评价元信息',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cloud_app`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cloud_app` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '云应用ID',
  `name` varchar(255) NOT NULL COMMENT '云应用名称',
  `code` varchar(64) NOT NULL COMMENT '云应用编码',
  `type` varchar(64) NOT NULL DEFAULT 'plugin' COMMENT '应用类型(core系统，plugin插件应用, theme主题应用)',
  `protocol` tinyint(3) unsigned NOT NULL DEFAULT '2',
  `description` text NOT NULL COMMENT '云应用描述',
  `icon` varchar(255) NOT NULL COMMENT '云应用图标',
  `version` varchar(32) NOT NULL COMMENT '云应用当前版本',
  `fromVersion` varchar(32) NOT NULL DEFAULT '0.0.0' COMMENT '云应用更新前版本',
  `developerId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '云应用开发者用户ID',
  `developerName` varchar(255) NOT NULL DEFAULT '' COMMENT '云应用开发者名称',
  `edusohoMinVersion` varchar(32) NOT NULL DEFAULT '0.0.0' COMMENT '依赖Edusoho的最小版本',
  `edusohoMaxVersion` varchar(32) NOT NULL DEFAULT 'up' COMMENT '依赖Edusoho的最大版本',
  `installedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '云应用安装时间',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '云应用最后更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='已安装的应用';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cloud_app_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cloud_app_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '云应用运行日志ID',
  `code` varchar(32) NOT NULL DEFAULT '' COMMENT '应用编码',
  `name` varchar(32) NOT NULL DEFAULT '' COMMENT '应用名称',
  `fromVersion` varchar(32) DEFAULT NULL COMMENT '升级前版本',
  `toVersion` varchar(32) NOT NULL DEFAULT '' COMMENT '升级后版本',
  `type` enum('install','upgrade') NOT NULL DEFAULT 'install' COMMENT '升级类型',
  `dbBackupPath` varchar(255) NOT NULL DEFAULT '' COMMENT '数据库备份文件',
  `sourceBackupPath` varchar(255) NOT NULL DEFAULT '' COMMENT '源文件备份地址',
  `status` varchar(32) NOT NULL COMMENT '升级状态(ROLLBACK,ERROR,SUCCESS,RECOVERED)',
  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `ip` varchar(32) NOT NULL DEFAULT '' COMMENT 'IP',
  `message` text COMMENT '失败原因',
  `createdTime` int(10) unsigned NOT NULL COMMENT '日志记录时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='应用升级日志';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cloud_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cloud_data` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `timestamp` int(10) unsigned NOT NULL,
  `createdTime` int(10) unsigned NOT NULL,
  `updatedTime` int(10) unsigned NOT NULL,
  `createdUserId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `comment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `objectType` varchar(32) NOT NULL,
  `objectId` int(10) unsigned NOT NULL,
  `userId` int(10) unsigned NOT NULL DEFAULT '0',
  `content` text NOT NULL,
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `objectType` (`objectType`,`objectId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `content` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '内容ID',
  `title` varchar(255) NOT NULL COMMENT '内容标题',
  `editor` enum('richeditor','none') NOT NULL DEFAULT 'richeditor' COMMENT '编辑器选择类型字段',
  `type` varchar(255) NOT NULL COMMENT '内容类型',
  `alias` varchar(255) NOT NULL DEFAULT '' COMMENT '内容别名',
  `summary` text COMMENT '内容摘要',
  `body` text COMMENT '内容正文',
  `picture` varchar(255) NOT NULL DEFAULT '' COMMENT '内容头图',
  `template` varchar(255) NOT NULL DEFAULT '' COMMENT '内容模板',
  `status` enum('published','unpublished','trash') NOT NULL COMMENT '内容状态',
  `categoryId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容分类ID',
  `tagIds` tinytext COMMENT '内容标签ID',
  `hits` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容点击量',
  `featured` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '是否头条',
  `promoted` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '是否推荐',
  `sticky` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否置顶',
  `userId` int(10) unsigned NOT NULL COMMENT '发布人ID',
  `field1` text COMMENT '扩展字段',
  `field2` text COMMENT '扩展字段',
  `field3` text COMMENT '扩展字段',
  `field4` text COMMENT '扩展字段',
  `field5` text COMMENT '扩展字段',
  `field6` text COMMENT '扩展字段',
  `field7` text COMMENT '扩展字段',
  `field8` text COMMENT '扩展字段',
  `field9` text COMMENT '扩展字段',
  `field10` text COMMENT '扩展字段',
  `publishedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发布时间',
  `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `coupon`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `coupon` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL COMMENT '优惠码',
  `type` enum('minus','discount') NOT NULL COMMENT '优惠方式',
  `status` enum('used','unused','receive','using') NOT NULL DEFAULT 'unused',
  `rate` float(10,2) unsigned NOT NULL COMMENT '若优惠方式为打折，则为打折率，若为抵价，则为抵价金额',
  `batchId` int(10) unsigned DEFAULT NULL COMMENT '批次号',
  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '使用者',
  `deadline` int(10) unsigned NOT NULL COMMENT '失效时间',
  `targetType` varchar(64) DEFAULT NULL COMMENT '使用对象类型',
  `targetId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '使用对象',
  `orderId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '订单号',
  `orderTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '使用时间',
  `createdTime` int(10) unsigned NOT NULL,
  `receiveTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '接收时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='优惠码表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `course`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '课程ID',
  `title` varchar(1024) NOT NULL COMMENT '课程标题',
  `subtitle` varchar(1024) NOT NULL DEFAULT '' COMMENT '课程副标题',
  `status` enum('draft','published','closed') NOT NULL DEFAULT 'draft' COMMENT '课程状态',
  `type` varchar(255) NOT NULL DEFAULT 'normal' COMMENT '课程类型',
  `maxStudentNum` int(11) NOT NULL DEFAULT '0' COMMENT '直播课程最大学员数上线',
  `price` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '课程价格',
  `originPrice` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '课程人民币原价',
  `coinPrice` float(10,2) NOT NULL DEFAULT '0.00',
  `originCoinPrice` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '课程虚拟币原价',
  `expiryMode` enum('date','days','none') NOT NULL DEFAULT 'none' COMMENT '有效期模式（截止日期|有效期天数|不设置）',
  `expiryDay` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课程过期天数',
  `showStudentNumType` enum('opened','closed') NOT NULL DEFAULT 'opened' COMMENT '学员数显示模式',
  `serializeMode` enum('none','serialize','finished') NOT NULL DEFAULT 'none' COMMENT '连载模式',
  `income` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '课程销售总收入',
  `lessonNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课时数',
  `giveCredit` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学完课程所有课时，可获得的总学分',
  `rating` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排行分数',
  `ratingNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '投票人数',
  `vipLevelId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '可以免费看的，会员等级',
  `categoryId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分类ID',
  `tags` text COMMENT '标签IDs',
  `smallPicture` varchar(255) NOT NULL DEFAULT '' COMMENT '小图',
  `middlePicture` varchar(255) NOT NULL DEFAULT '' COMMENT '中图',
  `largePicture` varchar(255) NOT NULL DEFAULT '' COMMENT '大图',
  `about` text COMMENT '简介',
  `teacherIds` text COMMENT '显示的课程教师IDs',
  `goals` text COMMENT '课程目标',
  `audiences` text COMMENT '适合人群',
  `recommended` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否为推荐课程',
  `recommendedSeq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐序号',
  `recommendedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐时间',
  `locationId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上课地区ID',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '上课地区地址',
  `studentNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学员数',
  `hitNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '查看次数',
  `userId` int(10) unsigned NOT NULL COMMENT '课程发布人ID',
  `discountId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '折扣活动ID',
  `discount` float(10,2) NOT NULL DEFAULT '10.00' COMMENT '折扣',
  `deadlineNotify` enum('active','none') NOT NULL DEFAULT 'none' COMMENT '开启有效期通知',
  `daysOfNotifyBeforeDeadline` int(10) NOT NULL DEFAULT '0',
  `useInClassroom` enum('single','more') NOT NULL DEFAULT 'single' COMMENT '课程能否用于多个班级',
  `watchLimit` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '视频观看次数限制',
  `singleBuy` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '加入班级后课程能否单独购买',
  `createdTime` int(10) unsigned NOT NULL COMMENT '课程创建时间',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  `freeStartTime` int(10) NOT NULL DEFAULT '0',
  `freeEndTime` int(10) NOT NULL DEFAULT '0',
  `approval` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否需要实名认证',
  `parentId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课程的父Id',
  `noteNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课程笔记数量',
  `locked` int(10) NOT NULL DEFAULT '0' COMMENT '是否上锁1上锁,0解锁',
  `maxRate` tinyint(3) unsigned NOT NULL DEFAULT '100' COMMENT '最大抵扣百分比',
  `buyable` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否开放购买',
  `buyExpiryTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '购买开放有效期',
  `tryLookable` tinyint(4) NOT NULL DEFAULT '0',
  `tryLookTime` int(11) NOT NULL DEFAULT '0',
  `conversationId` varchar(255) NOT NULL DEFAULT '0',
  `orgId` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '组织机构ID',
  `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构内部编码',
  PRIMARY KEY (`id`),
  KEY `updatedTime` (`updatedTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `course_chapter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_chapter` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '课程章节ID',
  `courseId` int(10) unsigned NOT NULL COMMENT '章节所属课程ID',
  `type` varchar(255) NOT NULL DEFAULT 'chapter' COMMENT '章节类型：chapter为章节，unit为单元，lesson为课时。',
  `number` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '章节编号',
  `seq` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '章节序号',
  `title` varchar(255) NOT NULL COMMENT '章节名称',
  `createdTime` int(10) unsigned NOT NULL COMMENT '章节创建时间',
  `copyId` int(10) NOT NULL DEFAULT '0' COMMENT '复制章节的id',
  `syncId` int(11) NOT NULL DEFAULT '0' COMMENT '租赁资源ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `course_draft`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_draft` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL COMMENT '标题',
  `summary` text COMMENT '摘要',
  `courseId` int(10) unsigned NOT NULL COMMENT '课程ID',
  `content` text COMMENT '内容',
  `userId` int(10) unsigned NOT NULL COMMENT '用户ID',
  `activityId` int(10) unsigned NOT NULL COMMENT '教学活动ID',
  `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `course_favorite`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_favorite` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '收藏ID',
  `courseId` int(10) unsigned NOT NULL COMMENT '教学计划ID',
  `userId` int(10) unsigned NOT NULL COMMENT '收藏人的ID',
  `createdTime` int(10) NOT NULL COMMENT '创建时间',
  `type` varchar(50) NOT NULL DEFAULT 'course' COMMENT '课程类型',
  `courseSetId` int(10) NOT NULL DEFAULT '0' COMMENT '课程ID',
  PRIMARY KEY (`id`),
  KEY `course_favorite_userId_courseId_type_index` (`userId`,`courseId`,`type`),
  KEY `course_favorite_userId_courseSetId_type_index` (`userId`,`courseSetId`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户的收藏数据表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `course_job`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_job` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `courseId` int(10) unsigned NOT NULL COMMENT '计划Id',
  `type` varchar(32) NOT NULL DEFAULT '' COMMENT '任务类型',
  `data` text COMMENT '任务参数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='课程定时任务表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `course_lesson`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_lesson` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '课时ID',
  `courseId` int(10) unsigned NOT NULL COMMENT '课时所属课程ID',
  `chapterId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课时所属章节ID',
  `number` int(10) unsigned NOT NULL COMMENT '课时编号',
  `seq` int(10) unsigned NOT NULL COMMENT '课时在课程中的序号',
  `free` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否为免费课时',
  `status` enum('unpublished','published') NOT NULL DEFAULT 'published' COMMENT '课时状态',
  `title` varchar(255) NOT NULL COMMENT '课时标题',
  `summary` text COMMENT '课时摘要',
  `tags` text COMMENT '课时标签',
  `type` varchar(64) NOT NULL DEFAULT 'text' COMMENT '课时类型',
  `content` text COMMENT '课时正文',
  `giveCredit` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学完课时获得的学分',
  `requireCredit` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学习课时前，需达到的学分',
  `mediaId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '媒体文件ID',
  `mediaSource` varchar(32) NOT NULL DEFAULT '' COMMENT '媒体文件来源(self:本站上传,youku:优酷)',
  `mediaName` varchar(255) NOT NULL DEFAULT '' COMMENT '媒体文件名称',
  `mediaUri` text COMMENT '媒体文件资源名',
  `homeworkId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '作业iD',
  `exerciseId` int(10) unsigned DEFAULT '0' COMMENT '练习ID',
  `length` int(11) unsigned DEFAULT NULL COMMENT '时长',
  `materialNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上传的资料数量',
  `quizNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '测验题目数量',
  `learnedNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '已学的学员数',
  `viewedNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '查看数',
  `startTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '直播课时开始时间',
  `endTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '直播课时结束时间',
  `memberNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '直播课时加入人数',
  `replayStatus` enum('ungenerated','generating','generated','videoGenerated') NOT NULL DEFAULT 'ungenerated',
  `maxOnlineNum` int(11) DEFAULT '0' COMMENT '直播在线人数峰值',
  `liveProvider` int(10) unsigned NOT NULL DEFAULT '0',
  `userId` int(10) unsigned NOT NULL COMMENT '发布人ID',
  `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  `copyId` int(10) NOT NULL DEFAULT '0' COMMENT '复制课时id',
  `testMode` enum('normal','realTime') DEFAULT 'normal' COMMENT '考试模式',
  `testStartTime` int(10) DEFAULT '0' COMMENT '实时考试开始时间',
  PRIMARY KEY (`id`),
  KEY `updatedTime` (`updatedTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `course_lesson_extend`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_lesson_extend` (
  `id` int(10) NOT NULL COMMENT '课时ID',
  `courseId` int(10) NOT NULL DEFAULT '0' COMMENT '课程ID',
  `doTimes` int(10) NOT NULL DEFAULT '0' COMMENT '可考试次数',
  `redoInterval` float(10,1) NOT NULL DEFAULT '0.0' COMMENT '重做时间间隔(小时)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='课时扩展表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `course_lesson_learn`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_lesson_learn` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '学员课时学习记录ID',
  `userId` int(10) unsigned NOT NULL COMMENT '学员ID',
  `courseId` int(10) unsigned NOT NULL COMMENT '课程ID',
  `lessonId` int(10) unsigned NOT NULL COMMENT '课时ID',
  `status` enum('learning','finished') NOT NULL COMMENT '学习状态',
  `startTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学习开始时间',
  `finishedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学习完成时间',
  `learnTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学习时间',
  `watchTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学习观看时间',
  `watchNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '观看次数',
  `videoStatus` enum('paused','playing') NOT NULL DEFAULT 'paused' COMMENT '学习观看时间',
  `updateTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId_lessonId` (`userId`,`lessonId`),
  KEY `userId_courseId` (`userId`,`courseId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `course_lesson_replay`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_lesson_replay` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `lessonId` int(10) unsigned NOT NULL COMMENT '所属课时',
  `courseId` int(10) unsigned NOT NULL COMMENT '所属课程',
  `title` varchar(255) NOT NULL COMMENT '名称',
  `replayId` text NOT NULL COMMENT '云直播中的回放id',
  `globalId` char(32) NOT NULL DEFAULT '' COMMENT '云资源ID',
  `userId` int(10) unsigned NOT NULL COMMENT '创建者',
  `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  `hidden` tinyint(1) unsigned DEFAULT '0' COMMENT '观看状态',
  `type` varchar(50) NOT NULL DEFAULT 'live' COMMENT '课程类型',
  `copyId` int(10) DEFAULT '0' COMMENT '复制回放的ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `course_lesson_view`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_lesson_view` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `courseId` int(10) NOT NULL,
  `lessonId` int(10) NOT NULL,
  `fileId` int(10) NOT NULL,
  `userId` int(10) NOT NULL,
  `fileType` enum('document','video','audio','image','ppt','other','none') NOT NULL DEFAULT 'none',
  `fileStorage` enum('local','cloud','net','none') NOT NULL DEFAULT 'none',
  `fileSource` varchar(32) NOT NULL,
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `course_material`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_material` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '课程资料ID',
  `courseId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '资料所属课程ID',
  `lessonId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '资料所属课时ID',
  `title` varchar(1024) NOT NULL COMMENT '资料标题',
  `description` text COMMENT '资料描述',
  `link` varchar(1024) NOT NULL DEFAULT '' COMMENT '外部链接地址',
  `fileId` int(10) unsigned NOT NULL COMMENT '资料文件ID',
  `fileUri` varchar(255) NOT NULL DEFAULT '' COMMENT '资料文件URI',
  `fileMime` varchar(255) NOT NULL DEFAULT '' COMMENT '资料文件MIME',
  `fileSize` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '资料文件大小',
  `source` varchar(50) NOT NULL DEFAULT 'coursematerial',
  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '资料创建人ID',
  `createdTime` int(10) unsigned NOT NULL COMMENT '资料创建时间',
  `copyId` int(10) NOT NULL DEFAULT '0' COMMENT '复制的资料Id',
  `type` varchar(50) NOT NULL DEFAULT 'course' COMMENT '课程类型',
  `courseSetId` int(10) DEFAULT '0' COMMENT '课程ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `course_material_v8`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_material_v8` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '课程资料ID',
  `courseId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '资料所属课程ID',
  `lessonId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '资料所属课时ID',
  `title` varchar(1024) NOT NULL COMMENT '资料标题',
  `description` text COMMENT '资料描述',
  `link` varchar(1024) NOT NULL DEFAULT '' COMMENT '外部链接地址',
  `fileId` int(10) unsigned NOT NULL COMMENT '资料文件ID',
  `fileUri` varchar(255) NOT NULL DEFAULT '' COMMENT '资料文件URI',
  `fileMime` varchar(255) NOT NULL DEFAULT '' COMMENT '资料文件MIME',
  `fileSize` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '资料文件大小',
  `source` varchar(50) NOT NULL DEFAULT 'coursematerial',
  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '资料创建人ID',
  `createdTime` int(10) unsigned NOT NULL COMMENT '资料创建时间',
  `copyId` int(10) NOT NULL DEFAULT '0' COMMENT '复制的资料Id',
  `type` varchar(50) NOT NULL DEFAULT 'course' COMMENT '课程类型',
  `courseSetId` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `course_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_member` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '课程学员记录ID',
  `courseId` int(10) unsigned NOT NULL COMMENT '教学计划ID',
  `classroomId` int(10) NOT NULL DEFAULT '0' COMMENT '班级ID',
  `joinedType` enum('course','classroom') NOT NULL DEFAULT 'course' COMMENT '购买班级或者课程加入学习',
  `userId` int(10) unsigned NOT NULL COMMENT '学员ID',
  `orderId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学员购买课程时的订单ID',
  `deadline` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学习最后期限',
  `refundDeadline` int(10) NOT NULL DEFAULT '0' COMMENT '退款截止时间',
  `levelId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户以会员的方式加入课程学员时的会员ID',
  `learnedNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '已学课时数',
  `learnedCompulsoryTaskNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '已学习的必修任务数量',
  `credit` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学员已获得的学分',
  `noteNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '笔记数目',
  `noteLastUpdateTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最新的笔记更新时间',
  `isLearned` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否已学完',
  `finishedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '完成课程时间',
  `seq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序序号',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `isVisible` tinyint(2) NOT NULL DEFAULT '1' COMMENT '可见与否，默认为可见',
  `role` enum('student','teacher') NOT NULL DEFAULT 'student' COMMENT '课程会员角色',
  `locked` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '学员是否被锁定',
  `deadlineNotified` int(10) NOT NULL DEFAULT '0' COMMENT '有效期通知',
  `createdTime` int(10) unsigned NOT NULL COMMENT '学员加入课程时间',
  `lastLearnTime` int(10) DEFAULT NULL COMMENT '最后学习时间',
  `updatedTime` int(10) NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  `lastViewTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后查看时间',
  `courseSetId` int(10) unsigned NOT NULL COMMENT '课程ID',
  PRIMARY KEY (`id`),
  UNIQUE KEY `courseId` (`courseId`,`userId`),
  KEY `courseId_role_createdTime` (`courseId`,`role`,`createdTime`),
  KEY `index_role_userId` (`role`,`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `course_note`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_note` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '笔记ID',
  `userId` int(10) NOT NULL COMMENT '笔记作者ID',
  `courseId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课程ID',
  `taskId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务ID',
  `content` text NOT NULL COMMENT '笔记内容',
  `length` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '笔记内容的字数',
  `likeNum` int(6) unsigned NOT NULL DEFAULT '0' COMMENT '点赞人数',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '笔记状态：0:私有, 1:公开',
  `createdTime` int(10) NOT NULL COMMENT '笔记创建时间',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '笔记更新时间',
  `courseSetId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `coursesetid_status` (`courseSetId`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `course_note_like`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_note_like` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `noteId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `createdTime` int(11) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `course_review`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_review` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '课程评价ID',
  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评价人ID',
  `courseId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '被评价的课程ID',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '评价标题',
  `content` text NOT NULL COMMENT '评论内容',
  `rating` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评分',
  `private` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否隐藏',
  `createdTime` int(10) unsigned NOT NULL COMMENT '评价创建时间',
  `parentId` int(10) NOT NULL DEFAULT '0' COMMENT '回复ID',
  `updatedTime` int(10) DEFAULT NULL,
  `meta` text COMMENT '评价元信息',
  `courseSetId` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `course_set_v8`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_set_v8` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(32) NOT NULL DEFAULT '',
  `title` varchar(1024) DEFAULT '',
  `subtitle` varchar(1024) DEFAULT '',
  `tags` text,
  `categoryId` int(10) NOT NULL DEFAULT '0',
  `serializeMode` varchar(32) NOT NULL DEFAULT 'none' COMMENT 'none, serilized, finished',
  `status` varchar(32) DEFAULT '0' COMMENT 'draft, published, closed',
  `summary` text,
  `goals` text,
  `audiences` text,
  `cover` varchar(1024) DEFAULT NULL,
  `ratingNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课程评论数',
  `rating` float unsigned NOT NULL DEFAULT '0' COMMENT '课程评分',
  `noteNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课程笔记数',
  `studentNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课程学员数',
  `hotSeq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最热排序',
  `recommended` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否为推荐课程',
  `recommendedSeq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐序号',
  `recommendedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐时间',
  `orgId` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '组织机构ID',
  `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构内部编码',
  `discountId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '折扣活动ID',
  `discount` float(10,2) NOT NULL DEFAULT '10.00' COMMENT '折扣',
  `hitNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课程点击数',
  `maxRate` tinyint(3) unsigned NOT NULL DEFAULT '100' COMMENT '最大抵扣百分比',
  `materialNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上传的资料数量',
  `parentId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '是否班级课程',
  `locked` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否锁住',
  `maxCoursePrice` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '已发布教学计划的最高价格',
  `minCoursePrice` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '已发布教学计划的最低价格',
  `teacherIds` varchar(1024) DEFAULT NULL,
  `creator` int(11) DEFAULT '0',
  `createdTime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatedTime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `defaultCourseId` int(11) unsigned DEFAULT '0' COMMENT '默认的计划ID',
  `conditionalAccess` int(11) NOT NULL DEFAULT '0' COMMENT '是否开启加入条件',
  `showable` int(11) NOT NULL DEFAULT '1' COMMENT '是否开启发布范围',
  `resourceCode` varchar(255) DEFAULT NULL COMMENT '资源编码',
  `deadline` int(11) NOT NULL DEFAULT '0' COMMENT '租赁课程有效期',
  `belong` varchar(255) NOT NULL DEFAULT 'self' COMMENT '课程所属',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `course_task`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_task` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `courseId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属课程的id',
  `seq` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '序号',
  `categoryId` int(10) DEFAULT NULL,
  `activityId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '引用的教学活动',
  `title` varchar(255) NOT NULL COMMENT '标题',
  `isFree` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '是否免费',
  `isOptional` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '是否必修',
  `startTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
  `endTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
  `mode` varchar(60) DEFAULT NULL COMMENT '任务模式',
  `status` varchar(255) NOT NULL DEFAULT 'create' COMMENT '发布状态 create|publish|unpublish',
  `number` varchar(32) NOT NULL DEFAULT '' COMMENT '任务编号',
  `type` varchar(50) NOT NULL COMMENT '任务类型',
  `mediaSource` varchar(32) NOT NULL DEFAULT '' COMMENT '媒体文件来源(self:本站上传,youku:优酷)',
  `maxOnlineNum` int(11) unsigned DEFAULT '0' COMMENT '任务最大可同时进行的人数，0为不限制',
  `fromCourseSetId` int(10) unsigned NOT NULL DEFAULT '0',
  `length` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '若是视频类型，则表示时长；若是ppt，则表示页数；由具体的活动业务来定义',
  `copyId` int(10) NOT NULL DEFAULT '0' COMMENT '复制来源task的id',
  `createdUserId` int(10) unsigned NOT NULL COMMENT '创建者',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  `syncId` int(11) NOT NULL DEFAULT '0' COMMENT '租赁资源ID',
  PRIMARY KEY (`id`),
  KEY `seq` (`seq`),
  KEY `course_task_courseId_status_index` (`courseId`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `course_task_result`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_task_result` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `activityId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '活动的id',
  `courseId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属课程的id',
  `courseTaskId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课程的任务id',
  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `status` varchar(255) NOT NULL DEFAULT 'start' COMMENT '任务状态，start，finish',
  `lastLearnTime` int(10) DEFAULT '0' COMMENT '最后学习时间',
  `finishedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '完成时间',
  `time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务进行时长（分钟）',
  `watchTime` int(10) unsigned NOT NULL DEFAULT '0',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_task_result` (`userId`,`courseTaskId`),
  KEY `course_task_result_courseId_userId_index` (`courseId`,`userId`),
  KEY `course_task_result_courseTaskId_userId_index` (`courseTaskId`,`userId`),
  KEY `taskid_userid` (`userId`,`courseTaskId`),
  KEY `idx_userId_courseId` (`userId`,`courseId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `course_task_try_view`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_task_try_view` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) NOT NULL,
  `courseSetId` int(10) NOT NULL,
  `courseId` int(10) NOT NULL,
  `taskId` int(10) NOT NULL,
  `taskType` varchar(50) NOT NULL DEFAULT '' COMMENT 'task.type',
  `createdTime` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `course_task_view`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_task_view` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `courseSetId` int(10) NOT NULL,
  `courseId` int(10) NOT NULL,
  `taskId` int(10) NOT NULL,
  `fileId` int(10) NOT NULL,
  `userId` int(10) NOT NULL,
  `fileType` varchar(80) NOT NULL,
  `fileStorage` varchar(80) NOT NULL,
  `fileSource` varchar(32) NOT NULL,
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `course_thread`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_thread` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '课程话题ID',
  `courseId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '话题所属课程ID',
  `taskId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务ID',
  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '话题发布人ID',
  `type` enum('discussion','question') NOT NULL DEFAULT 'discussion' COMMENT '话题类型',
  `isStick` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否置顶',
  `isElite` tinyint(10) unsigned NOT NULL DEFAULT '0' COMMENT '是否精华',
  `isClosed` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '是否关闭',
  `private` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否隐藏',
  `title` varchar(255) NOT NULL COMMENT '话题标题',
  `content` text COMMENT '话题内容',
  `postNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '回复数',
  `hitNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '查看数',
  `followNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关注数',
  `latestPostUserId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后回复人ID',
  `latestPostTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后回复时间',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '话题创建时间',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  `courseSetId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `updatedTime` (`updatedTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `course_thread_post`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_thread_post` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '课程话题回复ID',
  `courseId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '回复所属课程ID',
  `taskId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务ID',
  `threadId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '回复所属话题ID',
  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '回复人',
  `isElite` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否精华',
  `content` text NOT NULL COMMENT '正文',
  `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `course_v8`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_v8` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `courseSetId` int(11) NOT NULL,
  `title` varchar(1024) DEFAULT NULL,
  `learnMode` varchar(32) DEFAULT NULL COMMENT 'lockMode, freeMode',
  `expiryMode` varchar(32) DEFAULT NULL COMMENT 'days, date',
  `expiryDays` int(11) DEFAULT NULL,
  `expiryStartDate` int(11) DEFAULT NULL,
  `expiryEndDate` int(11) DEFAULT NULL,
  `summary` text,
  `goals` text,
  `audiences` text,
  `isDefault` tinyint(1) DEFAULT '0',
  `maxStudentNum` int(11) DEFAULT '0',
  `status` varchar(32) DEFAULT NULL COMMENT 'draft, published, closed',
  `isFree` tinyint(1) DEFAULT '1',
  `price` float(10,2) DEFAULT '0.00',
  `vipLevelId` int(11) DEFAULT '0',
  `buyable` tinyint(1) DEFAULT '1',
  `tryLookable` tinyint(1) DEFAULT '0',
  `tryLookLength` int(11) DEFAULT '0',
  `watchLimit` int(11) DEFAULT '0',
  `services` text,
  `taskNum` int(10) DEFAULT '0' COMMENT '任务数',
  `studentNum` int(10) DEFAULT '0' COMMENT '学员数',
  `teacherIds` varchar(1024) DEFAULT '0' COMMENT '可见教师ID列表',
  `parentId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课程的父Id',
  `ratingNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课程计划评论数',
  `rating` float unsigned NOT NULL DEFAULT '0' COMMENT '课程计划评分',
  `noteNum` int(10) unsigned NOT NULL DEFAULT '0',
  `threadNum` int(10) DEFAULT '0' COMMENT '话题数',
  `type` varchar(32) NOT NULL DEFAULT 'normal' COMMENT '教学计划类型',
  `approval` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否需要实名才能购买',
  `income` float(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '总收入',
  `originPrice` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '课程人民币原价',
  `coinPrice` float(10,2) NOT NULL DEFAULT '0.00',
  `originCoinPrice` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '课程虚拟币原价',
  `showStudentNumType` enum('opened','closed') NOT NULL DEFAULT 'opened' COMMENT '学员数显示模式',
  `serializeMode` varchar(32) NOT NULL DEFAULT 'none' COMMENT '连载模式',
  `giveCredit` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学完课程所有课时，可获得的总学分',
  `about` text COMMENT '简介',
  `locationId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上课地区ID',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '上课地区地址',
  `deadlineNotify` enum('active','none') NOT NULL DEFAULT 'none' COMMENT '开启有效期通知',
  `daysOfNotifyBeforeDeadline` int(10) NOT NULL DEFAULT '0',
  `useInClassroom` enum('single','more') NOT NULL DEFAULT 'single' COMMENT '课程能否用于多个班级',
  `singleBuy` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '加入班级后课程能否单独购买',
  `freeStartTime` int(10) NOT NULL DEFAULT '0',
  `freeEndTime` int(10) NOT NULL DEFAULT '0',
  `locked` int(10) NOT NULL DEFAULT '0' COMMENT '是否上锁1上锁,0解锁',
  `cover` varchar(1024) DEFAULT NULL,
  `buyExpiryTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '购买开放有效期',
  `enableFinish` int(1) NOT NULL DEFAULT '1' COMMENT '是否允许学院强制完成任务',
  `maxRate` tinyint(3) DEFAULT '0' COMMENT '最大抵扣百分比',
  `materialNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上传的资料数量',
  `compulsoryTaskNum` int(10) DEFAULT '0' COMMENT '必修任务数',
  `createdTime` int(10) unsigned NOT NULL COMMENT '课程创建时间',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  `creator` int(11) DEFAULT NULL,
  `recommended` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否为推荐课程',
  `recommendedSeq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐序号',
  `recommendedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐时间',
  `categoryId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分类',
  `showServices` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否在营销页展示服务承诺',
  `hitNum` int(10) NOT NULL DEFAULT '0' COMMENT '点击量',
  `courseType` varchar(32) DEFAULT 'default' COMMENT 'default, normal, times,...',
  `enableAudio` int(1) NOT NULL DEFAULT '0',
  `rewardPoint` int(10) NOT NULL DEFAULT '0' COMMENT '课程积分',
  `taskRewardPoint` int(10) NOT NULL DEFAULT '0' COMMENT '任务积分',
  `syncId` int(11) NOT NULL DEFAULT '0' COMMENT '租赁资源ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `crontab_job`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `crontab_job` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
  `name` varchar(1024) NOT NULL COMMENT '任务名称',
  `cycle` enum('once','everyhour','everyday','everymonth') NOT NULL DEFAULT 'once' COMMENT '任务执行周期',
  `cycleTime` varchar(255) NOT NULL DEFAULT '0' COMMENT '任务执行时间',
  `jobClass` varchar(1024) NOT NULL COMMENT '任务的Class名称',
  `jobParams` text COMMENT '任务参数',
  `targetType` varchar(64) NOT NULL DEFAULT '',
  `targetId` int(10) unsigned NOT NULL DEFAULT '0',
  `executing` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '任务执行状态',
  `nextExcutedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务下次执行的时间',
  `latestExecutedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务最后执行的时间',
  `creatorId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务创建人',
  `createdTime` int(10) unsigned NOT NULL COMMENT '任务创建时间',
  `enabled` tinyint(1) DEFAULT '1' COMMENT '是否启用',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `current_learning_task_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `current_learning_task_record` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户Id',
  `taskId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '任务Id',
  `startTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
  `lastTriggerTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最近一次上报时间',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `dictionary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dictionary` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '字典名称',
  `type` varchar(255) NOT NULL COMMENT '字典类型',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `dictionary_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dictionary_item` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL COMMENT '字典类型',
  `code` varchar(64) DEFAULT NULL COMMENT '编码',
  `name` varchar(255) NOT NULL COMMENT '字典内容名称',
  `weight` int(11) NOT NULL DEFAULT '0' COMMENT '权重',
  `createdTime` int(10) unsigned NOT NULL,
  `updateTime` int(10) unsigned DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `dingtalk_notification_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dingtalk_notification_record` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `agentId` varchar(255) NOT NULL DEFAULT '' COMMENT 'h5应用Id',
  `taskId` varchar(255) NOT NULL DEFAULT '' COMMENT '钉钉任务id',
  `targetType` varchar(225) NOT NULL DEFAULT '' COMMENT '发送场景的资源类型',
  `targetId` int(10) NOT NULL DEFAULT '0' COMMENT '发送场景的资源id',
  `batch` varchar(50) NOT NULL DEFAULT '' COMMENT '单个场景的发送批次',
  `data` text NOT NULL COMMENT '发送数据',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='钉钉消息发送记录';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `dingtalk_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dingtalk_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `unionid` varchar(255) NOT NULL DEFAULT '' COMMENT 'unionid',
  `userid` varchar(255) NOT NULL DEFAULT '' COMMENT 'userid',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `type` (`unionid`,`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='钉钉用户列表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `discovery_column`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `discovery_column` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `type` varchar(32) NOT NULL COMMENT '栏目类型',
  `categoryId` int(10) NOT NULL DEFAULT '0' COMMENT '分类',
  `orderType` varchar(32) NOT NULL COMMENT '排序字段',
  `showCount` int(10) NOT NULL COMMENT '展示数量',
  `seq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `isDisplay` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否展示',
  `createdTime` int(10) unsigned NOT NULL,
  `updateTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='发现页栏目';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `distributor_job_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `distributor_job_data` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `data` text NOT NULL COMMENT '数据',
  `jobType` varchar(128) NOT NULL COMMENT '使用的同步类型, 如order为 biz[distributor.sync.order] = BizDistributorServiceImplSyncOrderServiceImpl',
  `status` varchar(32) NOT NULL DEFAULT 'pending' COMMENT '分为 pending -- 可以发, finished -- 已发送, error -- 错误， 只有 pending 和 error 才会尝试发送',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `download_file_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `download_file_record` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `downloadActivityId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '资料所属活动ID',
  `materialId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '资料文件ID',
  `fileId` varchar(1024) DEFAULT '' COMMENT '文件ID',
  `link` varchar(1024) DEFAULT '' COMMENT '链接地址',
  `createdTime` int(10) unsigned NOT NULL COMMENT '下载时间',
  `userId` int(10) unsigned NOT NULL COMMENT '下载用户ID',
  PRIMARY KEY (`id`),
  KEY `createdTime` (`createdTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `file`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `file` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '上传文件ID',
  `groupId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上传文件组ID',
  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上传人ID',
  `uri` varchar(255) NOT NULL COMMENT '文件URI',
  `mime` varchar(255) NOT NULL COMMENT '文件MIME',
  `size` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文件大小',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '文件状态',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文件上传时间',
  `uploadFileId` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `uri` (`uri`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `file_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `file_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '上传文件组ID',
  `name` varchar(255) NOT NULL COMMENT '上传文件组名称',
  `code` varchar(255) NOT NULL COMMENT '上传文件组编码',
  `public` tinyint(4) NOT NULL DEFAULT '1' COMMENT '文件组文件是否公开',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `file_used`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `file_used` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(32) NOT NULL,
  `fileId` int(11) NOT NULL COMMENT 'upload_files id',
  `targetType` varchar(32) NOT NULL,
  `targetId` int(11) NOT NULL,
  `createdTime` int(11) NOT NULL,
  `syncId` int(11) NOT NULL DEFAULT '0' COMMENT '租赁资源ID',
  PRIMARY KEY (`id`),
  KEY `file_used_type_targetType_targetId_index` (`type`,`targetType`,`targetId`),
  KEY `file_used_type_targetType_targetId_fileId_index` (`type`,`targetType`,`targetId`,`fileId`),
  KEY `file_used_fileId_index` (`fileId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `friend`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `friend` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '关注ID',
  `fromId` int(10) unsigned NOT NULL COMMENT '关注人ID',
  `toId` int(10) unsigned NOT NULL COMMENT '被关注人ID',
  `pair` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否为互加好友',
  `createdTime` int(10) unsigned NOT NULL COMMENT '关注时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '小组id',
  `title` varchar(100) NOT NULL COMMENT '小组名称',
  `about` text COMMENT '小组介绍',
  `logo` varchar(100) NOT NULL DEFAULT '' COMMENT 'logo',
  `backgroundLogo` varchar(100) NOT NULL DEFAULT '',
  `status` enum('open','close') NOT NULL DEFAULT 'open',
  `memberNum` int(10) unsigned NOT NULL DEFAULT '0',
  `threadNum` int(10) unsigned NOT NULL DEFAULT '0',
  `postNum` int(10) unsigned NOT NULL DEFAULT '0',
  `ownerId` int(10) unsigned NOT NULL COMMENT '小组组长id',
  `createdTime` int(11) unsigned NOT NULL COMMENT '创建小组时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `groups_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groups_member` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '成员id主键',
  `groupId` int(10) unsigned NOT NULL COMMENT '小组id',
  `userId` int(10) unsigned NOT NULL COMMENT '用户id',
  `role` varchar(100) NOT NULL DEFAULT 'member',
  `postNum` int(10) unsigned NOT NULL DEFAULT '0',
  `threadNum` int(10) unsigned NOT NULL DEFAULT '0',
  `createdTime` int(11) unsigned NOT NULL COMMENT '加入时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `groups_thread`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groups_thread` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '话题id',
  `title` varchar(1024) NOT NULL COMMENT '话题标题',
  `content` text COMMENT '话题内容',
  `isElite` int(11) unsigned NOT NULL DEFAULT '0',
  `isStick` int(11) unsigned NOT NULL DEFAULT '0',
  `lastPostMemberId` int(10) unsigned NOT NULL DEFAULT '0',
  `lastPostTime` int(10) unsigned NOT NULL DEFAULT '0',
  `groupId` int(10) unsigned NOT NULL,
  `userId` int(10) unsigned NOT NULL,
  `createdTime` int(10) unsigned NOT NULL COMMENT '添加时间',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  `postNum` int(10) unsigned NOT NULL DEFAULT '0',
  `status` enum('open','close') NOT NULL DEFAULT 'open',
  `hitNum` int(10) unsigned NOT NULL DEFAULT '0',
  `rewardCoin` int(10) unsigned NOT NULL DEFAULT '0',
  `type` varchar(255) NOT NULL DEFAULT 'default',
  PRIMARY KEY (`id`),
  KEY `updatedTime` (`updatedTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `groups_thread_collect`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groups_thread_collect` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id主键',
  `threadId` int(11) unsigned NOT NULL COMMENT '收藏的话题id',
  `userId` int(10) unsigned NOT NULL COMMENT '收藏人id',
  `createdTime` int(10) unsigned NOT NULL COMMENT '收藏时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `groups_thread_goods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groups_thread_goods` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  `description` text,
  `userId` int(10) unsigned NOT NULL DEFAULT '0',
  `type` enum('content','attachment','postAttachment') NOT NULL,
  `threadId` int(10) unsigned NOT NULL,
  `postId` int(10) unsigned NOT NULL DEFAULT '0',
  `coin` int(10) unsigned NOT NULL,
  `fileId` int(10) unsigned NOT NULL DEFAULT '0',
  `hitNum` int(10) unsigned NOT NULL DEFAULT '0',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `groups_thread_post`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groups_thread_post` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id主键',
  `threadId` int(11) unsigned NOT NULL COMMENT '话题id',
  `content` text NOT NULL COMMENT '回复内容',
  `userId` int(10) unsigned NOT NULL COMMENT '回复人id',
  `fromUserId` int(10) unsigned NOT NULL DEFAULT '0',
  `postId` int(10) unsigned DEFAULT '0',
  `createdTime` int(10) unsigned NOT NULL COMMENT '回复时间',
  `adopt` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `threadid_postid_createdtime` (`threadId`,`postId`,`createdTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `groups_thread_trade`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groups_thread_trade` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `threadId` int(10) unsigned DEFAULT '0',
  `goodsId` int(10) DEFAULT '0',
  `userId` int(10) unsigned NOT NULL,
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `im_conversation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `im_conversation` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `no` varchar(64) NOT NULL COMMENT 'IM云端返回的会话id',
  `targetType` varchar(16) NOT NULL DEFAULT '',
  `targetId` int(10) unsigned NOT NULL DEFAULT '0',
  `memberIds` text NOT NULL COMMENT '会话中用户列表(用户id按照小到大排序，竖线隔开)',
  `memberHash` varchar(32) NOT NULL DEFAULT '' COMMENT 'memberIds字段的hash值，用于优化查询',
  `createdTime` int(10) unsigned NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `no` (`no`),
  KEY `targetId` (`targetId`),
  KEY `targetType` (`targetType`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='IM云端会话记录表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `im_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `im_member` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `convNo` varchar(32) NOT NULL COMMENT '会话ID',
  `targetId` int(10) NOT NULL,
  `targetType` varchar(15) NOT NULL,
  `userId` int(10) NOT NULL DEFAULT '0',
  `createdTime` int(10) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `convno_userId` (`convNo`,`userId`),
  KEY `userId_targetType` (`userId`,`targetType`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='会话用户表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `invite_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invite_record` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `inviteUserId` int(11) unsigned DEFAULT NULL COMMENT '邀请者',
  `invitedUserId` int(11) unsigned DEFAULT NULL COMMENT '被邀请者',
  `inviteTime` int(11) unsigned DEFAULT NULL COMMENT '邀请时间',
  `inviteUserCardId` int(11) unsigned DEFAULT NULL COMMENT '邀请者获得奖励的卡的ID',
  `invitedUserCardId` int(11) unsigned DEFAULT NULL COMMENT '被邀请者获得奖励的卡的ID',
  `amount` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '被邀请者被邀请后的消费总额',
  `cashAmount` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '被邀请者被邀请后的现金消费总额',
  `coinAmount` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '被邀请者被邀请后的虚拟币消费总额',
  PRIMARY KEY (`id`),
  KEY `idx_inviteUserId` (`inviteUserId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='邀请记录表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ip_blacklist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ip_blacklist` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(32) NOT NULL,
  `type` enum('failed','banned') NOT NULL COMMENT '禁用类型',
  `counter` int(10) unsigned NOT NULL DEFAULT '0',
  `expiredTime` int(10) unsigned NOT NULL DEFAULT '0',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `keyword`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `keyword` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `state` enum('replaced','banned') NOT NULL DEFAULT 'replaced',
  `bannedNum` int(10) unsigned NOT NULL DEFAULT '0',
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `keyword_banlog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `keyword_banlog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `keywordId` int(10) unsigned NOT NULL,
  `keywordName` varchar(64) NOT NULL DEFAULT '',
  `state` enum('replaced','banned') NOT NULL DEFAULT 'replaced',
  `text` text NOT NULL,
  `userId` int(10) unsigned NOT NULL DEFAULT '0',
  `ip` varchar(64) NOT NULL DEFAULT '',
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `keywordId` (`keywordId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `location`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `location` (
  `id` bigint(20) unsigned NOT NULL,
  `parentId` bigint(20) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `pinyin` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '系统日志ID',
  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '操作人ID',
  `module` varchar(32) NOT NULL COMMENT '日志所属模块',
  `action` varchar(255) NOT NULL COMMENT '日志所属操作类型',
  `message` text NOT NULL COMMENT '日志内容',
  `data` text COMMENT '日志数据',
  `ip` varchar(255) NOT NULL COMMENT '日志记录IP',
  `createdTime` int(10) unsigned NOT NULL COMMENT '日志发生时间',
  `level` char(10) NOT NULL COMMENT '日志等级',
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `manage_permission_org`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `manage_permission_org` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) NOT NULL COMMENT '被授权用户id',
  `orgId` int(10) NOT NULL COMMENT '授权部门id',
  `createdUserId` int(10) NOT NULL COMMENT '授权人',
  `createdTime` int(10) NOT NULL COMMENT '授权时间',
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户管理范围部门';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `marker`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `marker` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `second` int(10) unsigned NOT NULL COMMENT '驻点时间',
  `mediaId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '媒体文件ID',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='驻点';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `member_operation_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_operation_record` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(1024) NOT NULL DEFAULT '' COMMENT '标题',
  `member_id` int(10) unsigned NOT NULL COMMENT '成员ID',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户Id',
  `member_type` varchar(32) NOT NULL DEFAULT 'student' COMMENT '成员身份',
  `target_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '类型ID',
  `parent_id` int(10) NOT NULL DEFAULT '0' COMMENT '班级课程的被复制的计划Id',
  `course_set_id` int(10) NOT NULL DEFAULT '0' COMMENT '课程Id',
  `target_type` varchar(32) NOT NULL DEFAULT '' COMMENT '类型（classroom, course）',
  `operate_type` varchar(32) NOT NULL DEFAULT '' COMMENT '操作类型（join, exit）',
  `exit_course_set` tinyint(1) NOT NULL DEFAULT '0' COMMENT '退出的课程的最后教学计划，算退出课程',
  `join_course_set` tinyint(1) NOT NULL DEFAULT '0' COMMENT '加入的课程的第一个教学计划，算加入课程',
  `operate_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '操作时间',
  `operator_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '操作用户ID',
  `data` text COMMENT 'extra data',
  `order_id` int(11) NOT NULL DEFAULT '0' COMMENT '订单ID',
  `refund_id` int(11) NOT NULL DEFAULT '0' COMMENT '退款ID',
  `reason` varchar(256) NOT NULL DEFAULT '' COMMENT '加入理由或退出理由',
  `reason_type` varchar(255) NOT NULL DEFAULT '' COMMENT '用户退出或加入的类型：refund, remove, exit',
  `created_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '私信Id',
  `type` enum('text','image','video','audio') NOT NULL DEFAULT 'text' COMMENT '私信类型',
  `fromId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发信人Id',
  `toId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '收信人Id',
  `content` text NOT NULL COMMENT '私信内容',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '私信发送时间',
  `isDelete` int(1) NOT NULL DEFAULT '0' COMMENT '是否已删除',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `message_conversation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message_conversation` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '会话Id',
  `fromId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发信人Id',
  `toId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '收信人Id',
  `messageNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '此对话的信息条数',
  `latestMessageUserId` int(10) unsigned DEFAULT NULL COMMENT '最后发信人ID',
  `latestMessageTime` int(10) unsigned NOT NULL COMMENT '最后发信时间',
  `latestMessageContent` text NOT NULL COMMENT '最后发信内容',
  `latestMessageType` enum('text','image','video','audio') NOT NULL DEFAULT 'text' COMMENT '最后一条私信类型',
  `unreadNum` int(10) unsigned NOT NULL COMMENT '未读数量',
  `createdTime` int(10) unsigned NOT NULL COMMENT '会话创建时间',
  PRIMARY KEY (`id`),
  KEY `toId_fromId` (`toId`,`fromId`),
  KEY `toId_latestMessageTime` (`toId`,`latestMessageTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `message_relation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message_relation` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '消息关联ID',
  `conversationId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关联的会话ID',
  `messageId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关联的消息ID',
  `isRead` enum('0','1') NOT NULL DEFAULT '0' COMMENT '是否已读',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migration_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migration_versions` (
  `version` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `version` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mobile_device`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mobile_device` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '设备ID',
  `imei` varchar(255) NOT NULL COMMENT '串号',
  `platform` varchar(255) NOT NULL COMMENT '平台',
  `version` varchar(255) NOT NULL COMMENT '版本',
  `screenresolution` varchar(100) NOT NULL COMMENT '分辨率',
  `kernel` varchar(255) NOT NULL COMMENT '内核',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `money_card`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `money_card` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cardId` varchar(32) NOT NULL,
  `password` varchar(32) NOT NULL,
  `deadline` varchar(19) NOT NULL COMMENT '有效时间',
  `rechargeTime` int(10) NOT NULL DEFAULT '0' COMMENT '充值时间，0为未充值',
  `cardStatus` enum('normal','invalid','recharged','receive') NOT NULL DEFAULT 'invalid',
  `receiveTime` int(10) NOT NULL DEFAULT '0' COMMENT '领取学习卡时间',
  `rechargeUserId` int(11) NOT NULL DEFAULT '0',
  `batchId` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `money_card_batch`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `money_card_batch` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cardPrefix` varchar(32) NOT NULL,
  `cardLength` int(8) NOT NULL DEFAULT '0',
  `number` int(11) NOT NULL DEFAULT '0',
  `receivedNumber` int(11) NOT NULL DEFAULT '0',
  `rechargedNumber` int(11) NOT NULL DEFAULT '0',
  `token` varchar(64) NOT NULL DEFAULT '0',
  `deadline` varchar(19) CHARACTER SET latin1 NOT NULL,
  `money` int(8) NOT NULL DEFAULT '0',
  `batchStatus` enum('invalid','normal') NOT NULL DEFAULT 'normal',
  `coin` int(11) NOT NULL DEFAULT '0',
  `userId` int(11) NOT NULL DEFAULT '0',
  `createdTime` int(11) NOT NULL DEFAULT '0',
  `note` varchar(128) NOT NULL,
  `batchName` varchar(15) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `navigation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `navigation` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '导航ID',
  `name` varchar(255) NOT NULL COMMENT '导航名称',
  `url` varchar(300) NOT NULL COMMENT '链接地址',
  `sequence` tinyint(4) unsigned NOT NULL COMMENT '显示顺序',
  `parentId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父导航ID',
  `createdTime` int(11) NOT NULL COMMENT '创建时间',
  `updateTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  `type` varchar(30) NOT NULL COMMENT '类型',
  `isOpen` tinyint(2) NOT NULL DEFAULT '1' COMMENT '默认1，为开启',
  `isNewWin` tinyint(2) NOT NULL DEFAULT '1' COMMENT '默认为1,另开窗口',
  `orgId` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '组织机构ID',
  `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构内部编码',
  PRIMARY KEY (`id`),
  KEY `navigation_type_isOpen_orgId_index` (`type`,`isOpen`,`orgId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='导航数据表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `notification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notification` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '通知ID',
  `userId` int(10) unsigned NOT NULL COMMENT '被通知的用户ID',
  `type` varchar(64) NOT NULL DEFAULT 'default' COMMENT '通知类型',
  `content` text COMMENT '通知内容',
  `batchId` int(10) NOT NULL DEFAULT '0' COMMENT '群发通知表中的ID',
  `createdTime` int(10) unsigned NOT NULL COMMENT '通知时间',
  `isRead` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已读',
  PRIMARY KEY (`id`),
  KEY `userid_type` (`userId`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `offline_activity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `offline_activity` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `parentId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '活动父Id',
  `title` varchar(1024) DEFAULT NULL,
  `summary` text,
  `cover` varchar(1024) DEFAULT NULL,
  `categoryId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分类',
  `startTime` int(11) NOT NULL DEFAULT '0' COMMENT '活动开始时间',
  `endTime` int(11) NOT NULL DEFAULT '0' COMMENT '活动结束时间',
  `enrollmentEndDate` int(11) NOT NULL DEFAULT '0' COMMENT '报名结束时间',
  `enrollmentStartDate` int(11) NOT NULL DEFAULT '0' COMMENT '报名结束时间',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '上课地点',
  `maxStudentNum` int(11) DEFAULT '0',
  `studentNum` int(11) DEFAULT '0',
  `requireAudit` tinyint(1) DEFAULT '0',
  `status` varchar(32) DEFAULT NULL COMMENT 'draft, published, closed',
  `orgId` int(10) unsigned DEFAULT '0' COMMENT '组织机构ID',
  `orgCode` varchar(255) DEFAULT NULL COMMENT '组织机构内部编码',
  `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  `creator` int(11) DEFAULT NULL COMMENT '创建者',
  `conditionalAccess` int(11) NOT NULL DEFAULT '0' COMMENT '是否开启加入条件',
  `showable` int(11) NOT NULL DEFAULT '1' COMMENT '是否开启发布范围',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `offline_activity_enrollment_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `offline_activity_enrollment_record` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `offlineActivityId` int(10) unsigned NOT NULL COMMENT '活动ID',
  `userId` int(10) unsigned NOT NULL COMMENT '学员ID',
  `submittedTime` int(10) unsigned NOT NULL COMMENT '报名时间',
  `status` enum('none','approved','rejected','submitted') NOT NULL DEFAULT 'submitted' COMMENT '状态',
  `approvedTime` int(10) NOT NULL DEFAULT '0' COMMENT '审核通过时间',
  `rejectedReason` varchar(255) DEFAULT NULL COMMENT '拒绝原因',
  `updatedTime` int(10) NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `offline_activity_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `offline_activity_member` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `offlineActivityId` int(10) unsigned NOT NULL COMMENT '活动ID',
  `userId` int(10) unsigned NOT NULL COMMENT '学员ID',
  `attendedStatus` enum('none','attended','unattended') DEFAULT 'none' COMMENT '是否考勤, none表示未考勤',
  `score` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '分数',
  `joinStatus` enum('join','removed') NOT NULL DEFAULT 'join' COMMENT '加入状态',
  `joinType` enum('default','import') NOT NULL DEFAULT 'default' COMMENT '加入类型',
  `passedStatus` enum('none','passed','unpassed') NOT NULL DEFAULT 'none' COMMENT '通过状态，none表示没有结果',
  `evaluate` text COMMENT '评价',
  `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  `updatedTime` int(10) NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `offlineActivityId` (`offlineActivityId`,`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `offline_course`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `offline_course` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(1024) DEFAULT NULL,
  `summary` text,
  `cover` varchar(1024) DEFAULT NULL,
  `categoryId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分类',
  `projectPlanId` int(11) DEFAULT '0' COMMENT '项目ID',
  `teacherIds` varchar(1024) DEFAULT '0' COMMENT '教师ID',
  `taskNum` int(10) DEFAULT '0' COMMENT '任务数',
  `startTime` int(11) NOT NULL DEFAULT '0' COMMENT '开始时间',
  `endTime` int(11) NOT NULL DEFAULT '0' COMMENT '结束时间',
  `status` varchar(32) DEFAULT NULL COMMENT 'draft, published, closed',
  `creator` int(11) DEFAULT NULL COMMENT '创建者',
  `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='线下课程';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `offline_course_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `offline_course_member` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `offlineCourseId` int(10) unsigned NOT NULL COMMENT '线下课程ID',
  `userId` int(10) unsigned NOT NULL COMMENT '学员ID',
  `learnedNum` int(11) NOT NULL DEFAULT '0' COMMENT '以参加场次数',
  `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  `updatedTime` int(10) NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='线下课程学员';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `offline_course_task`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `offline_course_task` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `offlineCourseId` int(10) unsigned NOT NULL COMMENT '线下课程ID',
  `activityId` int(10) unsigned NOT NULL COMMENT 'activityID',
  `type` varchar(255) NOT NULL COMMENT '类型',
  `title` varchar(255) NOT NULL COMMENT '标题',
  `seq` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '序号',
  `place` varchar(255) NOT NULL DEFAULT '' COMMENT '上课地点',
  `hasHomework` tinyint(1) DEFAULT '0' COMMENT '是否有课后作业',
  `homeworkDeadline` int(10) NOT NULL DEFAULT '0' COMMENT '作业提交截止日期',
  `homeworkDemand` text COMMENT '作业要求',
  `creator` int(11) NOT NULL COMMENT '创建者',
  `orgId` int(11) NOT NULL DEFAULT '0' COMMENT '组织机构ID',
  `startTime` int(11) NOT NULL DEFAULT '0' COMMENT '开始时间',
  `endTime` int(11) NOT NULL DEFAULT '0' COMMENT '结束时间',
  `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  `updatedTime` int(10) NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='线下课程任务';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `offline_course_task_result`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `offline_course_task_result` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `offlineCourseId` int(10) unsigned NOT NULL COMMENT '线下课程ID',
  `userId` int(10) unsigned NOT NULL COMMENT '学员ID',
  `taskId` int(10) unsigned NOT NULL COMMENT '线下课程任务ID',
  `status` varchar(255) NOT NULL DEFAULT 'start' COMMENT '任务状态，start，finish',
  `attendStatus` varchar(255) NOT NULL DEFAULT 'unattended' COMMENT 'unattended:未签到, attended:已签到',
  `homeworkStatus` varchar(255) NOT NULL DEFAULT 'unsubmit' COMMENT 'unsubmit:未提交, submitted:已提交, passed:通过, unpassed:未通过',
  `finishedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '完成时间',
  `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  `updatedTime` int(10) NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `taskId` (`taskId`,`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='线下课程任务结果';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `offline_exam`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `offline_exam` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `projectPlanId` int(11) DEFAULT '0' COMMENT '项目ID',
  `title` varchar(1024) DEFAULT NULL,
  `summary` text,
  `place` varchar(255) NOT NULL DEFAULT '' COMMENT '考试地点',
  `status` varchar(32) DEFAULT NULL COMMENT 'draft, published, closed',
  `score` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '总分数',
  `passScore` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '通过分数',
  `creator` int(11) DEFAULT NULL COMMENT '创建者',
  `startTime` int(11) NOT NULL DEFAULT '0' COMMENT '开始时间',
  `endTime` int(11) NOT NULL DEFAULT '0' COMMENT '结束时间',
  `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='线下考试';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `offline_exam_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `offline_exam_member` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `offlineExamId` int(10) NOT NULL COMMENT '线下考试ID',
  `userId` int(10) NOT NULL COMMENT '线下考试用户ID',
  `score` float(10,1) NOT NULL DEFAULT '0.0' COMMENT '考试成绩',
  `status` varchar(32) NOT NULL DEFAULT 'none' COMMENT 'none:未批阅, passed:通过, unpassed:未通过',
  `operatorId` int(10) NOT NULL COMMENT '操作人ID',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='线下考试成员结果';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `open_course`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `open_course` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '课程ID',
  `title` varchar(1024) NOT NULL COMMENT '课程标题',
  `subtitle` varchar(1024) NOT NULL DEFAULT '' COMMENT '课程副标题',
  `status` enum('draft','published','closed') NOT NULL DEFAULT 'draft' COMMENT '课程状态',
  `type` varchar(255) NOT NULL DEFAULT 'normal' COMMENT '课程类型',
  `lessonNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课时数',
  `categoryId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分类ID',
  `tags` text COMMENT '标签IDs',
  `smallPicture` varchar(255) NOT NULL DEFAULT '' COMMENT '小图',
  `middlePicture` varchar(255) NOT NULL DEFAULT '' COMMENT '中图',
  `largePicture` varchar(255) NOT NULL DEFAULT '' COMMENT '大图',
  `about` text COMMENT '简介',
  `teacherIds` text COMMENT '显示的课程教师IDs',
  `studentNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学员数',
  `hitNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '查看次数',
  `likeNum` int(10) NOT NULL DEFAULT '0' COMMENT '点赞数',
  `postNum` int(10) NOT NULL DEFAULT '0' COMMENT '评论数',
  `userId` int(10) unsigned NOT NULL COMMENT '课程发布人ID',
  `parentId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课程的父Id',
  `locked` int(10) NOT NULL DEFAULT '0' COMMENT '是否上锁1上锁,0解锁',
  `recommended` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否为推荐课程',
  `recommendedSeq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐序号',
  `recommendedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐时间',
  `createdTime` int(10) unsigned NOT NULL COMMENT '课程创建时间',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  `orgId` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '组织机构ID',
  `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构内部编码',
  PRIMARY KEY (`id`),
  KEY `updatedTime` (`updatedTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `open_course_lesson`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `open_course_lesson` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '课时ID',
  `courseId` int(10) unsigned NOT NULL COMMENT '课时所属课程ID',
  `chapterId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课时所属章节ID',
  `number` int(10) unsigned NOT NULL COMMENT '课时编号',
  `seq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课时在课程中的序号',
  `free` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否为免费课时',
  `status` enum('unpublished','published') NOT NULL DEFAULT 'published' COMMENT '课时状态',
  `title` varchar(255) NOT NULL COMMENT '课时标题',
  `summary` text COMMENT '课时摘要',
  `tags` text COMMENT '课时标签',
  `type` varchar(64) NOT NULL DEFAULT 'text' COMMENT '课时类型',
  `content` text COMMENT '课时正文',
  `giveCredit` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学完课时获得的学分',
  `requireCredit` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学习课时前，需达到的学分',
  `mediaId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '媒体文件ID',
  `mediaSource` varchar(32) NOT NULL DEFAULT '' COMMENT '媒体文件来源(self:本站上传,youku:优酷)',
  `mediaName` varchar(255) NOT NULL DEFAULT '' COMMENT '媒体文件名称',
  `mediaUri` text COMMENT '媒体文件资源名',
  `homeworkId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '作业iD',
  `exerciseId` int(10) unsigned DEFAULT '0' COMMENT '练习ID',
  `length` int(11) unsigned DEFAULT NULL COMMENT '时长',
  `materialNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上传的资料数量',
  `quizNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '测验题目数量',
  `learnedNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '已学的学员数',
  `viewedNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '查看数',
  `startTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '直播课时开始时间',
  `endTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '直播课时结束时间',
  `memberNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '直播课时加入人数',
  `replayStatus` enum('ungenerated','generating','generated','videoGenerated') NOT NULL DEFAULT 'ungenerated',
  `maxOnlineNum` int(11) DEFAULT '0' COMMENT '直播在线人数峰值',
  `liveProvider` int(10) unsigned NOT NULL DEFAULT '0',
  `userId` int(10) unsigned NOT NULL COMMENT '发布人ID',
  `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  `copyId` int(10) NOT NULL DEFAULT '0' COMMENT '复制课时id',
  `testMode` enum('normal','realTime') DEFAULT 'normal' COMMENT '考试模式',
  `testStartTime` int(10) DEFAULT '0' COMMENT '实时考试开始时间',
  PRIMARY KEY (`id`),
  KEY `updatedTime` (`updatedTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `open_course_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `open_course_member` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '课程学员记录ID',
  `courseId` int(10) unsigned NOT NULL COMMENT '课程ID',
  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学员ID',
  `mobile` varchar(32) NOT NULL DEFAULT '' COMMENT '手机号码',
  `learnedNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '已学课时数',
  `learnTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学习时间',
  `seq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序序号',
  `isVisible` tinyint(2) NOT NULL DEFAULT '1' COMMENT '可见与否，默认为可见',
  `role` enum('student','teacher') NOT NULL DEFAULT 'student' COMMENT '课程会员角色',
  `ip` varchar(64) DEFAULT NULL COMMENT 'IP地址',
  `lastEnterTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上次进入时间',
  `isNotified` int(10) NOT NULL DEFAULT '0' COMMENT '直播开始通知',
  `createdTime` int(10) unsigned NOT NULL COMMENT '学员加入课程时间',
  PRIMARY KEY (`id`),
  KEY `open_course_member_ip_courseId_index` (`ip`,`courseId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `open_course_recommend`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `open_course_recommend` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `openCourseId` int(10) NOT NULL COMMENT '公开课id',
  `recommendCourseId` int(10) NOT NULL DEFAULT '0' COMMENT '推荐课程id',
  `seq` int(10) NOT NULL DEFAULT '0' COMMENT '序列',
  `type` varchar(255) NOT NULL COMMENT '类型',
  `createdTime` int(10) NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `open_course_recommend_openCourseId_index` (`openCourseId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='公开课推荐课程表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `order_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '订单日志ID',
  `orderId` int(10) unsigned NOT NULL COMMENT '订单ID',
  `type` varchar(32) NOT NULL COMMENT '订单日志类型',
  `message` text COMMENT '订单日志内容',
  `data` text COMMENT '订单日志数据',
  `userId` int(10) unsigned NOT NULL COMMENT '订单操作人',
  `ip` varchar(255) NOT NULL COMMENT '订单操作IP',
  `createdTime` int(10) unsigned NOT NULL COMMENT '订单日志记录时间',
  PRIMARY KEY (`id`),
  KEY `orderId` (`orderId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `order_referer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_referer` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `uv` varchar(64) NOT NULL,
  `data` text NOT NULL,
  `orderIds` text,
  `expiredTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '过期时间',
  PRIMARY KEY (`id`),
  KEY `order_referer_uv_expiredTime_index` (`uv`,`expiredTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户访问日志Token';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `order_referer_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_referer_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `refererLogId` int(11) NOT NULL COMMENT '促成订单的访问日志ID',
  `orderId` int(10) unsigned DEFAULT '0' COMMENT '订单ID',
  `sourceTargetId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '来源ID',
  `sourceTargetType` varchar(64) NOT NULL DEFAULT '' COMMENT '来源类型',
  `targetType` varchar(64) NOT NULL DEFAULT '' COMMENT '订单的对象类型',
  `targetId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单的对象ID',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单支付时间',
  `createdUserId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单支付者',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='订单促成日志';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `order_refund`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_refund` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '订单退款记录ID',
  `orderId` int(10) unsigned NOT NULL COMMENT '退款订单ID',
  `userId` int(10) unsigned NOT NULL COMMENT '退款人ID',
  `targetType` varchar(64) NOT NULL DEFAULT '' COMMENT '订单退款记录所属对象类型',
  `targetId` int(10) unsigned NOT NULL COMMENT '订单退款记录所属对象ID',
  `status` enum('created','success','failed','cancelled') NOT NULL DEFAULT 'created' COMMENT '退款状态',
  `expectedAmount` float(10,2) unsigned DEFAULT '0.00' COMMENT '期望退款的金额，NULL代表未知，0代表不需要退款',
  `actualAmount` float(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '实际退款金额，0代表无退款',
  `reasonType` varchar(64) NOT NULL DEFAULT '' COMMENT '退款理由类型',
  `reasonNote` varchar(1024) NOT NULL DEFAULT '' COMMENT '退款理由',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单退款记录最后更新时间',
  `createdTime` int(10) unsigned NOT NULL COMMENT '订单退款记录创建时间',
  `operator` int(11) NOT NULL COMMENT '操作人',
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '订单ID',
  `sn` varchar(32) NOT NULL COMMENT '订单编号',
  `status` enum('created','paid','refunding','refunded','cancelled') NOT NULL COMMENT '订单状态',
  `title` varchar(255) NOT NULL COMMENT '订单标题',
  `targetType` varchar(64) NOT NULL DEFAULT '' COMMENT '订单所属对象类型',
  `targetId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单所属对象ID',
  `amount` float(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '订单实付金额',
  `totalPrice` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '订单总价',
  `isGift` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否为赠送礼物',
  `giftTo` varchar(64) NOT NULL DEFAULT '' COMMENT '赠送给用户ID',
  `discountId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '折扣活动ID',
  `discount` float(10,2) NOT NULL DEFAULT '10.00' COMMENT '折扣',
  `token` varchar(50) DEFAULT NULL COMMENT '令牌',
  `refundId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后一次退款操作记录的ID',
  `userId` int(10) unsigned NOT NULL COMMENT '订单创建人',
  `coupon` varchar(255) NOT NULL DEFAULT '' COMMENT '优惠码',
  `couponDiscount` float(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '优惠码扣减金额',
  `payment` varchar(32) NOT NULL DEFAULT 'none' COMMENT '订单支付方式',
  `coinAmount` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '虚拟币支付额',
  `coinRate` float(10,2) NOT NULL DEFAULT '1.00' COMMENT '虚拟币汇率',
  `priceType` enum('RMB','Coin') NOT NULL DEFAULT 'RMB' COMMENT '创建订单时的标价类型',
  `bank` varchar(32) NOT NULL DEFAULT '' COMMENT '银行编号',
  `paidTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '支付时间',
  `cashSn` bigint(20) DEFAULT NULL COMMENT '支付流水号',
  `note` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `data` text COMMENT '订单业务数据',
  `refundEndTime` int(10) NOT NULL DEFAULT '0' COMMENT '退款截止时间',
  `createdTime` int(10) unsigned NOT NULL COMMENT '订单创建时间',
  `updatedTime` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sn` (`sn`),
  KEY `target_status` (`targetType`(6),`targetId`,`status`),
  KEY `idx_userId` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `org`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `org` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '组织机构ID',
  `name` varchar(255) NOT NULL COMMENT '名称',
  `parentId` int(11) NOT NULL DEFAULT '0' COMMENT '组织机构父ID',
  `childrenNum` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '辖下组织机构数量',
  `depth` int(11) NOT NULL DEFAULT '1' COMMENT '当前组织机构层级',
  `seq` int(11) NOT NULL DEFAULT '0' COMMENT '索引',
  `description` text COMMENT '备注',
  `code` varchar(255) NOT NULL DEFAULT '' COMMENT '机构编码',
  `orgCode` varchar(255) NOT NULL DEFAULT '0' COMMENT '内部编码',
  `createdUserId` int(11) NOT NULL COMMENT '创建用户ID',
  `createdTime` int(11) unsigned NOT NULL COMMENT '创建时间',
  `updateTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  `syncId` int(11) NOT NULL DEFAULT '0' COMMENT '同步部门ID',
  PRIMARY KEY (`id`),
  UNIQUE KEY `orgCode` (`orgCode`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='组织机构';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `post`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `post` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(255) NOT NULL COMMENT '岗位名称',
  `groupId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '分组ID',
  `seq` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '排序序号',
  `code` varchar(255) NOT NULL DEFAULT '' COMMENT '岗位编码',
  `createdUserId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建人',
  `createdTime` int(11) NOT NULL DEFAULT '0',
  `updatedTime` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='岗位';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `post_course`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `post_course` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `postId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '岗位ID',
  `courseSetId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '课程ID',
  `courseId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '教学计划ID',
  `seq` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '排序序号',
  `status` enum('unlearning','learning') NOT NULL DEFAULT 'unlearning' COMMENT '学习状态',
  `createdTime` int(10) NOT NULL DEFAULT '0',
  `updatedTime` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='岗位课程';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `post_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `post_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(255) NOT NULL COMMENT '岗位分组名称',
  `seq` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '排序序号',
  `createdUserId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建人',
  `createdTime` int(11) NOT NULL DEFAULT '0',
  `updatedTime` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='岗位分组';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `post_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `post_member` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `postId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '岗位ID',
  `userId` int(11) unsigned NOT NULL COMMENT '员工ID',
  `createdTime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatedTime` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='岗位成员';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `project_plan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_plan` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '培训计划ID',
  `name` varchar(255) NOT NULL COMMENT '名称',
  `startTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
  `endTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
  `status` varchar(32) NOT NULL DEFAULT 'unpublished' COMMENT 'published,unpublished,closed计划状态',
  `summary` text COMMENT '描述',
  `cover` varchar(1024) DEFAULT NULL,
  `itemNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '内容数量',
  `createdUserId` int(10) NOT NULL COMMENT '创建用户ID',
  `orgId` int(10) NOT NULL DEFAULT '1' COMMENT '组织机构id',
  `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构内部编码',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  `maxStudentNum` int(11) DEFAULT '0' COMMENT '最大人数限制',
  `requireAudit` tinyint(1) DEFAULT '0' COMMENT '是否开启报名审核',
  `enrollmentEndDate` int(11) DEFAULT '0' COMMENT '报名结束时间',
  `requireEnrollment` tinyint(1) DEFAULT '0' COMMENT '是否开启报名',
  `enrollmentStartDate` int(11) DEFAULT '0' COMMENT '报名开启时间',
  `categoryId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '项目分类Id',
  `conditionalAccess` int(11) NOT NULL DEFAULT '0' COMMENT '是否开启加入条件',
  `showable` int(11) NOT NULL DEFAULT '1' COMMENT '是否开启发布范围',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='培训项目';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `project_plan_advanced_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_plan_advanced_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `projectPlanId` int(11) DEFAULT '0' COMMENT '项目ID',
  `orgIds` varchar(128) DEFAULT '0' COMMENT '面向人群组织机构',
  `userGroupIds` varchar(128) DEFAULT '0' COMMENT '面向人群用户组',
  `postIds` varchar(128) DEFAULT '0' COMMENT '面向人群岗位',
  `requireRemark` tinyint(1) DEFAULT '0' COMMENT '是否开启报名备注',
  `requireMaterial` tinyint(1) DEFAULT '0' COMMENT '是否开启报名资料',
  `remarkRequirement` varchar(225) DEFAULT NULL COMMENT '备注说明',
  `materialRequirement` varchar(225) DEFAULT NULL COMMENT '资料要求说明',
  `material1` varchar(225) DEFAULT NULL COMMENT '资料名称1',
  `material2` varchar(225) DEFAULT NULL COMMENT '资料名称2',
  `material3` varchar(225) DEFAULT NULL COMMENT '资料名称3',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `project_plan_enrollment_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_plan_enrollment_record` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `projectPlanId` int(10) unsigned NOT NULL COMMENT '项目ID',
  `userId` int(10) unsigned NOT NULL COMMENT '学员ID',
  `submittedTime` int(10) unsigned NOT NULL COMMENT '报名时间',
  `status` enum('none','approved','rejected','submitted') NOT NULL DEFAULT 'submitted' COMMENT '状态',
  `approvedTime` int(10) NOT NULL DEFAULT '0' COMMENT '审核通过时间',
  `remark` varchar(255) DEFAULT NULL COMMENT '报名备注',
  `rejectedReason` varchar(255) DEFAULT NULL COMMENT '拒绝原因',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatedTime` int(10) NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `project_plan_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_plan_item` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `projectPlanId` int(10) NOT NULL COMMENT '项目计划ID ',
  `targetType` varchar(64) NOT NULL DEFAULT 'course' COMMENT '活动类型',
  `targetId` int(10) unsigned NOT NULL COMMENT '所属ID',
  `startTime` int(10) NOT NULL DEFAULT '0' COMMENT '开始时间',
  `endTime` int(10) NOT NULL DEFAULT '0' COMMENT '结束时间',
  `seq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '序号',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='培训项目内容';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `project_plan_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_plan_member` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `projectPlanId` int(10) NOT NULL COMMENT '项目计划ID ',
  `userId` int(10) unsigned NOT NULL COMMENT '用户ID',
  `status` varchar(32) NOT NULL DEFAULT 'notStart' COMMENT '项目完成状态(finished, ongoing, notStart)',
  `operatedUserId` int(10) NOT NULL COMMENT '操作人ID',
  `finishedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '完成时间',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='培训项目成员';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `project_plan_member_statistic_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_plan_member_statistic_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '培训项目成员结果ID',
  `memberId` int(11) NOT NULL DEFAULT '0' COMMENT '培训项目成员ID',
  `userId` int(11) NOT NULL DEFAULT '0' COMMENT '学员ID',
  `projectPlanId` int(11) NOT NULL DEFAULT '0' COMMENT '培训项目ID',
  `finishedItemNum` int(10) NOT NULL DEFAULT '0' COMMENT '培训项目内容完成数',
  `finishedOnlineCourseNum` int(11) NOT NULL DEFAULT '0' COMMENT '培训项目线上课程完成数',
  `finishedOfflineCourseNum` int(11) NOT NULL DEFAULT '0' COMMENT '培训项目线下课程完成数',
  `finishedSignInNum` int(11) NOT NULL DEFAULT '0' COMMENT '培训项目签到完成数',
  `submittedHomeworkNum` int(11) NOT NULL DEFAULT '0' COMMENT '培训项目提交作业数',
  `passedHomeworkNum` int(11) DEFAULT '0' COMMENT '培训项目作业通过数',
  `passedOnlineExamNum` int(11) DEFAULT '0' COMMENT '培训项目线上考试通过数',
  `passedOfflineExamNum` int(11) DEFAULT '0' COMMENT '培训项目线下考试通过数',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `question`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `question` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '题目ID',
  `type` varchar(64) NOT NULL DEFAULT '' COMMENT '题目类型',
  `stem` text COMMENT '题干',
  `score` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '分数',
  `answer` text COMMENT '参考答案',
  `analysis` text COMMENT '解析',
  `metas` text COMMENT '题目元信息',
  `categoryId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '类别',
  `difficulty` varchar(64) NOT NULL DEFAULT 'normal' COMMENT '难度',
  `target` varchar(255) NOT NULL DEFAULT '' COMMENT '从属于',
  `courseId` int(10) unsigned NOT NULL DEFAULT '0',
  `lessonId` int(10) unsigned NOT NULL DEFAULT '0',
  `parentId` int(10) unsigned DEFAULT '0' COMMENT '材料父ID',
  `subCount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '子题数量',
  `finishedTimes` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '完成次数',
  `passedTimes` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '成功次数',
  `createdUserId` int(10) unsigned NOT NULL DEFAULT '0',
  `updatedUserId` int(10) unsigned NOT NULL DEFAULT '0',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `copyId` int(10) NOT NULL DEFAULT '0' COMMENT '复制问题对应Id',
  `courseSetId` int(10) NOT NULL DEFAULT '0',
  `syncId` int(11) NOT NULL DEFAULT '0' COMMENT '租赁资源ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='问题表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `question_analysis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `question_analysis` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `targetId` int(10) unsigned NOT NULL DEFAULT '0',
  `targetType` varchar(30) NOT NULL,
  `activityId` int(10) unsigned NOT NULL DEFAULT '0',
  `questionId` int(10) unsigned NOT NULL,
  `choiceIndex` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '选项key',
  `firstAnswerCount` int(10) unsigned NOT NULL DEFAULT '0',
  `totalAnswerCount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '全部答题人数',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='答题分析表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `question_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `question_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '题目类别ID',
  `name` varchar(255) NOT NULL COMMENT '类别名称',
  `target` varchar(255) NOT NULL DEFAULT '' COMMENT '从属于',
  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '操作用户',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `seq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序序号',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='题库类别表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `question_favorite`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `question_favorite` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '题目收藏ID',
  `questionId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '被收藏的题目ID',
  `targetType` varchar(50) NOT NULL DEFAULT '',
  `targetId` int(10) unsigned NOT NULL DEFAULT '0',
  `target` varchar(255) NOT NULL DEFAULT '' COMMENT '题目所属对象',
  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '收藏人ID',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '收藏时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `question_marker`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `question_marker` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `markerId` int(10) unsigned NOT NULL COMMENT '驻点Id',
  `questionId` int(10) unsigned NOT NULL COMMENT '问题Id',
  `seq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `type` varchar(64) NOT NULL DEFAULT '' COMMENT '题目类型',
  `stem` text COMMENT '题干',
  `answer` text COMMENT '参考答案',
  `analysis` text COMMENT '解析',
  `metas` text COMMENT '题目元信息',
  `difficulty` varchar(64) NOT NULL DEFAULT 'normal' COMMENT '难度',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='弹题';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `question_marker_result`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `question_marker_result` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `markerId` int(10) unsigned NOT NULL COMMENT '驻点Id',
  `questionMarkerId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '弹题ID',
  `taskId` int(10) unsigned NOT NULL DEFAULT '0',
  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '做题人ID',
  `status` enum('none','right','partRight','wrong','noAnswer') NOT NULL DEFAULT 'none' COMMENT '结果状态',
  `answer` text,
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_qmid_taskid_stats` (`questionMarkerId`,`taskId`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ratelimit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ratelimit` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `_key` varchar(128) NOT NULL,
  `data` varchar(32) NOT NULL,
  `deadline` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `_key` (`_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `recent_post_num`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recent_post_num` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `ip` varchar(20) NOT NULL COMMENT 'IP',
  `type` varchar(255) NOT NULL COMMENT '类型',
  `num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'post次数',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后一次更新时间',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='黑名单表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `referer_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `referer_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `targetId` varchar(64) DEFAULT NULL COMMENT '模块ID',
  `targetType` varchar(64) NOT NULL COMMENT '模块类型',
  `targetInnerType` varchar(64) DEFAULT NULL COMMENT '模块自身的类型',
  `refererUrl` varchar(1024) DEFAULT '' COMMENT '访问来源Url',
  `refererHost` varchar(1024) DEFAULT '' COMMENT '访问来源Url',
  `refererName` varchar(64) DEFAULT '' COMMENT '访问来源站点名称',
  `orderCount` int(10) unsigned DEFAULT '0' COMMENT '促成订单数',
  `ip` varchar(64) DEFAULT NULL COMMENT '访问者IP',
  `userAgent` text COMMENT '浏览器的标识',
  `uri` varchar(1024) DEFAULT '' COMMENT '访问Url',
  `createdUserId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '访问者',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '访问时间',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='模块(课程|班级|公开课|...)的访问来源日志';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `resource_access_scope_hire_date`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resource_access_scope_hire_date` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `resourceType` varchar(32) NOT NULL COMMENT '资源类型',
  `resourceId` int(10) NOT NULL COMMENT '资源id',
  `scope` varchar(255) NOT NULL COMMENT '范围id',
  `createdUserId` int(10) NOT NULL COMMENT '创建人',
  `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `resourceType_resourceId` (`resourceType`,`resourceId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='加入范围入职时间';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `resource_access_scope_org`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resource_access_scope_org` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `resourceType` varchar(32) NOT NULL COMMENT '资源类型',
  `resourceId` int(10) NOT NULL COMMENT '资源id',
  `scope` int(10) NOT NULL COMMENT '范围id',
  `createdUserId` int(10) NOT NULL COMMENT '创建人',
  `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `resourceType_resourceId` (`resourceType`,`resourceId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='加入范围组织机构';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `resource_access_scope_post`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resource_access_scope_post` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `resourceType` varchar(32) NOT NULL COMMENT '资源类型',
  `resourceId` int(10) NOT NULL COMMENT '资源id',
  `scope` int(10) NOT NULL COMMENT '范围id',
  `createdUserId` int(10) NOT NULL COMMENT '创建人',
  `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `resourceType_resourceId` (`resourceType`,`resourceId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='加入范围岗位';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `resource_access_scope_user_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resource_access_scope_user_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `resourceType` varchar(32) NOT NULL COMMENT '资源类型',
  `resourceId` int(10) NOT NULL COMMENT '资源id',
  `scope` int(10) NOT NULL COMMENT '范围id',
  `createdUserId` int(10) NOT NULL COMMENT '创建人',
  `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `resourceType_resourceId` (`resourceType`,`resourceId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='加入范围用户组';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `resource_use_permission_shared`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resource_use_permission_shared` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `resourceType` varchar(32) NOT NULL COMMENT '资源类型',
  `resourceId` int(10) NOT NULL COMMENT '资源id',
  `toUserId` int(10) NOT NULL COMMENT '被授权人id',
  `fromUserId` int(10) NOT NULL COMMENT '授权人id',
  `createdTime` int(10) NOT NULL COMMENT '授权时间',
  PRIMARY KEY (`id`),
  KEY `resourceType_toUserId` (`resourceType`,`toUserId`),
  KEY `resourceId` (`resourceId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='资源使用授权';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `resource_use_permission_shared_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resource_use_permission_shared_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `resourceType` varchar(32) NOT NULL COMMENT '资源类型',
  `resourceId` int(10) NOT NULL COMMENT '资源id',
  `toUserId` int(10) NOT NULL COMMENT '被共享人id',
  `fromUserId` int(10) NOT NULL COMMENT '共享人id',
  `actionUser` int(10) NOT NULL COMMENT '操作人',
  `action` varchar(32) NOT NULL DEFAULT 'shared' COMMENT '操作(共享/取消)',
  `createdTime` int(10) NOT NULL COMMENT '记录时间',
  PRIMARY KEY (`id`),
  KEY `resourceType_resourceId` (`resourceType`,`resourceId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='资源使用授权操作历史记录';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `resource_visible_scope_org`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resource_visible_scope_org` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `resourceType` varchar(32) NOT NULL COMMENT '资源类型',
  `resourceId` int(10) NOT NULL COMMENT '资源id',
  `scope` int(10) NOT NULL COMMENT '范围id',
  `createdUserId` int(10) NOT NULL COMMENT '创建人',
  `createdTime` int(10) NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `resourceType_scope` (`resourceType`,`scope`),
  KEY `resourceType_resourceId` (`resourceType`,`resourceId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='发布范围组织机构';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `resource_visible_scope_post`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resource_visible_scope_post` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `resourceType` varchar(32) NOT NULL COMMENT '资源类型',
  `resourceId` int(10) NOT NULL COMMENT '资源id',
  `scope` int(10) NOT NULL COMMENT '范围id',
  `createdUserId` int(10) NOT NULL COMMENT '创建人',
  `createdTime` int(10) NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `resourceType_scope` (`resourceType`,`scope`),
  KEY `resourceType_resourceId` (`resourceType`,`resourceId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='发布范围岗位';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `resource_visible_scope_user_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resource_visible_scope_user_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `resourceType` varchar(32) NOT NULL COMMENT '资源类型',
  `resourceId` int(10) NOT NULL COMMENT '资源id',
  `scope` int(10) NOT NULL COMMENT '范围id',
  `createdUserId` int(10) NOT NULL COMMENT '创建人',
  `createdTime` int(10) NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `resourceType_scope` (`resourceType`,`scope`),
  KEY `resourceType_resourceId` (`resourceType`,`resourceId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='发布范围用户组';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `reward_point_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reward_point_account` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL COMMENT '用户Id',
  `balance` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '积分余额',
  `outflowAmount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '出账积分总数',
  `inflowAmount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '入账积分总数',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='积分账户';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `reward_point_account_flow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reward_point_account_flow` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL COMMENT '用户ID',
  `sn` bigint(20) unsigned NOT NULL COMMENT '账目流水号',
  `type` varchar(32) NOT NULL DEFAULT '' COMMENT 'inflow, outflow',
  `way` varchar(255) NOT NULL DEFAULT '' COMMENT '积分获取方式',
  `amount` int(10) NOT NULL DEFAULT '0' COMMENT '金额(积分)',
  `name` varchar(1024) NOT NULL DEFAULT '' COMMENT '帐目名称',
  `operator` int(10) unsigned NOT NULL COMMENT '操作员ID',
  `targetId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '流水所属对象ID',
  `targetType` varchar(64) NOT NULL DEFAULT '' COMMENT '流水所属对象类型',
  `note` varchar(255) NOT NULL DEFAULT '',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='积分帐目流水';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL COMMENT '权限名称',
  `code` varchar(32) NOT NULL COMMENT '权限代码',
  `data` text COMMENT '权限配置',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `createdUserId` int(10) unsigned NOT NULL COMMENT '创建用户ID',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `sess_id` varbinary(128) NOT NULL,
  `sess_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `sess_data` blob NOT NULL,
  `sess_time` int(10) unsigned NOT NULL,
  `sess_lifetime` mediumint(9) NOT NULL,
  PRIMARY KEY (`sess_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `setting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `setting` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '系统设置ID',
  `name` varchar(64) NOT NULL DEFAULT '' COMMENT '系统设置名',
  `value` longblob COMMENT '系统设置值',
  `namespace` varchar(255) NOT NULL DEFAULT 'default',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`,`namespace`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `shortcut`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shortcut` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sign_card`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sign_card` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL DEFAULT '0',
  `cardNum` int(10) unsigned NOT NULL DEFAULT '0',
  `useTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sign_target_statistics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sign_target_statistics` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '系统id',
  `targetType` varchar(255) NOT NULL DEFAULT '' COMMENT '签到目标类型',
  `targetId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '签到目标id',
  `signedNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '签到人数',
  `date` int(6) unsigned NOT NULL DEFAULT '0' COMMENT '统计日期',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sign_user_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sign_user_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '系统id',
  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `targetType` varchar(255) NOT NULL DEFAULT '' COMMENT '签到目标类型',
  `targetId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '签到目标id',
  `rank` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '签到排名',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '签到时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sign_user_statistics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sign_user_statistics` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '系统id',
  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `targetType` varchar(255) NOT NULL DEFAULT '' COMMENT '签到目标类型',
  `targetId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '签到目标id',
  `keepDays` int(5) unsigned NOT NULL DEFAULT '0' COMMENT '连续签到天数',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `status` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL COMMENT '动态发布的人',
  `courseId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课程Id',
  `classroomId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '班级id',
  `type` varchar(64) NOT NULL COMMENT '动态类型',
  `objectType` varchar(64) NOT NULL DEFAULT '' COMMENT '动态对象的类型',
  `objectId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '动态对象ID',
  `message` text NOT NULL COMMENT '动态的消息体',
  `properties` text NOT NULL COMMENT '动态的属性',
  `commentNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '评论数',
  `likeNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '被赞的数量',
  `private` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否隐藏',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '动态发布时间',
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`),
  KEY `createdTime` (`createdTime`),
  KEY `userid_type_object` (`userId`,`type`(8),`objectType`(6),`objectId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `subtitle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subtitle` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '字幕名称',
  `subtitleId` int(10) unsigned NOT NULL COMMENT 'subtitle的uploadFileId',
  `mediaId` int(10) unsigned NOT NULL COMMENT 'video/audio的uploadFileId',
  `ext` varchar(12) NOT NULL DEFAULT '' COMMENT '后缀',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='字幕关联表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '标签ID',
  `name` varchar(64) NOT NULL COMMENT '标签名称',
  `orgId` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '组织机构ID',
  `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构内部编码',
  `createdTime` int(10) unsigned NOT NULL COMMENT '标签创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tag_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tag_group` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '标签ID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '标签组名字',
  `scope` varchar(255) NOT NULL DEFAULT '' COMMENT '标签组应用范围',
  `tagNum` int(10) NOT NULL DEFAULT '0' COMMENT '标签组里的标签数量',
  `updatedTime` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `createdTime` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='标签组表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tag_group_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tag_group_tag` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `tagId` int(10) NOT NULL DEFAULT '0' COMMENT '标签ID',
  `groupId` int(10) NOT NULL DEFAULT '0' COMMENT '标签组ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='标签组跟标签的中间表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tag_owner`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tag_owner` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '标签ID',
  `ownerType` varchar(255) NOT NULL DEFAULT '' COMMENT '标签拥有者类型',
  `ownerId` int(10) NOT NULL DEFAULT '0' COMMENT '标签拥有者id',
  `tagId` int(10) NOT NULL DEFAULT '0' COMMENT '标签id',
  `userId` int(10) NOT NULL DEFAULT '0' COMMENT '操作用户id',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `tag_owner_ownerType_ownerId_index` (`ownerType`,`ownerId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='标签关系表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `task`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `task` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL COMMENT '任务标题',
  `description` text COMMENT '任务描述',
  `meta` text COMMENT '任务元信息',
  `userId` int(10) NOT NULL DEFAULT '0',
  `taskType` varchar(100) NOT NULL COMMENT '任务类型',
  `batchId` int(10) NOT NULL DEFAULT '0' COMMENT '批次Id',
  `targetId` int(10) NOT NULL DEFAULT '0' COMMENT '类型id,可以是课时id,作业id等',
  `targetType` varchar(100) DEFAULT NULL COMMENT '类型,可以是课时,作业等',
  `taskStartTime` int(10) NOT NULL DEFAULT '0' COMMENT '任务开始时间',
  `taskEndTime` int(10) NOT NULL DEFAULT '0' COMMENT '任务结束时间',
  `intervalDate` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '历时天数',
  `status` enum('active','completed') NOT NULL DEFAULT 'active',
  `required` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否为必做任务,0否,1是',
  `completedTime` int(10) NOT NULL DEFAULT '0' COMMENT '任务完成时间',
  `createdTime` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `teacher_level`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teacher_level` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(255) NOT NULL COMMENT '等级名称',
  `createdUserId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建人',
  `createdTime` int(11) NOT NULL DEFAULT '0',
  `updatedTime` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='讲师等级';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `teacher_profession_field`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teacher_profession_field` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `seq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '序号',
  `name` varchar(128) NOT NULL COMMENT '专业领域名称',
  `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='专业领域';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `teacher_profile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teacher_profile` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL DEFAULT '0' COMMENT 'userID',
  `levelId` int(11) NOT NULL DEFAULT '0' COMMENT '等级ID',
  `teacherProfessionFieldIds` varchar(128) NOT NULL DEFAULT '' COMMENT '教学领域ID',
  `creator` int(11) DEFAULT NULL COMMENT '创建者',
  `createdTime` int(10) unsigned NOT NULL COMMENT '创建时间',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='讲师资料';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `testpaper`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testpaper` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '试卷ID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '试卷名称',
  `description` text COMMENT '试卷说明',
  `limitedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '限时(单位：秒)',
  `pattern` varchar(255) NOT NULL DEFAULT '' COMMENT '试卷生成/显示模式',
  `target` varchar(255) NOT NULL DEFAULT '' COMMENT '试卷所属对象',
  `status` varchar(32) NOT NULL DEFAULT 'draft' COMMENT '试卷状态：draft,open,closed',
  `score` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '总分',
  `passedScore` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '通过考试的分数线',
  `itemCount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '题目数量',
  `createdUserId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建人',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatedUserId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改人',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  `metas` text COMMENT '题型排序',
  `copyId` int(10) NOT NULL DEFAULT '0' COMMENT '复制试卷对应Id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `testpaper_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testpaper_item` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '试卷条目ID',
  `testId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属试卷',
  `seq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '题目顺序',
  `questionId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '题目ID',
  `questionType` varchar(64) NOT NULL DEFAULT '' COMMENT '题目类别',
  `parentId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父题ID',
  `score` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '分值',
  `missScore` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '漏选得分',
  `copyId` int(10) NOT NULL DEFAULT '0' COMMENT '复制来源testpaper_item的id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `testpaper_item_result`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testpaper_item_result` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '试卷题目做题结果ID',
  `itemId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '试卷条目ID',
  `testId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '试卷ID',
  `testPaperResultId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '试卷结果ID',
  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '做题人ID',
  `questionId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '题目ID',
  `status` enum('none','right','partRight','wrong','noAnswer') NOT NULL DEFAULT 'none' COMMENT '结果状态',
  `score` float(10,1) NOT NULL DEFAULT '0.0' COMMENT '得分',
  `answer` text COMMENT '回答',
  `teacherSay` text COMMENT '老师评价',
  `pId` int(10) NOT NULL DEFAULT '0' COMMENT '复制试卷题目Id',
  PRIMARY KEY (`id`),
  KEY `testPaperResultId` (`testPaperResultId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `testpaper_item_result_v8`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testpaper_item_result_v8` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `itemId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '试卷题目id',
  `testId` int(10) unsigned NOT NULL DEFAULT '0',
  `resultId` int(10) NOT NULL DEFAULT '0' COMMENT '试卷结果ID',
  `userId` int(10) unsigned NOT NULL DEFAULT '0',
  `questionId` int(10) unsigned NOT NULL DEFAULT '0',
  `status` enum('none','right','partRight','wrong','noAnswer') NOT NULL DEFAULT 'none',
  `score` float(10,1) NOT NULL DEFAULT '0.0',
  `answer` text,
  `teacherSay` text,
  `pId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '复制试卷题目Id',
  `migrateItemResultId` int(11) unsigned NOT NULL DEFAULT '0',
  `type` varchar(32) NOT NULL DEFAULT 'testpaper' COMMENT '测验类型',
  PRIMARY KEY (`id`),
  KEY `testPaperResultId` (`resultId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `testpaper_item_v8`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testpaper_item_v8` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '题目',
  `testId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属试卷',
  `seq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '题目顺序',
  `questionId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '题目id',
  `questionType` varchar(64) NOT NULL DEFAULT '' COMMENT '题目类别',
  `parentId` int(10) unsigned NOT NULL DEFAULT '0',
  `score` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '分值',
  `missScore` float(10,1) unsigned NOT NULL DEFAULT '0.0',
  `copyId` int(10) NOT NULL DEFAULT '0' COMMENT '复制来源testpaper_item的id',
  `migrateItemId` int(11) unsigned NOT NULL DEFAULT '0',
  `type` varchar(32) NOT NULL DEFAULT 'testpaper' COMMENT '测验类型',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `testpaper_result`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testpaper_result` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '试卷结果ID',
  `paperName` varchar(255) NOT NULL DEFAULT '' COMMENT '试卷名称',
  `testId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '试卷ID',
  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '做卷人ID',
  `score` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '总分',
  `objectiveScore` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '主观题得分',
  `subjectiveScore` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '客观题得分',
  `teacherSay` text COMMENT '老师评价',
  `rightItemCount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '正确题目数',
  `passedStatus` enum('none','excellent','good','passed','unpassed') NOT NULL DEFAULT 'none' COMMENT '考试通过状态，none表示该考试没有',
  `limitedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '试卷限制时间(秒)',
  `beginTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
  `endTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
  `updateTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  `active` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `status` enum('doing','paused','reviewing','finished') NOT NULL COMMENT '状态',
  `target` varchar(255) NOT NULL DEFAULT '' COMMENT '试卷结果所属对象',
  `checkTeacherId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '批卷老师ID',
  `checkedTime` int(11) NOT NULL DEFAULT '0' COMMENT '批卷时间',
  `usedTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `testpaper_result_v8`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testpaper_result_v8` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `paperName` varchar(255) NOT NULL DEFAULT '',
  `testId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'testId',
  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'UserId',
  `courseId` int(10) NOT NULL DEFAULT '0',
  `lessonId` int(10) NOT NULL DEFAULT '0',
  `score` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '分数',
  `objectiveScore` float(10,1) unsigned NOT NULL DEFAULT '0.0',
  `subjectiveScore` float(10,1) unsigned NOT NULL DEFAULT '0.0',
  `teacherSay` text,
  `rightItemCount` int(10) unsigned NOT NULL DEFAULT '0',
  `passedStatus` enum('none','excellent','good','passed','unpassed') NOT NULL DEFAULT 'none' COMMENT '考试通过状态，none表示该考试没有',
  `limitedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '试卷限制时间(秒)',
  `beginTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
  `endTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
  `updateTime` int(10) unsigned NOT NULL DEFAULT '0',
  `metas` text COMMENT '练习的题型排序等附属信息',
  `active` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `status` enum('doing','paused','reviewing','finished') NOT NULL COMMENT '状态',
  `target` varchar(255) NOT NULL DEFAULT '',
  `checkTeacherId` int(10) unsigned NOT NULL DEFAULT '0',
  `checkedTime` int(11) NOT NULL DEFAULT '0',
  `usedTime` int(10) unsigned NOT NULL DEFAULT '0',
  `type` varchar(32) NOT NULL DEFAULT 'testpaper' COMMENT '测验类型',
  `courseSetId` int(11) unsigned NOT NULL DEFAULT '0',
  `migrateResultId` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `testpaper_v8`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testpaper_v8` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '试卷名称',
  `description` text COMMENT '试卷说明',
  `courseId` int(10) NOT NULL DEFAULT '0',
  `lessonId` int(10) NOT NULL DEFAULT '0',
  `limitedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '限时(单位：秒)',
  `pattern` varchar(255) NOT NULL DEFAULT '' COMMENT '试卷生成/显示模式',
  `target` varchar(255) NOT NULL DEFAULT '',
  `status` varchar(32) NOT NULL DEFAULT 'draft' COMMENT '试卷状态：draft,open,closed',
  `score` float(10,1) unsigned NOT NULL DEFAULT '0.0' COMMENT '总分',
  `passedCondition` text,
  `itemCount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '题目数量',
  `createdUserId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建人',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatedUserId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改人',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  `metas` text COMMENT '题型排序',
  `copyId` int(10) NOT NULL DEFAULT '0' COMMENT '复制试卷对应Id',
  `type` varchar(32) NOT NULL DEFAULT 'testpaper' COMMENT '测验类型',
  `courseSetId` int(11) unsigned NOT NULL DEFAULT '0',
  `migrateTestId` int(11) unsigned NOT NULL DEFAULT '0',
  `syncId` int(11) NOT NULL DEFAULT '0' COMMENT '租赁资源ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `theme_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `theme_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `config` text,
  `confirmConfig` text,
  `allConfig` text,
  `updatedTime` int(11) NOT NULL DEFAULT '0',
  `createdTime` int(11) NOT NULL DEFAULT '0',
  `updatedUserId` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `theme_config_name_uindex` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `thread`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `thread` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `targetType` varchar(255) NOT NULL DEFAULT 'classroom' COMMENT '所属 类型',
  `targetId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属类型 ID',
  `title` varchar(255) NOT NULL COMMENT '标题',
  `content` text COMMENT '内容',
  `ats` text COMMENT '@(提)到的人',
  `nice` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '加精',
  `sticky` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '置顶',
  `solved` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否有老师回答(已被解决)',
  `lastPostUserId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后回复人ID',
  `lastPostTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后回复时间',
  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `type` varchar(255) NOT NULL DEFAULT '' COMMENT '话题类型',
  `postNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '回复数',
  `hitNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点击数',
  `memberNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '成员人数',
  `status` enum('open','closed') NOT NULL DEFAULT 'open' COMMENT '状态',
  `startTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
  `endTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
  `maxUsers` int(10) NOT NULL DEFAULT '0' COMMENT '最大人数',
  `actvityPicture` varchar(255) DEFAULT NULL COMMENT '活动图片',
  `location` varchar(1024) DEFAULT NULL COMMENT '地点',
  `relationId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '从属ID',
  `categoryId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分类ID',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updateTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '话题最后一次被编辑或回复时间',
  PRIMARY KEY (`id`),
  KEY `updateTime` (`updateTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `thread_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `thread_member` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '系统Id',
  `threadId` int(10) unsigned NOT NULL COMMENT '话题Id',
  `userId` int(10) unsigned NOT NULL COMMENT '用户Id',
  `nickname` varchar(255) DEFAULT NULL COMMENT '用户名',
  `truename` varchar(255) DEFAULT NULL COMMENT '真实姓名',
  `mobile` varchar(32) DEFAULT NULL COMMENT '手机号码',
  `createdTIme` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='话题成员表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `thread_post`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `thread_post` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `threadId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '话题ID',
  `content` text NOT NULL COMMENT '内容',
  `adopted` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否被采纳(是老师回答)',
  `ats` text COMMENT '@(提)到的人',
  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `parentId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父ID',
  `subposts` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '子话题数量',
  `ups` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '投票数',
  `targetType` varchar(255) NOT NULL DEFAULT 'classroom' COMMENT '所属 类型',
  `targetId` int(10) unsigned NOT NULL COMMENT '所属 类型ID',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatedTime` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `thread_vote`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `thread_vote` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `threadId` int(10) unsigned NOT NULL COMMENT '话题ID',
  `postId` int(10) unsigned NOT NULL COMMENT '回帖ID',
  `action` enum('up','down') NOT NULL COMMENT '投票类型',
  `userId` int(10) unsigned NOT NULL COMMENT '投票人ID',
  `createdTime` int(10) unsigned NOT NULL COMMENT '投票时间',
  PRIMARY KEY (`id`),
  KEY `postId` (`threadId`,`postId`,`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='话题投票表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `upgrade_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `upgrade_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `remoteId` int(11) NOT NULL COMMENT 'packageId',
  `installedId` int(11) DEFAULT NULL COMMENT '本地已安装id',
  `ename` varchar(32) NOT NULL COMMENT '名称',
  `cname` varchar(32) NOT NULL COMMENT '中文名称',
  `fromv` varchar(32) DEFAULT NULL COMMENT '初始版本',
  `tov` varchar(32) NOT NULL COMMENT '目标版本',
  `type` smallint(6) NOT NULL COMMENT '升级类型',
  `dbBackPath` text COMMENT '数据库备份文件',
  `srcBackPath` text COMMENT '源文件备份地址',
  `status` varchar(32) NOT NULL COMMENT '状态(ROLLBACK,ERROR,SUCCESS,RECOVERED)',
  `logtime` int(11) NOT NULL COMMENT '升级时间',
  `uid` int(10) unsigned NOT NULL COMMENT 'uid',
  `ip` varchar(32) DEFAULT NULL COMMENT 'ip',
  `reason` text COMMENT '失败原因',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='本地升级日志表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `upgrade_notice`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `upgrade_notice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `code` varchar(100) NOT NULL COMMENT '编码',
  `version` varchar(100) NOT NULL COMMENT '版本号',
  `createdTime` int(11) NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户升级提示查看';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `upload_file_inits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `upload_file_inits` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `globalId` varchar(32) NOT NULL DEFAULT '0' COMMENT '云文件ID',
  `status` enum('uploading','ok') NOT NULL DEFAULT 'ok' COMMENT '文件上传状态',
  `hashId` varchar(128) NOT NULL DEFAULT '' COMMENT '文件的HashID',
  `targetId` int(11) NOT NULL COMMENT '所存目标id',
  `targetType` varchar(64) NOT NULL DEFAULT '' COMMENT '目标类型',
  `filename` varchar(1024) NOT NULL DEFAULT '',
  `ext` varchar(12) NOT NULL DEFAULT '' COMMENT '后缀',
  `fileSize` bigint(20) NOT NULL DEFAULT '0',
  `etag` varchar(256) NOT NULL DEFAULT '',
  `length` int(10) unsigned NOT NULL DEFAULT '0',
  `convertHash` varchar(256) NOT NULL DEFAULT '' COMMENT '文件转换时的查询转换进度用的Hash值',
  `convertStatus` enum('none','waiting','doing','success','error') NOT NULL DEFAULT 'none',
  `metas` text,
  `metas2` text,
  `type` enum('document','video','audio','image','ppt','other','flash','subtitle') NOT NULL DEFAULT 'other' COMMENT '文件类型',
  `storage` enum('local','cloud') NOT NULL,
  `convertParams` text COMMENT '文件转换参数',
  `updatedUserId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新用户名',
  `updatedTime` int(10) unsigned DEFAULT '0',
  `createdUserId` int(10) unsigned NOT NULL,
  `createdTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hashId` (`hashId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `upload_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `upload_files` (
  `id` int(10) unsigned NOT NULL COMMENT '上传文件ID',
  `globalId` varchar(32) NOT NULL DEFAULT '0' COMMENT '云文件ID',
  `status` enum('uploading','ok') NOT NULL DEFAULT 'ok' COMMENT '文件上传状态',
  `hashId` varchar(128) NOT NULL DEFAULT '' COMMENT '文件的HashID',
  `targetId` int(11) NOT NULL COMMENT '所存目标ID',
  `targetType` varchar(64) NOT NULL DEFAULT '' COMMENT '目标类型',
  `useType` varchar(64) DEFAULT NULL COMMENT '文件使用的模块类型',
  `filename` varchar(1024) NOT NULL DEFAULT '' COMMENT '文件名',
  `ext` varchar(12) NOT NULL DEFAULT '' COMMENT '后缀',
  `fileSize` bigint(20) NOT NULL DEFAULT '0' COMMENT '文件大小',
  `etag` varchar(256) NOT NULL DEFAULT '' COMMENT 'ETAG',
  `length` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '长度（音视频则为时长，PPT/文档为页数）',
  `description` text,
  `convertHash` varchar(128) NOT NULL DEFAULT '' COMMENT '文件转换时的查询转换进度用的Hash值',
  `convertStatus` enum('none','waiting','doing','success','error') NOT NULL DEFAULT 'none' COMMENT '文件转换状态',
  `convertParams` text COMMENT '文件转换参数',
  `metas` text COMMENT '元信息',
  `metas2` text COMMENT '元信息',
  `type` enum('document','video','audio','image','ppt','other','flash','subtitle') NOT NULL DEFAULT 'other' COMMENT '文件类型',
  `storage` enum('local','cloud') NOT NULL COMMENT '文件存储方式',
  `isPublic` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否公开文件',
  `canDownload` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否可下载',
  `usedCount` int(10) unsigned NOT NULL DEFAULT '0',
  `updatedUserId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新用户名',
  `updatedTime` int(10) unsigned DEFAULT '0' COMMENT '文件最后更新时间',
  `createdUserId` int(10) unsigned NOT NULL COMMENT '文件上传人',
  `createdTime` int(10) unsigned NOT NULL COMMENT '文件上传时间',
  `audioConvertStatus` enum('none','waiting','doing','success','error') NOT NULL DEFAULT 'none' COMMENT '视频转音频的状态',
  `syncId` int(11) NOT NULL DEFAULT '0' COMMENT '租赁资源ID',
  `belongResourceCodes` text COMMENT '租赁文件所属所有租赁课程的编码',
  PRIMARY KEY (`id`),
  UNIQUE KEY `convertHash` (`convertHash`(64)),
  UNIQUE KEY `hashId` (`hashId`(120))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `upload_files_collection`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `upload_files_collection` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fileId` int(10) unsigned NOT NULL COMMENT '文件Id',
  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '收藏者',
  `createdTime` int(10) unsigned NOT NULL,
  `updatedTime` int(10) unsigned DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='文件收藏表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `upload_files_share`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `upload_files_share` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sourceUserId` int(10) unsigned NOT NULL COMMENT '上传文件的用户ID',
  `targetUserId` int(10) unsigned NOT NULL COMMENT '文件分享目标用户ID',
  `isActive` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否有效',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `upload_files_share_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `upload_files_share_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '系统ID',
  `sourceUserId` int(10) NOT NULL COMMENT '分享用户的ID',
  `targetUserId` int(10) NOT NULL COMMENT '被分享的用户的ID',
  `isActive` tinyint(4) NOT NULL DEFAULT '0',
  `createdTime` int(10) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `upload_files_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `upload_files_tag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '系统ID',
  `fileId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文件ID',
  `tagId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '标签ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='文件与标签的关联表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `email` varchar(128) NOT NULL COMMENT '用户邮箱',
  `verifiedMobile` varchar(32) NOT NULL DEFAULT '',
  `password` varchar(64) NOT NULL COMMENT '用户密码',
  `salt` varchar(32) NOT NULL COMMENT '密码SALT',
  `payPassword` varchar(64) NOT NULL DEFAULT '' COMMENT '支付密码',
  `payPasswordSalt` varchar(64) NOT NULL DEFAULT '' COMMENT '支付密码Salt',
  `locale` varchar(20) DEFAULT NULL,
  `uri` varchar(64) NOT NULL DEFAULT '' COMMENT '用户URI',
  `nickname` varchar(64) NOT NULL COMMENT '用户名',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '头衔',
  `tags` varchar(255) NOT NULL DEFAULT '' COMMENT '标签',
  `type` varchar(32) NOT NULL COMMENT 'default默认为网站注册, weibo新浪微薄登录',
  `point` int(11) NOT NULL DEFAULT '0' COMMENT '积分',
  `coin` int(11) NOT NULL DEFAULT '0' COMMENT '金币',
  `smallAvatar` varchar(255) NOT NULL DEFAULT '' COMMENT '小头像',
  `mediumAvatar` varchar(255) NOT NULL DEFAULT '' COMMENT '中头像',
  `largeAvatar` varchar(255) NOT NULL DEFAULT '' COMMENT '大头像',
  `emailVerified` tinyint(1) NOT NULL DEFAULT '0' COMMENT '邮箱是否为已验证',
  `setup` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否初始化设置的，未初始化的可以设置邮箱、用户名。',
  `roles` varchar(255) NOT NULL COMMENT '用户角色',
  `promoted` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否为推荐',
  `promotedSeq` int(10) unsigned NOT NULL DEFAULT '0',
  `promotedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '推荐时间',
  `locked` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否被禁止',
  `lockDeadline` int(10) NOT NULL DEFAULT '0' COMMENT '帐号锁定期限',
  `consecutivePasswordErrorTimes` int(11) NOT NULL DEFAULT '0' COMMENT '帐号密码错误次数',
  `lastPasswordFailTime` int(10) NOT NULL DEFAULT '0',
  `loginTime` int(11) NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  `loginIp` varchar(64) NOT NULL DEFAULT '' COMMENT '最后登录IP',
  `loginSessionId` varchar(255) NOT NULL DEFAULT '' COMMENT '最后登录会话ID',
  `approvalTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '实名认证时间',
  `approvalStatus` enum('unapprove','approving','approved','approve_fail') NOT NULL DEFAULT 'unapprove' COMMENT '实名认证状态',
  `newMessageNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '未读私信数',
  `newNotificationNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '未读消息数',
  `createdIp` varchar(64) NOT NULL DEFAULT '' COMMENT '注册IP',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '注册时间',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  `inviteCode` varchar(255) DEFAULT NULL COMMENT '邀请码',
  `registeredWay` varchar(64) NOT NULL DEFAULT '' COMMENT '注册设备来源(web/ios/android)',
  `pwdInit` tinyint(1) NOT NULL DEFAULT '0' COMMENT '初始化密码',
  `postId` int(11) NOT NULL DEFAULT '0' COMMENT '岗位id',
  `readGuide` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否阅读新手引导',
  `orgIds` varchar(128) NOT NULL DEFAULT '0' COMMENT '部门ID',
  `orgCodes` varchar(255) NOT NULL DEFAULT '0' COMMENT '部门ID',
  `hireDate` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '入职时间',
  `distributorToken` varchar(255) NOT NULL DEFAULT '' COMMENT '分销平台token',
  `uuid` varchar(255) NOT NULL DEFAULT '' COMMENT '用户uuid',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `nickname` (`nickname`),
  UNIQUE KEY `uuid` (`uuid`),
  KEY `updatedTime` (`updatedTime`),
  KEY `user_type_index` (`type`),
  KEY `promoted` (`promoted`),
  KEY `distributorToken` (`distributorToken`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_active_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_active_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `userId` int(11) NOT NULL COMMENT '用户Id',
  `activeTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '激活时间',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `userId_createdTime` (`userId`,`createdTime`),
  KEY `createdTime` (`createdTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='活跃用户记录表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_approval`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_approval` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '用户认证ID',
  `userId` int(10) NOT NULL COMMENT '用户ID',
  `idcard` varchar(24) NOT NULL DEFAULT '' COMMENT '身份证号',
  `faceImg` varchar(500) NOT NULL DEFAULT '' COMMENT '认证正面图',
  `backImg` varchar(500) NOT NULL DEFAULT '' COMMENT '认证背面图',
  `truename` varchar(255) DEFAULT NULL COMMENT '真实姓名',
  `note` text COMMENT '认证信息',
  `status` enum('unapprove','approving','approved','approve_fail') NOT NULL COMMENT '是否通过：1是 0否',
  `operatorId` int(10) unsigned DEFAULT NULL COMMENT '审核人',
  `createdTime` int(10) NOT NULL DEFAULT '0' COMMENT '申请时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户认证表';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_bind`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_bind` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户绑定ID',
  `type` varchar(64) NOT NULL COMMENT '用户绑定类型',
  `fromId` varchar(32) NOT NULL COMMENT '来源方用户ID',
  `toId` int(10) unsigned NOT NULL COMMENT '被绑定的用户ID',
  `token` varchar(255) NOT NULL DEFAULT '' COMMENT 'oauth token',
  `refreshToken` varchar(255) NOT NULL DEFAULT '' COMMENT 'oauth refresh token',
  `expiredTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'token过期时间',
  `createdTime` int(10) unsigned NOT NULL COMMENT '绑定时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `type` (`type`,`fromId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_daily_learn_record`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_daily_learn_record` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL COMMENT '用户Id',
  `postId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '岗位Id',
  `courseId` int(10) unsigned NOT NULL COMMENT '课程Id',
  `classroomId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '班级Id',
  `categoryId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课程分类Id',
  `learnTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '总学习时间',
  `finishedTaskNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '完成任务数',
  `courseStatus` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '课程完成状态',
  `date` int(10) unsigned NOT NULL COMMENT '学习日期',
  `createdTime` int(10) unsigned NOT NULL COMMENT '记录添加时间',
  `updatedTime` int(10) unsigned NOT NULL COMMENT '记录更新时间',
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`),
  KEY `postId` (`postId`),
  KEY `categoryId` (`categoryId`),
  KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_field`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_field` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `fieldName` varchar(100) NOT NULL DEFAULT '',
  `title` varchar(1024) NOT NULL DEFAULT '',
  `seq` int(10) unsigned NOT NULL,
  `enabled` int(10) unsigned NOT NULL DEFAULT '0',
  `createdTime` int(100) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_fortune_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_fortune_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `number` int(10) NOT NULL,
  `action` varchar(20) NOT NULL,
  `note` varchar(255) NOT NULL DEFAULT '',
  `createdTime` int(11) NOT NULL,
  `type` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户组ID',
  `name` varchar(255) NOT NULL COMMENT '名称',
  `description` text COMMENT '描述',
  `code` varchar(255) NOT NULL DEFAULT '' COMMENT '用户组编码',
  `createdUserId` int(11) NOT NULL COMMENT '创建用户ID',
  `createdTime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户组';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_group_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_group_member` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `groupId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户组ID',
  `memberId` int(11) unsigned NOT NULL COMMENT '成员类型ID',
  `memberType` varchar(32) NOT NULL COMMENT '成员类型',
  `createdTime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户组成员';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_learn_statistics_daily`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_learn_statistics_daily` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL COMMENT '用户Id',
  `joinedClassroomNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '当天加入的班级数',
  `joinedCourseSetNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '当天加入的非班级课程数',
  `joinedCourseNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '当天加入的非班级计划数',
  `exitClassroomNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT ' 当天退出的班级数',
  `exitCourseSetNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '当天退出的非班级课程数',
  `exitCourseNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '当天退出的非班级计划数',
  `learnedSeconds` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学习时长',
  `finishedTaskNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT ' 当天学完的任务数量',
  `paidAmount` int(10) NOT NULL DEFAULT '0' COMMENT '支付金额',
  `refundAmount` int(10) NOT NULL DEFAULT '0' COMMENT '退款金额',
  `actualAmount` int(10) NOT NULL DEFAULT '0' COMMENT '实付金额',
  `recordTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '记录时间, 当天同步时间的0点',
  `isStorage` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否存储到total表',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`,`recordTime`),
  KEY `index_user_id` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_learn_statistics_total`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_learn_statistics_total` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL COMMENT '用户Id',
  `joinedClassroomNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '加入的班级数',
  `joinedCourseSetNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '加入的非班级课程数',
  `joinedCourseNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '加入的非班级计划数',
  `exitClassroomNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '退出的班级数',
  `exitCourseSetNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '退出的非班级课程数',
  `exitCourseNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '退出的非班级计划数',
  `learnedSeconds` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学习时长',
  `finishedTaskNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '学完的任务数量',
  `paidAmount` int(10) NOT NULL DEFAULT '0' COMMENT '支付金额',
  `refundAmount` int(10) NOT NULL DEFAULT '0' COMMENT '退款金额',
  `actualAmount` int(10) NOT NULL DEFAULT '0' COMMENT '实付金额',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`),
  KEY `index_user_id` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_org`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_org` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `userId` int(10) unsigned NOT NULL COMMENT '用户ID',
  `orgId` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '组织机构id',
  `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构内部编码',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`),
  KEY `orgCode` (`orgCode`),
  KEY `orgId` (`orgId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户组织机构关系';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_pay_agreement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_pay_agreement` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL COMMENT '用户Id',
  `type` int(8) NOT NULL DEFAULT '0' COMMENT '0:储蓄卡1:信用卡',
  `bankName` varchar(255) NOT NULL COMMENT '银行名称',
  `bankNumber` int(8) NOT NULL COMMENT '银行卡号',
  `userAuth` varchar(225) DEFAULT NULL COMMENT '用户授权',
  `bankAuth` varchar(225) NOT NULL COMMENT '银行授权码',
  `bankId` int(8) NOT NULL COMMENT '对应的银行Id',
  `updatedTime` int(10) NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  `createdTime` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户授权银行';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_profile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_profile` (
  `id` int(10) unsigned NOT NULL COMMENT '用户ID',
  `truename` varchar(255) NOT NULL DEFAULT '' COMMENT '真实姓名',
  `idcard` varchar(24) NOT NULL DEFAULT '' COMMENT '身份证号码',
  `gender` enum('male','female','secret') NOT NULL DEFAULT 'secret' COMMENT '性别',
  `iam` varchar(255) NOT NULL DEFAULT '' COMMENT '我是谁',
  `birthday` date DEFAULT NULL COMMENT '生日',
  `city` varchar(64) NOT NULL DEFAULT '' COMMENT '城市',
  `mobile` varchar(32) NOT NULL DEFAULT '' COMMENT '手机',
  `qq` varchar(32) NOT NULL DEFAULT '' COMMENT 'QQ',
  `signature` text COMMENT '签名',
  `about` text COMMENT '自我介绍',
  `company` varchar(255) NOT NULL DEFAULT '' COMMENT '公司',
  `job` varchar(255) NOT NULL DEFAULT '' COMMENT '工作',
  `school` varchar(255) NOT NULL DEFAULT '' COMMENT '学校',
  `class` varchar(255) NOT NULL DEFAULT '' COMMENT '班级',
  `weibo` varchar(255) NOT NULL DEFAULT '' COMMENT '微博',
  `weixin` varchar(255) NOT NULL DEFAULT '' COMMENT '微信',
  `isQQPublic` int(11) NOT NULL DEFAULT '0' COMMENT 'QQ号是否公开',
  `isWeixinPublic` int(11) NOT NULL DEFAULT '0' COMMENT '微信是否公开',
  `isWeiboPublic` int(11) NOT NULL DEFAULT '0' COMMENT '微博是否公开',
  `site` varchar(255) NOT NULL DEFAULT '' COMMENT '网站',
  `intField1` int(11) DEFAULT NULL,
  `intField2` int(11) DEFAULT NULL,
  `intField3` int(11) DEFAULT NULL,
  `intField4` int(11) DEFAULT NULL,
  `intField5` int(11) DEFAULT NULL,
  `dateField1` date DEFAULT NULL,
  `dateField2` date DEFAULT NULL,
  `dateField3` date DEFAULT NULL,
  `dateField4` date DEFAULT NULL,
  `dateField5` date DEFAULT NULL,
  `floatField1` float(10,2) DEFAULT NULL,
  `floatField2` float(10,2) DEFAULT NULL,
  `floatField3` float(10,2) DEFAULT NULL,
  `floatField4` float(10,2) DEFAULT NULL,
  `floatField5` float(10,2) DEFAULT NULL,
  `varcharField1` varchar(1024) DEFAULT NULL,
  `varcharField2` varchar(1024) DEFAULT NULL,
  `varcharField3` varchar(1024) DEFAULT NULL,
  `varcharField4` varchar(1024) DEFAULT NULL,
  `varcharField5` varchar(1024) DEFAULT NULL,
  `varcharField6` varchar(1024) DEFAULT NULL,
  `varcharField7` varchar(1024) DEFAULT NULL,
  `varcharField8` varchar(1024) DEFAULT NULL,
  `varcharField9` varchar(1024) DEFAULT NULL,
  `varcharField10` varchar(1024) DEFAULT NULL,
  `textField1` text,
  `textField2` text,
  `textField3` text,
  `textField4` text,
  `textField5` text,
  `textField6` text,
  `textField7` text,
  `textField8` text,
  `textField9` text,
  `textField10` text,
  `quick_entrance` text COMMENT '快速入口',
  `custom_column` text COMMENT '员工数据自定义列',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_secure_question`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_secure_question` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `securityQuestionCode` varchar(64) NOT NULL DEFAULT '' COMMENT '问题的code',
  `securityAnswer` varchar(64) NOT NULL DEFAULT '' COMMENT '安全问题的答案',
  `securityAnswerSalt` varchar(64) NOT NULL DEFAULT '' COMMENT '安全问题的答案Salt',
  `createdTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_token` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'TOKEN编号',
  `token` varchar(64) NOT NULL COMMENT 'TOKEN值',
  `userId` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'TOKEN关联的用户ID',
  `type` varchar(255) NOT NULL COMMENT 'TOKEN类型',
  `data` text NOT NULL COMMENT 'TOKEN数据',
  `times` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'TOKEN的校验次数限制(0表示不限制)',
  `remainedTimes` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'TOKE剩余校验次数',
  `expiredTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'TOKEN过期时间',
  `createdTime` int(10) unsigned NOT NULL COMMENT 'TOKEN创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`(60))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `xapi_activity_watch_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `xapi_activity_watch_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL COMMENT '用户ID',
  `activity_id` int(11) DEFAULT NULL COMMENT '教学活动ID',
  `course_id` int(11) DEFAULT NULL COMMENT '教学计划ID',
  `task_id` int(11) DEFAULT NULL COMMENT '任务ID',
  `watched_time` int(10) unsigned NOT NULL COMMENT '观看时长',
  `created_time` int(10) unsigned NOT NULL COMMENT '创建时间',
  `updated_time` int(10) unsigned NOT NULL COMMENT '更新时间',
  `is_push` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '是否推送',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `xapi_statement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `xapi_statement` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(64) NOT NULL,
  `version` varchar(32) NOT NULL DEFAULT '' COMMENT '版本号',
  `push_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上报时间',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属用户',
  `verb` varchar(32) NOT NULL DEFAULT '' COMMENT '用户行为',
  `target_id` int(10) DEFAULT NULL COMMENT '目标Id',
  `target_type` varchar(32) NOT NULL COMMENT '目标类型',
  `status` varchar(16) NOT NULL DEFAULT 'created' COMMENT '状态: created, pushing, pushed',
  `context` text COMMENT '上下文信息',
  `data` text COMMENT '数据',
  `created_time` int(10) unsigned NOT NULL COMMENT '创建时间',
  `occur_time` int(10) unsigned NOT NULL COMMENT '行为发生时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `xapi_statement_archive`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `xapi_statement_archive` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(64) NOT NULL,
  `version` varchar(32) NOT NULL DEFAULT '' COMMENT '版本号',
  `push_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上报时间',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属用户',
  `verb` varchar(32) NOT NULL DEFAULT '' COMMENT '用户行为',
  `target_id` int(10) DEFAULT NULL COMMENT '目标Id',
  `target_type` varchar(32) NOT NULL COMMENT '目标类型',
  `status` varchar(16) NOT NULL DEFAULT 'created' COMMENT '状态: created, pushing, pushed',
  `data` text COMMENT '数据',
  `occur_time` int(10) unsigned NOT NULL COMMENT '行为发生时间',
  `created_time` int(10) unsigned NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
