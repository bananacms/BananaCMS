<?php
/**
 * 支付配置
 * Powered by https://xpornkit.com
 */

defined('XPK_ROOT') or exit('Access Denied');

return [
    // USDT/TRC20 配置
    'usdt' => [
        'enabled' => false,                             // 是否启用USDT支付
        'address' => '',                                // USDT收款地址(TRC20)
        'tron_api_key' => '',                           // TronGrid API Key (https://www.trongrid.io)
        'usdt_contract' => 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t', // USDT合约地址(勿改)
        'lock_time' => 1800,                            // 金额锁定时间(秒)，默认30分钟
    ],
    
    // 订单配置
    'order' => [
        'expire_time' => 1800,                          // 订单过期时间(秒)，默认30分钟
        'no_prefix' => 'XPK',                           // 订单号前缀
    ],
    
    // 回调配置
    'notify_url' => SITE_URL . '/api?action=pay.notify',
    'return_url' => SITE_URL . '/user/pay/result',
];
