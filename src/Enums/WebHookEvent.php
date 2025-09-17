<?php

namespace Ledc\Websocket\Enums;

/**
 * WebHook事件枚举
 */
enum WebHookEvent: string
{
    /**
     * 客户端绑定UID
     */
    case bind = 'bind';
    /**
     * 客户端解绑UID
     */
    case unbind = 'unbind';
    /**
     * UID上线
     */
    case online = 'online';
    /**
     * UID下线
     */
    case offline = 'offline';
    /**
     * 客户端心跳ping
     */
    case ping = 'ping';
    /**
     * 客户端心跳pong
     */
    case pong = 'pong';

    /**
     * 是否是绑定UID
     * @return bool
     */
    public function isBind(): bool
    {
        return self::bind === $this;
    }

    /**
     * 是否是解绑UID
     * @return bool
     */
    public function isUnbind(): bool
    {
        return self::unbind === $this;
    }

    /**
     * 是否是上线
     * @return bool
     */

    public function isOnline(): bool
    {
        return self::online === $this;
    }

    /**
     * 是否是下线
     * @return bool
     */
    public function isOffline(): bool
    {
        return self::offline === $this;
    }

    /**
     * 是否是心跳ping
     * @return bool
     */
    public function isPing(): bool
    {
        return self::ping === $this;
    }

    /**
     * 是否是心跳pong
     * @return bool
     */
    public function isPong(): bool
    {
        return self::pong === $this;
    }
}
