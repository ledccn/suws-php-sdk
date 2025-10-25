<?php

namespace Ledc\Websocket;

use Ledc\Curl\Curl;
use Ledc\Websocket\Enums\ApiRoute;
use Ledc\Websocket\Exceptions\BadRequestException;
use Ledc\Websocket\Exceptions\BusinessException;
use Ledc\Websocket\Exceptions\InvalidArgumentException;

/**
 * WebSocket管理类
 */
class SuWS
{
    /**
     * 服务地址
     * @var string
     */
    protected string $url;
    /**
     * 单例
     * @var SuWS|null
     */
    protected static ?SuWS $instance = null;
    /**
     * 是否使用安全模式（签名验证）
     * @var bool true安全模式签名验证、false普通模式Token验证
     */
    protected bool $secureMode = false;

    /**
     * 构造函数
     * @param string $token 管理令牌
     * @param int $timeout 请求超时时间
     * @param string $url 接口服务地址
     */
    final public function __construct(public readonly string $token, public readonly int $timeout = 5, string $url = 'https://suws.cn/api')
    {
        $this->url = $url;
    }

    /**
     * 向所有客户端或者指定的客户端发送数据
     * @param string $data 数据
     * @param array $client_ids 指定客户端IDs
     * @param array $exclude_client_ids 排除的ClientIds
     * @param array $exclude_uids 排除的UIDs
     * @return array
     * @throws BadRequestException
     * @throws BusinessException
     * @throws InvalidArgumentException
     */
    public function sendToAll(string $data, array $client_ids = [], array $exclude_client_ids = [], array $exclude_uids = []): array
    {
        $result = $this->request(ApiRoute::sendToAll, ApiRoute::sendToAll, [
            'data' => $data,
            'client_ids' => $client_ids,
            'exclude_client_ids' => $exclude_client_ids,
            'exclude_uids' => $exclude_uids,
        ]);
        return $result['data'];
    }

    /**
     * 向指定的客户端发送数据
     * @param string $client_id 客户端ID
     * @param string $data 数据（通过HTTP请求Body传递参数）
     * @return bool
     * @throws BadRequestException
     * @throws BusinessException
     * @throws InvalidArgumentException
     */
    public function sendToClient(string $client_id, string $data): bool
    {
        $this->request(ApiRoute::sendToClient->value . '?client_id=' . $client_id, ApiRoute::sendToClient, $data);
        return true;
    }

    /**
     * 关闭指定的客户端的连接
     * @param string $client_id
     * @return bool
     * @throws BadRequestException
     * @throws BusinessException
     * @throws InvalidArgumentException
     */
    public function closeClient(string $client_id): bool
    {
        $this->request(ApiRoute::closeClient, ApiRoute::closeClient, [
            'client_id' => $client_id,
        ]);
        return true;
    }

    /**
     * 判断指定客户端是否在线
     * @param string $client_id
     * @return bool
     * @throws BadRequestException
     * @throws BusinessException
     * @throws InvalidArgumentException
     */
    public function isOnline(string $client_id): bool
    {
        $result = $this->request(ApiRoute::isOnline, ApiRoute::isOnline, ['client_id' => $client_id]);
        return $result['data']['online'] ?? false;
    }

    /**
     * 获取ClientId会话信息
     * @param string $client_id
     * @return array|null
     * @throws BadRequestException
     * @throws BusinessException
     * @throws InvalidArgumentException
     */
    public function getSession(string $client_id): ?array
    {
        $result = $this->request(ApiRoute::getSession, ApiRoute::getSession, ['client_id' => $client_id]);
        return $result['data']['session'] ?? null;
    }

    /**
     * 设置ClientId会话信息
     * @param string $client_id
     * @param array $session 会话信息
     * @return bool
     * @throws BadRequestException
     * @throws BusinessException
     * @throws InvalidArgumentException
     */
    public function setSession(string $client_id, array $session): bool
    {
        $this->request(ApiRoute::setSession->value . '?client_id=' . $client_id, ApiRoute::setSession, $session);
        return true;
    }

