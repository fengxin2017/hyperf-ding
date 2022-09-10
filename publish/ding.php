<?php

return [
    // 默认机器人
    'default' => 'local',

    // 忽略的头信息
    'ignore_header' => [
        'client-key', 'client-timestamp', 'client-nonce', 'client-sign', 'client-accesskey'
    ],
    // 头信息前缀
    'header_prefix' => '',

    'bots' => [
        // 生产环境
        'prod' => [
            // 机器人access_token
            'token' => '',
            // 机器人secret
            'secret' => '',
            // 机器人名称，每个机器人名称不要一致
            'name' => '生产环境',
            // 异常发生时是否开启追踪
            'trace' => true,
            // 相同异常发生时每多少秒上报一次。
            'report_frequency' => 60,
        ],

        // 开发环境
        'dev' => [
            'token' => '',
            'secret' => '',
            'name' => '开发环境',
            'trace' => false,
            'report_frequency' => 20,
        ],

        // 本地环境
        'local' => [
            'token' => '',
            'secret' => '',
            'name' => '本地环境',
            'trace' => false,
            'report_frequency' => 20,
        ]
    ]
];
