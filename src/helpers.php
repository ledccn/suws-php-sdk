<?php

namespace Ledc\Websocket;

/**
 * 设置环境变量
 * @param string $token 管理Token
 * @param string $url 请求地址
 * @param int $timeout 请求超时
 */
function suws_set_env(string $token, string $url, int $timeout = 5): void
{
    putenv('SUWS_TOKEN=' . $token);
    putenv('SUWS_URL=' . $url);
    putenv('SUWS_TIMEOUT=' . $timeout);
}