    /**
     * 获取所有在线ClientId总数量
     * @return int
     * @throws BadRequestException
     * @throws BusinessException
     * @throws InvalidArgumentException
     */
    public function getAllClientIdCount(): int
    {
        $result = $this->request(ApiRoute::getAllClientIdCount, ApiRoute::getAllClientIdCount);
        return $result['data']['count'] ?? 0;
    }

    /**
     * 获取所有在线ClientId总数量
     * @return int
     * @throws BadRequestException
     * @throws BusinessException
     * @throws InvalidArgumentException
     */
    public function getAllClientCount(): int
    {
        return $this->getAllClientIdCount();
    }

    /**
     * 获取所有在线ClientId列表
     * @return array|null
     * @throws BadRequestException
     * @throws BusinessException
     * @throws InvalidArgumentException
     */
    public function getAllClientIdList(): ?array
    {
        $result = $this->request(ApiRoute::getAllClientIdList, ApiRoute::getAllClientIdList);
        return $result['data']['client_ids'] ?? null;
    }

    /**
     * 获取所有在线Client会话信息
     * @return array|null
     * @throws BadRequestException
     * @throws BusinessException
     * @throws InvalidArgumentException
     */
    public function getAllClientSessions(): ?array
    {
        $result = $this->request(ApiRoute::getAllClientSessions, ApiRoute::getAllClientSessions);
        return $result['data']['sessions'] ?? null;
    }

    /**
     * 将client_id与uid绑定
     * - 以便通过 sendToUid() 发送数据
     * - 通过 isUidOnline() 判断用户是否在线。
     * @param string $client_id 客户端ID
     * @param string $uid 用户UID
     * @param string $auth 客户端验证信息
     * @return bool
     * @throws BadRequestException
     * @throws BusinessException
     * @throws InvalidArgumentException
     */
    public function bindUid(string $client_id, string $uid, string $auth): bool
    {
        $this->request(ApiRoute::bindUID, ApiRoute::bindUID, [
            'client_id' => $client_id,
            'uid' => $uid,
            'auth' => $auth,
        ]);
        return true;
    }

    /**
     * 解除client_id与uid的绑定
     * @param string $client_id 客户端ID
     * @param string $uid 用户UID
     * @param string $auth 客户端验证信息
     * @return bool
     * @throws BadRequestException
     * @throws BusinessException
     * @throws InvalidArgumentException
     */
    public function unbindUid(string $client_id, string $uid, string $auth = ''): bool
    {
        $data = [
            'client_id' => $client_id,
            'uid' => $uid,
            'auth' => $auth,
        ];
        if ('' === $auth) {
            unset($data['auth']);
        }
        $this->request(ApiRoute::unbindUID, ApiRoute::unbindUID, $data);
        return true;
    }

    /**
     * 判断用户是否在线
     * @param string $uid 用户UID
     * @return bool
     * @throws BadRequestException
     * @throws BusinessException
     * @throws InvalidArgumentException
     */
    public function isUidOnline(string $uid): bool
    {
        $result = $this->request(ApiRoute::isUidOnline, ApiRoute::isUidOnline, ['uid' => $uid]);
        return $result['data']['online'] ?? false;
    }

    /**
     * 发送消息给指定UID
     * @param string $uid 用户UID
     * @param string $data 数据
     * @return bool
     * @throws BadRequestException
     * @throws BusinessException
     * @throws InvalidArgumentException
     */
    public function sendToUid(string $uid, string $data): bool
    {
        $this->request(ApiRoute::sendToUid->value . '?uid=' . $uid, ApiRoute::sendToUid, $data);
        return true;
    }

    /**
     * 通过UID获取ClientIds
     * @param string $uid
     * @return array|null
     * @throws BadRequestException
     * @throws BusinessException
     * @throws InvalidArgumentException
     */
    public function getClientIdByUid(string $uid): ?array
    {
        $result = $this->request(ApiRoute::getClientIdByUid, ApiRoute::getClientIdByUid, ['uid' => $uid]);
        return $result['data']['client_ids'] ?: null;
    }

    /**
     * 通过ClientId获取UID
     * @param string $client_id
     * @return string|null
     * @throws BadRequestException
     * @throws BusinessException
     * @throws InvalidArgumentException
     */
    public function getUidByClientId(string $client_id): ?string
    {
        $result = $this->request(ApiRoute::getUidByClientId, ApiRoute::getUidByClientId, ['client_id' => $client_id]);
        return $result['data']['uid'] ?? null;
    }

