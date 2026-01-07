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
  KEY `type_pid` (`type_pid`),
  KEY `type_en` (`type_en`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='分类表';

-- 分类数据由采集自动创建

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
  `vod_type_id_1` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '一级分类ID',
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
  `vod_letter` varchar(1) NOT NULL DEFAULT '' COMMENT '首字母',
  `vod_tag` varchar(255) NOT NULL DEFAULT '' COMMENT '标签',
  `vod_class` varchar(255) NOT NULL DEFAULT '' COMMENT '扩展分类',
  `vod_isend` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否完结:0连载,1完结',
  `vod_serial` varchar(20) NOT NULL DEFAULT '' COMMENT '连载集数',
  `vod_total` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '总集数',
  `vod_weekday` varchar(10) NOT NULL DEFAULT '' COMMENT '更新日',
  `vod_state` varchar(50) NOT NULL DEFAULT '' COMMENT '资源状态',
  `vod_version` varchar(50) NOT NULL DEFAULT '' COMMENT '版本',
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
  `vod_play_url` mediumtext COMMENT '播放地址',
  `vod_down_from` varchar(255) NOT NULL DEFAULT '' COMMENT '下载来源',
  `vod_down_url` text COMMENT '下载地址',
  `vod_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态',
  `vod_lock` tinyint(1) NOT NULL DEFAULT 0 COMMENT '锁定:0未锁,1已锁',
  `vod_ai_rewrite` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'AI改写:0未处理,1已改写,2失败',
  `vod_collect_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '采集站ID',
  `vod_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '更新时间',
  `vod_time_add` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '添加时间',
  PRIMARY KEY (`vod_id`),
  UNIQUE KEY `vod_slug` (`vod_slug`),
  KEY `vod_type_id` (`vod_type_id`),
  KEY `vod_type_id_1` (`vod_type_id_1`),
  KEY `vod_letter` (`vod_letter`),
  KEY `vod_isend` (`vod_isend`),
  KEY `vod_time` (`vod_time`),
  KEY `vod_hits` (`vod_hits`),
  KEY `vod_status` (`vod_status`),
  KEY `vod_collect_id` (`vod_collect_id`),
  KEY `vod_ai_rewrite` (`vod_ai_rewrite`),
  FULLTEXT KEY `ft_search` (`vod_name`, `vod_sub`, `vod_actor`) WITH PARSER ngram
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
  `user_pic` varchar(255) NOT NULL DEFAULT '' COMMENT '头像',
  `user_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态',
  `user_vip_level` tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT 'VIP等级:0普通用户',
  `user_vip_expire` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'VIP过期时间',
  `user_points` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '积分余额',
  `user_daily_views` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '今日观看次数',
  `user_daily_date` date DEFAULT NULL COMMENT '观看次数日期',
  `user_invite_code` varchar(10) DEFAULT NULL COMMENT '邀请码',
  `user_invited_by` int(10) unsigned DEFAULT NULL COMMENT '邀请人ID',
  `user_reg_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '注册时间',
  `user_reg_ip` varchar(50) NOT NULL DEFAULT '' COMMENT '注册IP',
  `user_login_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '登录时间',
  `user_login_ip` varchar(50) NOT NULL DEFAULT '' COMMENT '登录IP',
  `user_login_num` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '登录次数',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_name` (`user_name`),
  UNIQUE KEY `user_invite_code` (`user_invite_code`),
  KEY `user_email` (`user_email`),
  KEY `user_reg_ip` (`user_reg_ip`),
  KEY `user_vip_level` (`user_vip_level`)
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
(3, 'site_keywords', '香蕉CMS,BananaCMS,免费影视CMS,在线观看'),
(4, 'site_description', '香蕉CMS - 轻量级影视内容管理系统，提供最新电影、电视剧、综艺、动漫在线观看'),
(5, 'url_mode', '4'),
(6, 'seo_title_vod_detail', '{name}在线观看 - {sitename}'),
(7, 'seo_keywords_vod_detail', '{name},{actor},{type},{year},{area}'),
(8, 'seo_description_vod_detail', '{name}由{actor}主演，{year}年{area}{type}，{description}'),
(9, 'seo_title_type', '{name}大全_最新{name}排行榜 - {sitename}'),
(10, 'seo_keywords_type', '{name},{name}大全,最新{name},{name}排行榜'),
(11, 'seo_description_type', '{name}分类视频大全，最新{name}在线观看 - {sitename}'),
(12, 'seo_title_hot', '热门视频_最受欢迎视频推荐 - {sitename}'),
(13, 'seo_keywords_hot', '热门视频,热门电影,热门电视剧,推荐视频'),
(14, 'seo_description_hot', '最受欢迎的热门视频推荐，精选优质内容 - {sitename}'),
(15, 'seo_title_actor_detail', '{name}个人资料_主演作品 - {sitename}'),
(16, 'seo_title_art_detail', '{name} - {sitename}'),
(17, 'comment_audit', '0'),
(18, 'comment_guest', '1'),
(19, 'user_register', '1'),
(20, 'user_register_limit', '5'),
(21, 'ad_url_check', '1'),
(22, 'ad_allowed_domains', ''),
(23, 'ad_allowed_protocols', 'https,http'),
(24, 'ad_max_url_length', '500'),
(25, 'ad_blocked_extensions', 'exe,bat,sh,php,js'),
(26, 'ad_content_filter', '1'),
(27, 'security_csp_enabled', '1'),
(28, 'security_csp_script_src', "'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com"),
(29, 'security_csp_style_src', "'self' 'unsafe-inline' https://cdn.tailwindcss.com https://fonts.googleapis.com"),
(30, 'security_csp_img_src', "'self' data: https: http:"),
(31, 'security_frame_options', 'SAMEORIGIN'),
(32, 'security_xss_protection', '1'),
(33, 'security_referrer_policy', 'strict-origin-when-cross-origin'),
(34, 'security_hsts_max_age', '31536000'),
(35, 'security_hsts_include_subdomains', '1'),
(36, 'security_hsts_preload', '0'),
(37, 'security_permissions_policy', 'camera=(), microphone=(), geolocation=(), payment=()'),
(38, 'security_coep_enabled', '0'),
(39, 'security_coop_enabled', '0'),
(40, 'security_coop_policy', 'same-origin'),
(41, 'security_corp_enabled', '0'),
(42, 'security_corp_policy', 'same-origin'),
(43, 'security_hide_server_info', '1');

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
  `collect_progress` text COMMENT '采集进度JSON',
  `collect_opt_hits_start` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '随机点击量起始',
  `collect_opt_hits_end` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '随机点击量结束',
  `collect_opt_score_start` decimal(3,1) NOT NULL DEFAULT 0.0 COMMENT '随机评分起始',
  `collect_opt_score_end` decimal(3,1) NOT NULL DEFAULT 0.0 COMMENT '随机评分结束',
  `collect_opt_dup_rule` varchar(50) NOT NULL DEFAULT 'name' COMMENT '重复判断规则:name/name_type/name_year',
  `collect_opt_update_fields` varchar(255) NOT NULL DEFAULT 'remarks,content,play' COMMENT '允许更新的字段',
  `collect_opt_play_merge` tinyint(1) NOT NULL DEFAULT 0 COMMENT '播放地址合并:0覆盖,1合并',
  PRIMARY KEY (`collect_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='采集站表';

-- 采集分类绑定表
DROP TABLE IF EXISTS `xpk_collect_bind`;
CREATE TABLE `xpk_collect_bind` (
  `bind_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `collect_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '采集站ID(0为全局)',
  `remote_type_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '远程分类ID',
  `remote_type_name` varchar(100) NOT NULL DEFAULT '' COMMENT '远程分类名称',
  `local_type_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '本地分类ID',
  PRIMARY KEY (`bind_id`),
  UNIQUE KEY `collect_remote` (`collect_id`, `remote_type_id`),
  KEY `remote_type_id` (`remote_type_id`),
  KEY `local_type_id` (`local_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='采集分类绑定表';

-- 采集日志表
DROP TABLE IF EXISTS `xpk_collect_log`;
CREATE TABLE `xpk_collect_log` (
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `collect_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '采集站ID',
  `collect_name` varchar(100) NOT NULL DEFAULT '' COMMENT '采集站名称',
  `log_type` varchar(20) NOT NULL DEFAULT 'manual' COMMENT '类型:manual手动/cron定时',
  `log_mode` varchar(20) NOT NULL DEFAULT 'add' COMMENT '模式:add/update/all',
  `log_pages` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '采集页数',
  `log_added` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '新增数量',
  `log_updated` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '更新数量',
  `log_skipped` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '跳过数量',
  `log_duration` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '耗时(秒)',
  `log_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态:0失败,1成功,2进行中',
  `log_message` varchar(500) NOT NULL DEFAULT '' COMMENT '消息/错误信息',
  `log_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '开始时间',
  PRIMARY KEY (`log_id`),
  KEY `collect_id` (`collect_id`),
  KEY `log_time` (`log_time`),
  KEY `log_type` (`log_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='采集日志表';

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

-- 单页面表
DROP TABLE IF EXISTS `xpk_page`;
CREATE TABLE `xpk_page` (
  `page_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `page_slug` varchar(50) NOT NULL DEFAULT '' COMMENT '页面标识',
  `page_title` varchar(100) NOT NULL DEFAULT '' COMMENT '页面标题',
  `page_content` mediumtext COMMENT '页面内容',
  `page_sort` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '排序',
  `page_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态:0禁用,1启用',
  `page_footer` tinyint(1) NOT NULL DEFAULT 1 COMMENT '底部显示:0不显示,1显示',
  PRIMARY KEY (`page_id`),
  UNIQUE KEY `page_slug` (`page_slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='单页面表';

-- 默认单页数据
INSERT INTO `xpk_page` (`page_slug`, `page_title`, `page_content`, `page_sort`, `page_status`, `page_footer`) VALUES
('about', '关于我们', '<h2>关于我们</h2><p>欢迎访问本站！我们致力于为用户提供优质的视频内容服务。</p><p>如有任何问题或建议，欢迎联系我们。</p>', 1, 1, 1),
('contact', '联系方式', '<h2>联系方式</h2><p>如需联系我们，请通过以下方式：</p><ul><li>邮箱：admin@example.com</li></ul><p>我们会尽快回复您的消息。</p>', 2, 1, 1),
('disclaimer', '免责声明', '<h2>免责声明</h2><p>本站所有内容均来自互联网，仅供学习交流使用。</p><p>本站不存储任何视频文件，所有视频均由第三方提供。</p><p>如有侵权，请联系我们删除。</p>', 3, 1, 1),
('terms', '服务条款', '<h2>服务条款</h2><p>欢迎使用本站服务。使用本站即表示您同意以下条款：</p><h3>1. 服务说明</h3><p>本站提供视频内容浏览服务，所有内容均来自互联网。</p><h3>2. 用户行为</h3><p>用户应遵守相关法律法规，不得利用本站从事违法活动。</p><h3>3. 知识产权</h3><p>本站尊重知识产权，如有侵权请联系我们删除。</p><h3>4. 免责声明</h3><p>本站不对内容的准确性、完整性作任何保证。</p><h3>5. 条款修改</h3><p>本站保留随时修改服务条款的权利。</p>', 4, 1, 0),
('privacy', '隐私政策', '<h2>隐私政策</h2><p>我们重视您的隐私保护。</p><h3>1. 信息收集</h3><p>我们可能收集您的注册信息、浏览记录等。</p><h3>2. 信息使用</h3><p>收集的信息仅用于提供和改进服务。</p><h3>3. 信息保护</h3><p>我们采取合理措施保护您的个人信息安全。</p><h3>4. Cookie使用</h3><p>本站使用Cookie来改善用户体验。</p>', 5, 1, 0),
('dmca', 'DMCA版权声明', '<h2>DMCA版权声明</h2><p>本站尊重并保护知识产权，根据《数字千年版权法》(DMCA)的规定，我们将对涉嫌侵权内容采取相应措施。</p><h3>版权投诉</h3><p>如果您认为本站上的内容侵犯了您的版权，请向我们提供以下信息：</p><ul><li>您声称被侵权的版权作品的描述</li><li>涉嫌侵权内容在本站的具体位置（URL链接）</li><li>您的联系方式（地址、电话、邮箱）</li><li>您声明善意相信该内容的使用未经版权所有者授权</li><li>您声明投诉信息准确无误</li><li>您的签名（电子签名或手写签名）</li></ul><h3>处理流程</h3><p>收到有效的版权投诉后，我们将在24-48小时内进行审核处理。</p><h3>联系方式</h3><p>请将版权投诉发送至：admin@example.com</p><p>我们承诺认真对待每一份投诉，并依法保护版权所有者的合法权益。</p>', 6, 1, 1);

-- 播放器表
DROP TABLE IF EXISTS `xpk_player`;
CREATE TABLE `xpk_player` (
  `player_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `player_name` varchar(50) NOT NULL DEFAULT '' COMMENT '播放器名称(中文)',
  `player_code` varchar(50) NOT NULL DEFAULT '' COMMENT '播放器标识(英文)',
  `player_parse` varchar(500) NOT NULL DEFAULT '' COMMENT '解析接口地址',
  `player_sort` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '排序',
  `player_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态:0禁用,1启用',
  `player_tip` varchar(255) NOT NULL DEFAULT '' COMMENT '提示信息',
  PRIMARY KEY (`player_id`),
  UNIQUE KEY `player_code` (`player_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='播放器表';

-- 默认播放器数据
INSERT INTO `xpk_player` (`player_name`, `player_code`, `player_parse`, `player_sort`, `player_status`, `player_tip`) VALUES
('M3U8播放器', 'm3u8', '', 1, 1, '支持m3u8格式视频'),
('MP4播放器', 'mp4', '', 2, 1, '支持mp4格式视频'),
('iframe嵌入', 'iframe', '', 3, 1, '直接嵌入播放页面'),
('量子资源', 'lzm3u8', '', 10, 1, '量子资源m3u8'),
('红牛资源', 'hnm3u8', '', 11, 1, '红牛资源m3u8'),
('光速资源', 'gsm3u8', '', 12, 1, '光速资源m3u8'),
('暴风资源', 'bfzym3u8', '', 13, 1, '暴风资源m3u8'),
('无尽资源', 'wjm3u8', '', 14, 1, '无尽资源m3u8'),
('酷点资源', 'kdm3u8', '', 15, 1, '酷点资源m3u8'),
('新浪资源', 'xlm3u8', '', 16, 1, '新浪资源m3u8'),
('优酷', 'youku', '', 20, 1, '优酷视频'),
('爱奇艺', 'iqiyi', '', 21, 1, '爱奇艺视频'),
('腾讯', 'qq', '', 22, 1, '腾讯视频'),
('芒果', 'mgtv', '', 23, 1, '芒果TV'),
('哔哩哔哩', 'bilibili', '', 24, 1, 'B站视频');

SET FOREIGN_KEY_CHECKS = 1;


-- 转码任务表
DROP TABLE IF EXISTS `xpk_transcode`;
CREATE TABLE `xpk_transcode` (
  `transcode_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vod_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '关联视频ID',
  `source_file` varchar(500) NOT NULL DEFAULT '' COMMENT '源文件路径',
  `output_dir` varchar(500) NOT NULL DEFAULT '' COMMENT '输出目录',
  `transcode_status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态:0待处理,1处理中,2完成,3失败',
  `transcode_progress` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '进度百分比',
  `duration` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '视频时长(秒)',
  `resolution` varchar(20) NOT NULL DEFAULT '' COMMENT '分辨率',
  `bitrate` varchar(20) NOT NULL DEFAULT '' COMMENT '码率',
  `encrypt_key` varchar(64) NOT NULL DEFAULT '' COMMENT '加密密钥(hex)',
  `m3u8_url` varchar(500) NOT NULL DEFAULT '' COMMENT '生成的m3u8地址',
  `error_msg` text COMMENT '错误信息',
  `created_at` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
  `updated_at` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '更新时间',
  `finished_at` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '完成时间',
  PRIMARY KEY (`transcode_id`),
  KEY `idx_vod` (`vod_id`),
  KEY `idx_status` (`transcode_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='转码任务表';

-- 分片上传临时表
DROP TABLE IF EXISTS `xpk_upload_chunk`;
CREATE TABLE `xpk_upload_chunk` (
  `chunk_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `upload_id` varchar(64) NOT NULL DEFAULT '' COMMENT '上传标识',
  `chunk_index` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '分片序号',
  `chunk_path` varchar(500) NOT NULL DEFAULT '' COMMENT '分片路径',
  `total_chunks` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '总分片数',
  `file_name` varchar(255) NOT NULL DEFAULT '' COMMENT '原文件名',
  `file_size` bigint(20) unsigned NOT NULL DEFAULT 0 COMMENT '文件总大小',
  `created_at` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`chunk_id`),
  UNIQUE KEY `uk_upload_chunk` (`upload_id`, `chunk_index`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='分片上传临时表';

-- 转码广告表
DROP TABLE IF EXISTS `xpk_transcode_ad`;
CREATE TABLE `xpk_transcode_ad` (
  `ad_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ad_name` varchar(100) NOT NULL DEFAULT '' COMMENT '广告名称',
  `ad_position` varchar(20) NOT NULL DEFAULT 'head' COMMENT '位置:head片头,middle片中,tail片尾',
  `ad_file` varchar(500) NOT NULL DEFAULT '' COMMENT '广告视频路径',
  `ad_duration` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '广告时长(秒)',
  `ad_sort` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '排序',
  `ad_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态:0禁用,1启用',
  `created_at` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
  `updated_at` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`ad_id`),
  KEY `idx_position` (`ad_position`, `ad_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='转码广告表';

-- 用户操作日志表
DROP TABLE IF EXISTS `xpk_user_logs`;
CREATE TABLE `xpk_user_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '日志ID',
  `log_time` datetime NOT NULL COMMENT '操作时间',
  `log_level` varchar(20) NOT NULL DEFAULT 'info' COMMENT '日志级别',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID，0表示游客',
  `user_ip` varchar(45) NOT NULL DEFAULT '' COMMENT '用户IP地址',
  `log_action` varchar(100) NOT NULL DEFAULT '' COMMENT '操作类型',
  `log_uri` varchar(500) NOT NULL DEFAULT '' COMMENT '请求URI',
  `log_data` text COMMENT '操作数据JSON',
  `user_agent` varchar(500) NOT NULL DEFAULT '' COMMENT '用户代理',
  PRIMARY KEY (`log_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_log_time` (`log_time`),
  KEY `idx_log_action` (`log_action`),
  KEY `idx_user_ip` (`user_ip`),
  KEY `idx_log_level` (`log_level`),
  KEY `idx_user_time` (`user_id`, `log_time`),
  KEY `idx_action_time` (`log_action`, `log_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户操作日志表';


-- =============================================
-- 支付系统相关表
-- =============================================

-- 支付通道表
DROP TABLE IF EXISTS `xpk_payment_channel`;
CREATE TABLE `xpk_payment_channel` (
  `channel_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `channel_code` varchar(50) NOT NULL DEFAULT '' COMMENT '通道编码(唯一标识)',
  `channel_name` varchar(100) NOT NULL DEFAULT '' COMMENT '通道名称',
  `channel_type` varchar(20) NOT NULL DEFAULT 'gateway' COMMENT '通道类型:gateway网关',
  `gateway_url` varchar(500) NOT NULL DEFAULT '' COMMENT '网关地址',
  `query_url` varchar(500) NOT NULL DEFAULT '' COMMENT '订单查询地址',
  `merchant_id` varchar(100) NOT NULL DEFAULT '' COMMENT '商户ID/PID',
  `merchant_key` varchar(255) NOT NULL DEFAULT '' COMMENT '商户密钥',
  `extra_config` text COMMENT '额外配置(JSON)',
  `support_methods` varchar(100) NOT NULL DEFAULT 'alipay' COMMENT '支持的支付方式:alipay,wechat',
  `fee_rate` decimal(5,4) NOT NULL DEFAULT 0.0000 COMMENT '手续费率',
  `min_amount` decimal(10,2) NOT NULL DEFAULT 0.01 COMMENT '最小金额',
  `max_amount` decimal(10,2) NOT NULL DEFAULT 50000.00 COMMENT '最大金额',
  `daily_limit` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT '每日限额(0=不限)',
  `weight` int(10) unsigned NOT NULL DEFAULT 100 COMMENT '权重(用于轮询)',
  `channel_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态:0禁用,1启用',
  `channel_sort` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '排序',
  `channel_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
  `channel_update` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`channel_id`),
  UNIQUE KEY `uk_channel_code` (`channel_code`),
  KEY `idx_status_type` (`channel_status`, `channel_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='支付通道表';


-- VIP套餐表
DROP TABLE IF EXISTS `xpk_vip_package`;
CREATE TABLE `xpk_vip_package` (
  `package_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `package_name` varchar(50) NOT NULL DEFAULT '' COMMENT '套餐名称',
  `package_code` varchar(20) NOT NULL DEFAULT '' COMMENT '套餐编码',
  `package_price` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '价格(CNY)',
  `package_price_usdt` decimal(10,2) DEFAULT NULL COMMENT 'USDT价格',
  `package_original` decimal(10,2) DEFAULT NULL COMMENT '原价(划线价)',
  `package_days` int(10) unsigned NOT NULL DEFAULT 1 COMMENT '有效天数',
  `package_daily_limit` int(10) unsigned NOT NULL DEFAULT 9999 COMMENT '每日观看限制(9999=无限)',
  `package_bonus_points` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '赠送积分',
  `package_bonus_days` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '赠送天数',
  `package_desc` varchar(255) NOT NULL DEFAULT '' COMMENT '套餐描述',
  `package_icon` varchar(255) NOT NULL DEFAULT '' COMMENT '图标',
  `package_hot` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否热门:0否,1是',
  `package_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态:0下架,1上架',
  `package_sort` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '排序',
  PRIMARY KEY (`package_id`),
  UNIQUE KEY `uk_package_code` (`package_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='VIP套餐表';

-- 默认VIP套餐
INSERT INTO `xpk_vip_package` (`package_name`, `package_code`, `package_price`, `package_price_usdt`, `package_days`, `package_daily_limit`, `package_bonus_points`, `package_desc`, `package_hot`, `package_sort`) VALUES
('日卡', 'day', 9.90, 1.50, 1, 20, 0, '体验尝鲜', 0, 1),
('周卡', 'week', 29.90, 4.50, 7, 50, 10, '超值之选', 0, 2),
('月卡', 'month', 99.00, 14.00, 30, 9999, 50, '热门推荐', 1, 3),
('年卡', 'year', 299.00, 42.00, 365, 9999, 200, '尊享特权', 0, 4);


-- 订单表
DROP TABLE IF EXISTS `xpk_order`;
CREATE TABLE `xpk_order` (
  `order_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_no` varchar(32) NOT NULL DEFAULT '' COMMENT '订单号',
  `user_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '用户ID',
  `order_type` varchar(20) NOT NULL DEFAULT 'vip' COMMENT '订单类型:vip/points',
  `product_id` int(10) unsigned DEFAULT NULL COMMENT '商品ID(套餐ID)',
  `product_name` varchar(100) NOT NULL DEFAULT '' COMMENT '商品名称',
  `order_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '订单金额',
  `pay_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT '实付金额',
  `pay_method` varchar(20) DEFAULT NULL COMMENT '支付方式:alipay/wechat/usdt',
  `channel_id` int(10) unsigned DEFAULT NULL COMMENT '支付通道ID',
  `channel_code` varchar(50) DEFAULT NULL COMMENT '支付通道编码',
  `trade_no` varchar(64) DEFAULT NULL COMMENT '第三方交易号',
  `txid` varchar(100) DEFAULT NULL COMMENT 'USDT交易哈希',
  `usdt_amount` decimal(12,4) DEFAULT NULL COMMENT 'USDT金额',
  `order_status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态:0待支付,1已支付,2已取消,3已退款',
  `pay_time` int(10) unsigned DEFAULT NULL COMMENT '支付时间',
  `expire_time` int(10) unsigned DEFAULT NULL COMMENT '过期时间',
  `extra_data` text COMMENT '额外数据(JSON)',
  `client_ip` varchar(45) NOT NULL DEFAULT '' COMMENT '客户端IP',
  `order_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
  `order_update` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`order_id`),
  UNIQUE KEY `uk_order_no` (`order_no`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`order_status`),
  KEY `idx_order_time` (`order_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='订单表';


-- USDT金额锁定表
DROP TABLE IF EXISTS `xpk_usdt_lock`;
CREATE TABLE `xpk_usdt_lock` (
  `lock_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `lock_amount` decimal(12,4) NOT NULL COMMENT 'USDT金额',
  `order_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '订单ID',
  `expire_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '过期时间',
  PRIMARY KEY (`lock_id`),
  UNIQUE KEY `uk_amount` (`lock_amount`),
  KEY `idx_expire` (`expire_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='USDT金额锁定表';

-- 积分记录表
DROP TABLE IF EXISTS `xpk_point_log`;
CREATE TABLE `xpk_point_log` (
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '用户ID',
  `log_type` varchar(20) NOT NULL DEFAULT '' COMMENT '类型:earn获得/consume消费/gift赠送/refund退还',
  `log_amount` int(11) NOT NULL DEFAULT 0 COMMENT '变动数量(正负)',
  `log_balance` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '变动后余额',
  `log_remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `related_id` int(10) unsigned DEFAULT NULL COMMENT '关联ID',
  `log_time` int(10) unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`log_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_log_time` (`log_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='积分记录表';
