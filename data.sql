-- 香蕉CMS 数据库结构
-- Powered by https://xpornkit.com

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 管理员表
DROP TABLE IF EXISTS `xpk_admin`;
CREATE TABLE `xpk_admin` (
  `admin_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `admin_name` varchar(50) NOT NULL DEFAULT '' COMMENT '用户名',
  `admin_pwd` varchar(255) NOT NULL DEFAULT '' COMMENT '密码',
  `admin_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态',
  `admin_login_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '登录时间',
  `admin_login_ip` varchar(50) NOT NULL DEFAULT '' COMMENT '登录IP',
  `admin_login_num` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '登录次数',
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `admin_name` (`admin_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='管理员表';

-- 管理员由安装向导创建

-- 分类表
DROP TABLE IF EXISTS `xpk_type`;
CREATE TABLE `xpk_type` (
  `type_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type_pid` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '父ID',
  `type_name` varchar(50) NOT NULL DEFAULT '' COMMENT '分类名',
  `type_en` varchar(50) NOT NULL DEFAULT '' COMMENT '英文名',
  `type_sort` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '排序',
  `type_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态',
  `type_tpl` varchar(50) NOT NULL DEFAULT '' COMMENT '模板',
  `type_key` varchar(255) NOT NULL DEFAULT '' COMMENT '关键词',
  `type_des` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `type_extend` text COMMENT '扩展配置',
  PRIMARY KEY (`type_id`),
  KEY `type_pid` (`type_pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='分类表';

-- 默认分类
INSERT INTO `xpk_type` VALUES 
(1, 0, '电影', 'movie', 1, 1, '', '', '', NULL),
(2, 0, '电视剧', 'tv', 2, 1, '', '', '', NULL),
(3, 0, '综艺', 'variety', 3, 1, '', '', '', NULL),
(4, 0, '动漫', 'anime', 4, 1, '', '', '', NULL),
(5, 1, '动作片', 'action', 1, 1, '', '', '', NULL),
(6, 1, '喜剧片', 'comedy', 2, 1, '', '', '', NULL),
(7, 1, '爱情片', 'romance', 3, 1, '', '', '', NULL),
(8, 1, '科幻片', 'scifi', 4, 1, '', '', '', NULL),
(9, 2, '国产剧', 'cn-tv', 1, 1, '', '', '', NULL),
(10, 2, '韩剧', 'kr-tv', 2, 1, '', '', '', NULL),
(11, 2, '美剧', 'us-tv', 3, 1, '', '', '', NULL);

-- 文章分类表
DROP TABLE IF EXISTS `xpk_art_type`;
CREATE TABLE `xpk_art_type` (
  `type_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type_pid` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '父ID',
  `type_name` varchar(50) NOT NULL DEFAULT '' COMMENT '分类名',
  `type_sort` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '排序',
  `type_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态',
  PRIMARY KEY (`type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文章分类表';

-- 默认文章分类
INSERT INTO `xpk_art_type` VALUES 
(1, 0, '影视资讯', 1, 1),
(2, 0, '娱乐八卦', 2, 1),
(3, 0, '网站公告', 3, 1);

-- 视频表
DROP TABLE IF EXISTS `xpk_vod`;
CREATE TABLE `xpk_vod` (
  `vod_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vod_type_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '分类ID',
  `vod_name` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
  `vod_sub` varchar(255) NOT NULL DEFAULT '' COMMENT '副标题',
  `vod_en` varchar(255) NOT NULL DEFAULT '' COMMENT '英文名',
  `vod_slug` varchar(255) NOT NULL DEFAULT '' COMMENT 'URL别名',
  `vod_pic` varchar(255) NOT NULL DEFAULT '' COMMENT '封面图',
  `vod_pic_thumb` varchar(255) NOT NULL DEFAULT '' COMMENT '缩略图',
  `vod_actor` varchar(255) NOT NULL DEFAULT '' COMMENT '演员',
  `vod_director` varchar(255) NOT NULL DEFAULT '' COMMENT '导演',
  `vod_writer` varchar(255) NOT NULL DEFAULT '' COMMENT '编剧',
  `vod_year` varchar(10) NOT NULL DEFAULT '' COMMENT '年份',
  `vod_area` varchar(50) NOT NULL DEFAULT '' COMMENT '地区',
  `vod_lang` varchar(50) NOT NULL DEFAULT '' COMMENT '语言',
  `vod_score` decimal(3,1) NOT NULL DEFAULT 0.0 COMMENT '评分',
  `vod_hits` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '点击量',
  `vod_hits_day` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '日点击',
  `vod_hits_week` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '周点击',
  `vod_hits_month` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '月点击',
  `vod_up` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '顶',
  `vod_down` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '踩',
  `vod_remarks` varchar(100) NOT NULL DEFAULT '' COMMENT '备注',
  `vod_content` text COMMENT '简介',
  `vod_play_from` varchar(255) NOT NULL DEFAULT '' COMMENT '播放来源',
  `vod_play_url` text COMMENT '播放地址',
  `vod_down_from` varchar(255) NOT NULL DEFAULT '' COMMENT '下载来源',
  `vod_down_url` text COMMENT '下载地址',
  `vod_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态',
  `vod_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '更新时间',
  `vod_time_add` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '添加时间',
  PRIMARY KEY (`vod_id`),
  UNIQUE KEY `vod_slug` (`vod_slug`),
  KEY `vod_type_id` (`vod_type_id`),
  KEY `vod_time` (`vod_time`),
  KEY `vod_hits` (`vod_hits`),
  KEY `vod_status` (`vod_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='视频表';

-- 演员表
DROP TABLE IF EXISTS `xpk_actor`;
CREATE TABLE `xpk_actor` (
  `actor_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `actor_name` varchar(100) NOT NULL DEFAULT '' COMMENT '姓名',
  `actor_en` varchar(100) NOT NULL DEFAULT '' COMMENT '英文名',
  `actor_slug` varchar(100) NOT NULL DEFAULT '' COMMENT 'URL别名',
  `actor_pic` varchar(255) NOT NULL DEFAULT '' COMMENT '头像',
  `actor_sex` varchar(10) NOT NULL DEFAULT '' COMMENT '性别',
  `actor_area` varchar(50) NOT NULL DEFAULT '' COMMENT '地区',
  `actor_blood` varchar(10) NOT NULL DEFAULT '' COMMENT '血型',
  `actor_birthday` varchar(20) NOT NULL DEFAULT '' COMMENT '生日',
  `actor_height` varchar(10) NOT NULL DEFAULT '' COMMENT '身高',
  `actor_weight` varchar(10) NOT NULL DEFAULT '' COMMENT '体重',
  `actor_content` text COMMENT '简介',
  `actor_hits` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '点击量',
  `actor_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态',
  `actor_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`actor_id`),
  UNIQUE KEY `actor_slug` (`actor_slug`),
  KEY `actor_name` (`actor_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='演员表';

-- 文章表
DROP TABLE IF EXISTS `xpk_art`;
CREATE TABLE `xpk_art` (
  `art_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `art_type_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '分类ID',
  `art_name` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
  `art_slug` varchar(255) NOT NULL DEFAULT '' COMMENT 'URL别名',
  `art_pic` varchar(255) NOT NULL DEFAULT '' COMMENT '封面图',
  `art_author` varchar(50) NOT NULL DEFAULT '' COMMENT '作者',
  `art_from` varchar(100) NOT NULL DEFAULT '' COMMENT '来源',
  `art_content` text COMMENT '内容',
  `art_hits` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '点击量',
  `art_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态',
  `art_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`art_id`),
  UNIQUE KEY `art_slug` (`art_slug`),
  KEY `art_type_id` (`art_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文章表';

-- 用户表
DROP TABLE IF EXISTS `xpk_user`;
CREATE TABLE `xpk_user` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_name` varchar(50) NOT NULL DEFAULT '' COMMENT '用户名',
  `user_pwd` varchar(255) NOT NULL DEFAULT '' COMMENT '密码',
  `user_nick_name` varchar(50) NOT NULL DEFAULT '' COMMENT '昵称',
  `user_email` varchar(100) NOT NULL DEFAULT '' COMMENT '邮箱',
  `user_phone` varchar(20) NOT NULL DEFAULT '' COMMENT '手机',
  `user_pic` varchar(255) NOT NULL DEFAULT '' COMMENT '头像',
  `user_points` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '积分',
  `user_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态',
  `user_reg_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '注册时间',
  `user_reg_ip` varchar(50) NOT NULL DEFAULT '' COMMENT '注册IP',
  `user_login_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '登录时间',
  `user_login_ip` varchar(50) NOT NULL DEFAULT '' COMMENT '登录IP',
  `user_login_num` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '登录次数',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_name` (`user_name`),
  KEY `user_email` (`user_email`),
  KEY `user_reg_ip` (`user_reg_ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户表';

-- 配置表
DROP TABLE IF EXISTS `xpk_config`;
CREATE TABLE `xpk_config` (
  `config_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `config_name` varchar(50) NOT NULL DEFAULT '' COMMENT '配置名',
  `config_value` text COMMENT '配置值',
  PRIMARY KEY (`config_id`),
  UNIQUE KEY `config_name` (`config_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='配置表';

-- 默认配置
INSERT INTO `xpk_config` VALUES 
(1, 'site_name', '香蕉影视'),
(2, 'site_url', 'http://localhost'),
(3, 'site_keywords', '香蕉CMS,BananaCMS,免费影视CMS'),
(4, 'site_description', '香蕉CMS - 轻量级影视内容管理系统');

-- 采集站表
DROP TABLE IF EXISTS `xpk_collect`;
CREATE TABLE `xpk_collect` (
  `collect_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `collect_name` varchar(100) NOT NULL DEFAULT '' COMMENT '名称',
  `collect_api` varchar(500) NOT NULL DEFAULT '' COMMENT 'API地址',
  `collect_type` varchar(10) NOT NULL DEFAULT 'json' COMMENT '数据格式',
  `collect_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态',
  `collect_filter` varchar(500) NOT NULL DEFAULT '' COMMENT '过滤关键词',
  `collect_param` varchar(255) NOT NULL DEFAULT '' COMMENT '附加参数',
  `collect_bind` text COMMENT '分类绑定JSON',
  PRIMARY KEY (`collect_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='采集站表';

-- 操作日志表
DROP TABLE IF EXISTS `xpk_admin_log`;
CREATE TABLE `xpk_admin_log` (
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '管理员ID',
  `admin_name` varchar(50) NOT NULL DEFAULT '' COMMENT '管理员名',
  `log_action` varchar(50) NOT NULL DEFAULT '' COMMENT '操作类型',
  `log_module` varchar(50) NOT NULL DEFAULT '' COMMENT '操作模块',
  `log_content` varchar(500) NOT NULL DEFAULT '' COMMENT '操作内容',
  `log_ip` varchar(50) NOT NULL DEFAULT '' COMMENT 'IP地址',
  `log_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '操作时间',
  PRIMARY KEY (`log_id`),
  KEY `admin_id` (`admin_id`),
  KEY `log_time` (`log_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='操作日志表';

-- 友情链接表
DROP TABLE IF EXISTS `xpk_link`;
CREATE TABLE `xpk_link` (
  `link_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `link_name` varchar(100) NOT NULL DEFAULT '' COMMENT '网站名称',
  `link_url` varchar(255) NOT NULL DEFAULT '' COMMENT '网站地址',
  `link_logo` varchar(255) NOT NULL DEFAULT '' COMMENT 'Logo图片',
  `link_contact` varchar(100) NOT NULL DEFAULT '' COMMENT '联系方式',
  `link_sort` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '排序',
  `link_status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态:0待审核,1已通过,2已拒绝',
  `link_type` tinyint(1) NOT NULL DEFAULT 0 COMMENT '类型:0申请,1手动添加',
  `link_check_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '最后检测时间',
  `link_check_status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '检测状态:0未检测,1有回链,2无回链',
  `link_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '添加时间',
  PRIMARY KEY (`link_id`),
  KEY `link_status` (`link_status`),
  KEY `link_sort` (`link_sort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='友情链接表';

-- 广告表
DROP TABLE IF EXISTS `xpk_ad`;
CREATE TABLE `xpk_ad` (
  `ad_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ad_title` varchar(100) NOT NULL DEFAULT '' COMMENT '广告名称',
  `ad_position` varchar(50) NOT NULL DEFAULT '' COMMENT '广告位置',
  `ad_type` varchar(20) NOT NULL DEFAULT 'image' COMMENT '类型:image/code/video/text',
  `ad_image` varchar(500) NOT NULL DEFAULT '' COMMENT '图片地址',
  `ad_link` varchar(500) NOT NULL DEFAULT '' COMMENT '跳转链接',
  `ad_code` text COMMENT '广告代码',
  `ad_video` varchar(500) NOT NULL DEFAULT '' COMMENT '视频地址',
  `ad_duration` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '视频时长(秒)',
  `ad_skip_time` int(10) unsigned NOT NULL DEFAULT 5 COMMENT '跳过时间(秒)',
  `ad_sort` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '排序',
  `ad_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态:0禁用,1启用',
  `ad_start_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '开始时间',
  `ad_end_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '结束时间',
  `ad_shows` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '展示次数',
  `ad_clicks` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '点击次数',
  `ad_remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `ad_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '添加时间',
  PRIMARY KEY (`ad_id`),
  KEY `ad_position` (`ad_position`),
  KEY `ad_status` (`ad_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='广告表';

-- 评论表
DROP TABLE IF EXISTS `xpk_comment`;
CREATE TABLE `xpk_comment` (
  `comment_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `comment_type` varchar(20) NOT NULL DEFAULT 'vod' COMMENT '类型:vod/art',
  `target_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '目标ID',
  `user_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '用户ID',
  `parent_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '父评论ID(楼中楼)',
  `reply_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '回复的评论ID',
  `comment_content` text COMMENT '评论内容',
  `comment_up` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '点赞数',
  `comment_down` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '踩数',
  `comment_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态:0待审核,1通过,2拒绝',
  `comment_ip` varchar(50) NOT NULL DEFAULT '' COMMENT 'IP地址',
  `comment_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '评论时间',
  PRIMARY KEY (`comment_id`),
  KEY `target` (`comment_type`, `target_id`),
  KEY `parent_id` (`parent_id`),
  KEY `user_id` (`user_id`),
  KEY `comment_status` (`comment_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='评论表';

-- 评论投票表
DROP TABLE IF EXISTS `xpk_comment_vote`;
CREATE TABLE `xpk_comment_vote` (
  `vote_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `comment_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '评论ID',
  `user_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '用户ID',
  `vote_type` varchar(10) NOT NULL DEFAULT 'up' COMMENT '类型:up/down',
  `vote_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '投票时间',
  PRIMARY KEY (`vote_id`),
  UNIQUE KEY `comment_user` (`comment_id`, `user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='评论投票表';

-- 评分表
DROP TABLE IF EXISTS `xpk_score`;
CREATE TABLE `xpk_score` (
  `score_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `score_type` varchar(20) NOT NULL DEFAULT 'vod' COMMENT '类型:vod/art',
  `target_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '目标ID',
  `user_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '用户ID(0为游客)',
  `score` tinyint(2) unsigned NOT NULL DEFAULT 0 COMMENT '评分1-10',
  `score_ip` varchar(50) NOT NULL DEFAULT '' COMMENT 'IP地址',
  `score_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '评分时间',
  PRIMARY KEY (`score_id`),
  UNIQUE KEY `type_target_user` (`score_type`, `target_id`, `user_id`),
  KEY `target` (`score_type`, `target_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='评分表';

-- 短视频/短剧表
DROP TABLE IF EXISTS `xpk_short`;
CREATE TABLE `xpk_short` (
  `short_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `short_type` varchar(20) NOT NULL DEFAULT 'video' COMMENT '类型:video短视频/drama短剧',
  `short_name` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
  `short_pic` varchar(500) NOT NULL DEFAULT '' COMMENT '封面图(竖版)',
  `short_url` varchar(500) NOT NULL DEFAULT '' COMMENT '视频地址(短视频用)',
  `short_duration` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '时长(秒)',
  `short_desc` text COMMENT '简介',
  `short_tags` varchar(255) NOT NULL DEFAULT '' COMMENT '标签',
  `category_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '分类ID',
  `short_hits` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '播放量',
  `short_likes` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '点赞数',
  `short_comments` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '评论数',
  `short_shares` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '分享数',
  `short_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态:0下架,1上架',
  `short_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '更新时间',
  `short_time_add` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '添加时间',
  PRIMARY KEY (`short_id`),
  KEY `short_type` (`short_type`),
  KEY `category_id` (`category_id`),
  KEY `short_status` (`short_status`),
  KEY `short_time` (`short_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='短视频/短剧表';

-- 短剧剧集表
DROP TABLE IF EXISTS `xpk_short_episode`;
CREATE TABLE `xpk_short_episode` (
  `episode_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `short_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '短剧ID',
  `episode_name` varchar(100) NOT NULL DEFAULT '' COMMENT '集数标题',
  `episode_url` varchar(500) NOT NULL DEFAULT '' COMMENT '视频地址',
  `episode_pic` varchar(500) NOT NULL DEFAULT '' COMMENT '封面图',
  `episode_duration` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '时长(秒)',
  `episode_sort` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '排序',
  `episode_hits` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '播放量',
  `episode_free` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否免费:0付费,1免费',
  `episode_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '添加时间',
  PRIMARY KEY (`episode_id`),
  KEY `short_id` (`short_id`),
  KEY `episode_sort` (`episode_sort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='短剧剧集表';

-- 统计日志表
DROP TABLE IF EXISTS `xpk_stats_log`;
CREATE TABLE `xpk_stats_log` (
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `log_type` varchar(20) NOT NULL DEFAULT '' COMMENT '类型:visit/vod/play/search',
  `target_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '目标ID',
  `log_ip` varchar(50) NOT NULL DEFAULT '' COMMENT 'IP地址',
  `log_ua` varchar(500) NOT NULL DEFAULT '' COMMENT 'User-Agent',
  `log_referer` varchar(500) NOT NULL DEFAULT '' COMMENT '来源页面',
  `log_date` date NOT NULL COMMENT '日期',
  `log_pv` int(10) unsigned NOT NULL DEFAULT 1 COMMENT 'PV次数',
  `log_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '最后访问时间',
  PRIMARY KEY (`log_id`),
  UNIQUE KEY `type_target_ip_date` (`log_type`, `target_id`, `log_ip`, `log_date`),
  KEY `log_date` (`log_date`),
  KEY `log_type` (`log_type`),
  KEY `log_time` (`log_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='统计日志表';

-- 用户收藏表
DROP TABLE IF EXISTS `xpk_user_favorite`;
CREATE TABLE `xpk_user_favorite` (
  `fav_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '用户ID',
  `vod_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '视频ID',
  `fav_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '收藏时间',
  PRIMARY KEY (`fav_id`),
  UNIQUE KEY `user_vod` (`user_id`, `vod_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户收藏表';

-- 用户观看历史表
DROP TABLE IF EXISTS `xpk_user_history`;
CREATE TABLE `xpk_user_history` (
  `history_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '用户ID',
  `vod_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '视频ID',
  `sid` int(10) unsigned NOT NULL DEFAULT 1 COMMENT '播放源',
  `nid` int(10) unsigned NOT NULL DEFAULT 1 COMMENT '集数',
  `progress` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '播放进度(秒)',
  `duration` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '总时长(秒)',
  `watch_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '观看时间',
  PRIMARY KEY (`history_id`),
  UNIQUE KEY `user_vod` (`user_id`, `vod_id`),
  KEY `user_id` (`user_id`),
  KEY `watch_time` (`watch_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户观看历史表';

-- 搜索日志表
DROP TABLE IF EXISTS `xpk_search_log`;
CREATE TABLE `xpk_search_log` (
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `keyword` varchar(100) NOT NULL DEFAULT '' COMMENT '搜索词',
  `search_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '搜索时间',
  `search_ip` varchar(50) NOT NULL DEFAULT '' COMMENT 'IP地址',
  PRIMARY KEY (`log_id`),
  KEY `keyword` (`keyword`),
  KEY `search_time` (`search_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='搜索日志表';

SET FOREIGN_KEY_CHECKS = 1;