    /**
     * 获取所有在线UID列表
     * @return array|null
     * @throws BadRequestException
     * @throws BusinessException
     * @throws InvalidArgumentException
     */
    public function getAllUidList(): ?array
    {
        $result = $this->request(ApiRoute::getAllUidList, ApiRoute::getAllUidList);
        return $result['data']['uids'] ?? null;
    }

    /**
     * 获取所有在线UID总数量
     * @return int
     * @throws BadRequestException
     * @throws BusinessException
     * @throws InvalidArgumentException
     */
    public function getAllUidCount(): int
    {
        $result = $this->request(ApiRoute::getAllUidCount, ApiRoute::getAllUidCount);
        return $result['data']['count'] ?? 0;
    }

    /**
     * 获取UID会话信息
     * @param string $uid 用户UID
     * @return array|null
     * @throws BadRequestException
     * @throws BusinessException
     * @throws InvalidArgumentException
     */
    public function getUidSession(string $uid): ?array
    {
        $result = $this->request(ApiRoute::getUidSession, ApiRoute::getUidSession, ['uid' => $uid]);
        return $result['data']['session'] ?? null;
    }

    /**
     * 设置UID会话信息
     * @param string $uid 用户UID
     * @param array $session 会话信息
     * @return bool
     * @throws BadRequestException
     * @throws BusinessException
     * @throws InvalidArgumentException
     */
    public function setUidSession(string $uid, array $session): bool
    {
        $this->request(ApiRoute::setUidSession->value . '?uid=' . $uid, ApiRoute::setUidSession, $session);
        return true;
    }

    /**
     * RPC调用
     * @param string $uid 用户UID
     * @param string|RpcProtocol $payload Body参数
     * @return array
     * @throws BadRequestException
     * @throws BusinessException
     * @throws InvalidArgumentException
     */
    public function rpc(string $uid, string|RpcProtocol $payload): array
    {
        $payload = $payload instanceof RpcProtocol ? $payload->toJson() : $payload;
        return $this->request(ApiRoute::rpc, ApiRoute::rpc, $payload, ['uid' => $uid]);
    }

    /**
     * 加入群组
     * @param string $group 群组名称
     * @param string $client_id 客户端ID
     * @return bool
     * @throws BadRequestException
     * @throws BusinessException
     * @throws InvalidArgumentException
     */
    public function joinGroup(string $group, string $client_id): bool
    {
        $this->request(ApiRoute::joinGroup, ApiRoute::joinGroup, ['group' => $group, 'client_id' => $client_id]);
        return true;
    }

    /**
     * 离开群组
     * @param string $group 群组名称
     * @param string $client_id 客户端ID
     * @return bool
     * @throws BadRequestException
     * @throws BusinessException
     * @throws InvalidArgumentException
     */
    public function leaveGroup(string $group, string $client_id): bool
    {
        $this->request(ApiRoute::leaveGroup, ApiRoute::leaveGroup, ['group' => $group, 'client_id' => $client_id]);
        return true;
    }

    /**
     * 解散群组
     * @param string $group 群组名称
     * @return bool
     * @throws BadRequestException
     * @throws BusinessException
     * @throws InvalidArgumentException
     */
    public function ungroup(string $group): bool
    {
        $this->request(ApiRoute::ungroup, ApiRoute::ungroup, ['group' => $group]);
        return true;
    }

    /**
     * 发送消息给群组
     * @param string $group 群组名称
     * @param string $data 数据
     * @param array $client_ids 指定ClientId列表
     * @param array $exclude_client_ids 排除ClientId列表
     * @param array $exclude_uids 排除UID列表
     * @return array
     * @throws BadRequestException
     * @throws BusinessException
     * @throws InvalidArgumentException
     */
    public function sendToGroup(string $group, string $data, array $client_ids = [], array $exclude_client_ids = [], array $exclude_uids = []): array
    {
        $result = $this->request(ApiRoute::sendToGroup, ApiRoute::sendToGroup, [
            'group' => $group,
            'data' => $data,
            'client_ids' => $client_ids,
            'exclude_client_ids' => $exclude_client_ids,
            'exclude_uids' => $exclude_uids,
        ]);
        return $result['data'];
    }

