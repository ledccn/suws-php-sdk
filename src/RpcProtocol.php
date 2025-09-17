<?php

namespace Ledc\Websocket;

use InvalidArgumentException;
use JsonSerializable;

/**
 * RPC调用协议
 */
class RpcProtocol implements JsonSerializable
{
    /**
     * 调用参数
     */
    protected array $args = [];
    /**
     * Json编码选项
     */
    protected int $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

    /**
     * 构造函数
     * @param string $_id 调用上下文ID
     * @param string $_method 调用方法
     */
    final public function __construct(public readonly string $_id, public readonly string $_method)
    {
        if (method_exists($this, 'init')) {
            $this->init();
        }
    }

    /**
     * 创建实例
     * @param string $_id 调用上下文ID
     * @param string $_method 调用方法
     * @param array $args 调用参数
     * @return static
     */
    final public static function make(string $_id, string $_method, array $args = []): static
    {
        return (new static($_id, $_method))->setArgs($args);
    }

    /**
     * 【获取】调用上下文ID
     * @return string
     */
    final public function getId(): string
    {
        return $this->_id;
    }

    /**
     * 【获取】调用方法
     * @return string
     */
    final public function getMethod(): string
    {
        return $this->_method;
    }

    /**
     * 【设置】调用参数
     * @param array $args
     * @return static
     */
    final public function setArgs(array $args): static
    {
        $this->args = $args;
        return $this;
    }

    /**
     * 【获取】调用参数
     * @return array
     */
    final public function getArgs(): array
    {
        return $this->args;
    }

    /**
     * 【设置】Json编码选项
     * @param int $flags
     * @return static
     */
    final public function setFlags(int $flags): static
    {
        $this->flags = $flags;
        return $this;
    }

    /**
     * 【获取】Json编码选项
     * @return int
     */
    final public function getFlags(): int
    {
        return $this->flags;
    }

    /**
     * 输出Json数据
     * @return string
     */
    final public function toJson(): string
    {
        $json = json_encode($this->jsonSerialize(), $this->getFlags());
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidArgumentException('json_encode error: ' . json_last_error_msg());
        }
        return $json;
    }

    /**
     * 转数组
     * @return array
     */
    final public function jsonSerialize(): array
    {
        $args = $this->getArgs();
        if (empty($args)) {
            $args = (object)$args;
        } else {
            $args = array_is_list($args) ? $args : (object)$args;
        }

        return [
            '_id' => $this->_id,
            '_method' => $this->_method,
            'args' => $args,
        ];
    }
}
