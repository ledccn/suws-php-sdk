<?php

namespace Ledc\Websocket\Enums;

/**
 * API路由枚举
 */
enum ApiRoute: string
{
    /**
     * 发送消息给所有在线用户
     */
    case sendToAll = '/sendToAll';
    /**
     * 发送消息给指定ClientId
     */
    case sendToClient = '/sendToClient';
    /**
     * 关闭指定ClientId
     */
    case closeClient = '/closeClient';
    /**
     * 判断ClientId是否在线
     */
    case isOnline = '/isOnline';
    /**
     * 获取ClientId会话信息
     */
    case getSession = '/getSession';
    /**
     * 设置ClientId会话信息
     */
    case setSession = '/setSession';
    /**
     * 获取所有在线ClientId总数量
     */
    case getAllClientIdCount = '/getAllClientIdCount';
    /**
     * 获取所有在线ClientId总数量
     */
    case getAllClientCount = '/getAllClientCount';
    /**
     * 获取所有在线ClientId列表
     */
    case getAllClientIdList = '/getAllClientIdList';
    /**
     * 获取所有在线Client会话信息
     */
    case getAllClientSessions = '/getAllClientSessions';
    /**
     * 绑定UID
     */
    case bindUID = '/bindUid';
    /**
     * 解绑UID
     */
    case unbindUID = '/unbindUid';
    /**
     * 判断UID是否在线
     */
    case isUidOnline = '/isUidOnline';
    /**
     * 发送消息给UID
     */
    case sendToUid = '/sendToUid';
    /**
     * 通过UID获取ClientIds
     */
    case getClientIdByUid = '/getClientIdByUid';
    /**
     * 通过ClientId获取UID
     */
    case getUidByClientId = '/getUidByClientId';
    /**
     * 获取所有在线UID列表
     */
    case getAllUidList = '/getAllUidList';
    /**
     * 获取所有在线UID总数量
     */
    case getAllUidCount = '/getAllUidCount';
    /**
     * 获取UID会话信息
     */
    case getUidSession = '/getUidSession';
    /**
     * 设置UID会话信息
     */
    case setUidSession = '/setUidSession';
    /**
     * RPC
     */
    case rpc = '/rpc';
    /**
     * 加入群组
     */
    case joinGroup = '/joinGroup';
    /**
     * 离开群组
     */
    case leaveGroup = '/leaveGroup';
    /**
     * 解散群组
     */
    case ungroup = '/ungroup';
    /**
     * 发送消息给群组
     */
    case sendToGroup = '/sendToGroup';
    /**
     * 获取群组在线ClientId总数量
     */
    case getClientIdCountByGroup = '/getClientIdCountByGroup';
    /**
     * 获取群组在线客户端的会话信息
     */
    case getClientSessionsByGroup = '/getClientSessionsByGroup';
    /**
     * 获取群组在线ClientId列表
     */
    case getClientIdListByGroup = '/getClientIdListByGroup';
    /**
     * 获取群组在线UID列表
     */
    case getUidListByGroup = '/getUidListByGroup';
    /**
     * 获取群组在线UID总数量
     */
    case getUidCountByGroup = '/getUidCountByGroup';
    /**
     * 获取所有群组名称列表
     */
    case getAllGroupIdList = '/getAllGroupIdList';
    /**
     * 获取UID加入的群组名称列表
     */
    case getGroupByUid = '/getGroupByUid';
    /**
     * 重新加载配置文件
     */
    case reloadConfig = '/reloadConfig';
    /**
     * 获取配置
     */
    case getConfig = '/getConfig';

    /**
     * 获取请求方式
     * @return string
     */
    public function getMethod(): string
    {
        return match ($this) {
            self::sendToAll,
            self::sendToClient,
            self::closeClient,
            self::setSession,
            self::bindUID,
            self::unbindUID,
            self::sendToUid,
            self::setUidSession,
            self::rpc,
            self::joinGroup,
            self::leaveGroup,
            self::ungroup,
            self::sendToGroup,
            self::reloadConfig => 'POST',
            default => 'GET',
        };
    }
}
