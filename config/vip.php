<?php
/**
 * VIP配置
 * Powered by https://xpornkit.com
 */

defined('XPK_ROOT') or exit('Access Denied');

return [
    // 免费用户限制
    'free_user' => [
        'daily_limit' => 3,                             // 每日免费观看次数
        'register_gift' => 5,                           // 注册赠送观看次数
    ],
    
    // 积分配置
    'points' => [
        'unlock_cost' => 10,                            // 积分解锁单个视频消耗
        'daily_sign' => 5,                              // 每日签到奖励积分
    ],
    
    // 邀请奖励
    'invite' => [
        'enabled' => true,                              // 是否启用邀请功能
        'register_points' => 50,                        // 邀请注册奖励积分
        'first_pay_rate' => 0.10,                       // 首次付费返佣比例(10%)
        'renew_rate' => 0.05,                           // 续费返佣比例(5%)
    ],
    
    // VIP功能开关
    'vip_enabled' => true,                              // 是否启用VIP功能(关闭则不限制观看)
];