    /**
     * 获取群组在线ClientId总数量
     * @param string $group 群组名称
     * @return int
     * @throws BadRequestException
     * @throws BusinessException
     * @throws InvalidArgumentException
     */
    public function getClientIdCountByGroup(string $group): int
    {
        $result = $this->request(ApiRoute::getClientIdCountByGroup, ApiRoute::getClientIdCountByGroup, ['group' => $group]);
        return $result['data']['count'] ?? 0;
    }

    /**
     * 获取群组在线客户端的会话信息
     * @param string $group 群组名称
     * @return array
     * @throws BadRequestException
     * @throws BusinessException
     * @throws InvalidArgumentException
     */
    public function getClientSessionsByGroup(string $group): array
    {
        $result = $this->request(ApiRoute::getClientSessionsByGroup, ApiRoute::getClientSessionsByGroup, ['group' => $group]);
        return $result['data']['sessions'] ?? [];
    }

    /**
     * 获取群组在线ClientId列表
     * @param string $group 群组名称
     * @return array|null
     * @throws BadRequestException
     * @throws BusinessException
     * @throws InvalidArgumentException
     */
    public function getClientIdListByGroup(string $group): ?array
    {
        $result = $this->request(ApiRoute::getClientIdListByGroup, ApiRoute::getClientIdListByGroup, ['group' => $group]);
        return $result['data']['client_ids'] ?? null;
    }

    /**
     * 获取群组在线UID列表
     * @param string $group 群组名称
     * @return array|null
     * @throws BadRequestException
     * @throws BusinessException
     * @throws InvalidArgumentException
     */
    public function getUidListByGroup(string $group): ?array
    {
        $result = $this->request(ApiRoute::getUidListByGroup, ApiRoute::getUidListByGroup, ['group' => $group]);
        return $result['data']['uids'] ?? null;
    }

    /**
     * 获取群组在线UID总数量
     * @param string $group 群组名称
     * @return int
     * @throws BadRequestException
     * @throws BusinessException
     * @throws InvalidArgumentException
     */
    public function getUidCountByGroup(string $group): int
    {
        $result = $this->request(ApiRoute::getUidCountByGroup, ApiRoute::getUidCountByGroup, ['group' => $group]);
        return $result['data']['count'] ?? 0;
    }

    /**
     * 获取所有群组名称列表
     * @return array|null
     * @throws BadRequestException
     * @throws BusinessException
     * @throws InvalidArgumentException
     */
    public function getAllGroupIdList(): ?array
    {
        $result = $this->request(ApiRoute::getAllGroupIdList, ApiRoute::getAllGroupIdList);
        return $result['data']['groups'] ?? null;
    }

    /**
     * 获取UID加入的群组名称列表
     * @param string $uid UID
     * @return array|null
     * @throws BadRequestException
     * @throws BusinessException
     * @throws InvalidArgumentException
     */
    public function getGroupByUid(string $uid): ?array
    {
        $result = $this->request(ApiRoute::getGroupByUid, ApiRoute::getGroupByUid, ['uid' => $uid]);
        return $result['data']['groups'] ?? null;
    }

    /**
     * 重新加载配置文件
     * @return bool
     * @throws BadRequestException
     * @throws BusinessException
     * @throws InvalidArgumentException
     */
    public function reloadConfig(): bool
    {
        $this->request(ApiRoute::reloadConfig, ApiRoute::reloadConfig);
        return true;
    }

    /**
     * 获取配置
     * @return array
     * @throws BadRequestException
     * @throws BusinessException
     * @throws InvalidArgumentException
     */
    public function getConfig(): array
    {
        $result = $this->request(ApiRoute::getConfig, ApiRoute::getConfig);
        return $result['data'];
    }

    /**
     * 单例
     * @return SuWS
     */
    final public static function getInstance(): SuWS
    {
        $token = getenv('SUWS_TOKEN') ?: '';
        $timeout = getenv('SUWS_TIMEOUT') ?: 5;
        $url = getenv('SUWS_URL') ?: 'http://127.0.0.1:8788/api';
        return static::$instance ?? (static::$instance = self::make($token, (int)$timeout, $url));
    }

