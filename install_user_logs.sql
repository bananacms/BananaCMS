-- 用户操作日志表
-- 可选安装，用于记录用户关键操作日志

CREATE TABLE IF NOT EXISTS `xpk_user_logs` (
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
  KEY `idx_log_level` (`log_level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户操作日志表';

-- 创建索引以提高查询性能
CREATE INDEX `idx_user_time` ON `xpk_user_logs` (`user_id`, `log_time`);
CREATE INDEX `idx_action_time` ON `xpk_user_logs` (`log_action`, `log_time`);

-- 可选：创建分区表以提高大数据量下的性能
-- ALTER TABLE `xpk_user_logs` 
-- PARTITION BY RANGE (TO_DAYS(log_time)) (
--     PARTITION p202401 VALUES LESS THAN (TO_DAYS('2024-02-01')),
--     PARTITION p202402 VALUES LESS THAN (TO_DAYS('2024-03-01')),
--     PARTITION p202403 VALUES LESS THAN (TO_DAYS('2024-04-01')),
--     PARTITION p_future VALUES LESS THAN MAXVALUE
-- );