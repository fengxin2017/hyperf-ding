<?php

use Fengxin2017\HyperfDing\Ding;

if (!function_exists('ding')) {
    /**
     * @param array $config
     * @return Ding
     * @throws Exception
     */
    function ding($config = [])
    {
        if (is_string($config)) {
            $config = config('ding.bots.' . $config);
            if (is_null($config)) {
                throw new Exception(sprintf('【%s】未配置默认机器人', 'config/ding.php'));
            }
        }

        return make(Ding::class, ['params' => $config]);
    }
}