    /**
     * 创建实例
     * @param string $token 管理令牌
     * @param int $timeout 请求超时时间
     * @param string $url 接口服务地址
     * @return SuWS
     */
    final public static function make(string $token, int $timeout = 5, string $url = 'https://suws.cn/api'): static
    {
        return new static($token, $timeout, $url);
    }

    /**
     * 获取服务地址
     * @return string
     */
    final public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * 设置服务地址
     * @param string $url
     * @return SuWS
     */
    final public function setUrl(string $url): static
    {
        $this->url = rtrim($url, '/');
        return $this;
    }

    /**
     * 获取是否启用安全模式
     * @return bool
     */
    final public function isSecureMode(): bool
    {
        return $this->secureMode;
    }

    /**
     * 设置是否启用安全模式
     * @param bool $secureMode
     */
    final public function setSecureMode(bool $secureMode): void
    {
        $this->secureMode = $secureMode;
    }

    /**
     * 请求
     * @param string|ApiRoute $uri
     * @param string|ApiRoute $method
     * @param array|object|string $payload
     * @param array $headers
     * @return array|null
     * @throws BadRequestException
     * @throws BusinessException
     * @throws InvalidArgumentException
     */
    final public function request(string|ApiRoute $uri, string|ApiRoute $method, array|object|string $payload = [], array $headers = []): ?array
    {
        // 兼容枚举
        $uri = $uri instanceof ApiRoute ? $uri->value : $uri;
        $method = $method instanceof ApiRoute ? $method->getMethod() : $method;

        match ($method) {
            'GET', 'POST' => true,
            default => throw new InvalidArgumentException('暂不支持的请求方法：' . $method),
        };

        $url = $this->getUrl() . $uri;
        $curl = new Curl();
        if ('https' === parse_url($this->getUrl(), PHP_URL_SCHEME)) {
            $curl->setSslVerify(false);
        }
        $curl->setAccept('application/json');
        $curl->setTimeout($this->timeout, $this->timeout * 2);
        $curl->setHeaders($headers);
        if ($this->isSecureMode()) {
            // 安全模式（签名验证）
            $payload = is_string($payload) ? $payload : json_encode($payload, JSON_UNESCAPED_UNICODE);
            $timestamp = time() * 1000;
            $curl->setHeader('Timestamp', (string)$timestamp);
            $curl->setHeader('Signature', $this->generateSignature($payload, (string)$timestamp));
        } else {
            // 普通模式（Token验证）
            $curl->setHeader('Token', $this->token);
        }

        if ('GET' === $method) {
            $curl->get($url, $payload);
        } else {
            $curl->post($url, $payload, true);
        }

        return $this->parseHttpResponse($curl);
    }

    /**
     * 解析HTTP响应
     * @param Curl $curl
     * @return array|null
     * @throws BusinessException|BadRequestException
     */
    final public function parseHttpResponse(Curl $curl): array
    {
        $response = $curl->getResponse();
        if (empty($response)) {
            $httpStatus = $curl->getHttpStatus();
            $curlErrorNo = $curl->getErrorCode();
            $curlErrorMessage = $curl->getErrorMessage();
            throw new BadRequestException('CURL请求失败：' . json_encode(compact('httpStatus', 'curlErrorNo', 'curlErrorMessage'), JSON_UNESCAPED_UNICODE));
        }

        $result = json_decode($response, true);
        $code = $result['code'] ?? -1;
        $message = $result['message'] ?? 'SUWS未返回错误信息';
        if ($curl->isSuccess() && 0 === $code) {
            return $result;
        }
        throw new BusinessException($message, $code);
    }

    /**
     * 生成签名
     * @param string $data 数据
     * @param string $timestamp 毫秒时间戳
     * @return string
     */
    final public function generateSignature(string $data, string $timestamp): string
    {
        return md5($data . $timestamp . $this->token);
    }

    /**
     * 验证签名
     * @param string $data 数据
     * @param string $timestamp 毫秒时间戳
     * @param string $signature 签名
     * @return bool
     */
    final public function verifySignature(string $data, string $timestamp, string $signature): bool
    {
        return $this->generateSignature($data, $timestamp) === $signature;
    }
}
