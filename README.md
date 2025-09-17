# Su WebSocket服务

Su WebSocket服务是一个高性能的WebSocket服务器，支持客户端连接管理、UID管理、群组管理、消息推送、RPC调用等功能。

完全参考GatewayWorker设计的HTTP管理API接口。 

## 安装 Installation

```sh
composer require ledc/websocket
```

## 运行环境

PHP版本：>=8.3

## 快速开始 Quick Start

可以在 `.env` 文件中配置环境变量

```conf
SUWS_TOKEN=管理令牌（与SUWS服务端配置一致）
SUWS_URL=http://127.0.0.1:8788/api
SUWS_TIMEOUT=5
```

或者通过函数设置环境变量：
```php
\Ledc\Websocket\suws_set_env('管理Token', 'http://127.0.0.1:8788/api', 5);
```

然后，使用单例模式调用：

```php
use Ledc\Websocket\SuWS;

// 向所有客户端或者指定的客户端发送数据
$result = SuWS::getInstance()->sendToAll();

// 向指定的客户端发送数据
$result = SuWS::getInstance()->sendToClient();

// 关闭指定的客户端的连接
$result = SuWS::getInstance()->closeClient();

// 判断指定客户端是否在线
$result = SuWS::getInstance()->isOnline();

// 获取ClientId会话信息
$result = SuWS::getInstance()->getSession();

// 设置ClientId会话信息
$result = SuWS::getInstance()->setSession();

// 获取所有在线ClientId总数量
$result = SuWS::getInstance()->getAllClientIdCount();
$result = SuWS::getInstance()->getAllClientCount();

// 获取所有在线ClientId列表
$result = SuWS::getInstance()->getAllClientIdList();

// 获取所有在线Client会话信息
$result = SuWS::getInstance()->getAllClientSessions();

// 将client_id与uid绑定
$result = SuWS::getInstance()->bindUid();

// 解除client_id与uid的绑定
$result = SuWS::getInstance()->unbindUid();

// 判断用户是否在线
$result = SuWS::getInstance()->isUidOnline();

// 发送消息给指定UID
$result = SuWS::getInstance()->sendToUid();

// 通过UID获取ClientIds
$result = SuWS::getInstance()->getClientIdByUid();

// 通过ClientId获取UID
$result = SuWS::getInstance()->getUidByClientId();

// 获取所有在线UID列表
$result = SuWS::getInstance()->getAllUidList();

// 获取所有在线UID总数量
$result = SuWS::getInstance()->getAllUidCount();

// 获取UID会话信息
$result = SuWS::getInstance()->getUidSession();

// 设置UID会话信息
$result = SuWS::getInstance()->setUidSession();

// RPC调用
$result = SuWS::getInstance()->rpc();

// 加入群组
$result = SuWS::getInstance()->joinGroup();

// 离开群组
$result = SuWS::getInstance()->leaveGroup();

// 解散群组
$result = SuWS::getInstance()->ungroup();

// 发送消息给群组
$result = SuWS::getInstance()->sendToGroup();

// 获取群组在线ClientId总数量
$result = SuWS::getInstance()->getClientIdCountByGroup();

// 获取群组在线客户端的会话信息
$result = SuWS::getInstance()->getClientSessionsByGroup();

// 获取群组在线ClientId列表
$result = SuWS::getInstance()->getClientIdListByGroup();

// 获取群组在线UID列表
$result = SuWS::getInstance()->getUidListByGroup();

// 获取群组在线UID总数量
$result = SuWS::getInstance()->getUidCountByGroup();

// 获取所有群组名称列表
$result = SuWS::getInstance()->getAllGroupIdList();

// 获取UID加入的群组名称列表
$result = SuWS::getInstance()->getGroupByUid();


// 重新加载配置文件
$result = SuWS::getInstance()->reloadConfig();

// 获取配置
$result = SuWS::getInstance()->getConfig();
```
