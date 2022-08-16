<?php

return [
    // 默认机器人
    'default' => 'local',

    'bots' => [
        'prod' => [
            // 机器人access_token
            'token' => '',
            // 机器人secret
            'secret' => '',
            // 机器人名称
            'name' => '生产环境',
            // 异常发生时是否开启追踪
            'trace' => true,
            // 异常发生时播报频率
            'report_frequency' => 60,
        ],

        'dev' => [
            'token' => '',
            'secret' => '',
            'name' => '开发环境',
            'trace' => false,
            'report_frequency' => 20,
        ],

        'local' => [
            'token' => '',
            'secret' => '',
            'name' => '本地环境',
            'trace' => false,
            'report_frequency' => 20,
        ]
    ]
];
