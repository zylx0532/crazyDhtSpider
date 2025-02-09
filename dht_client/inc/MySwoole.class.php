<?php

class MySwoole
{
    public static function workStart($serv, $worker_id)
    {
        if ($worker_id >= $serv->setting['worker_num']) {
            swoole_set_process_name("php_dht_client_task_worker");
        } else {
            swoole_set_process_name("php_dht_client_event_worker");
        }
        //每分钟向文件覆盖写入一次work status信息，用来监控运行状态
        swoole_timer_tick(60000, function ($timer_id) use ($serv) {
            Func::Logs(json_encode($serv->stats()) . PHP_EOL, 3);
        });
        swoole_timer_tick(AUTO_FIND_TIME, function ($timer_id) use ($serv) {
            global $table, $bootstrap_nodes;
            gc_mem_caches(); //清理内存碎片
            if (count($table) == 0) {
                DhtServer::join_dht($table, $bootstrap_nodes);
            } else {
                 if ($serv->stats()['idle_worker_num'] > 30) {
                    DhtServer::auto_find_node($table, $bootstrap_nodes);
                }
            }
        });
    }

    /*
    $server，swoole_server对象
    $fd，TCP客户端连接的文件描述符
    $from_id，TCP连接所在的Reactor线程ID
    $data，收到的数据内容，可能是文本或者二进制内容
    */
    public static function packet($serv, $data, $fdinfo)
    {
        if (strlen($data) == 0) {
            return false;
        }
        $msg = Base::decode($data);
        try {
            if (!isset($msg['y'])) {
                return false;
            }
            if ($msg['y'] == 'r') {
                // 如果是回复, 且包含nodes信息 添加到路由表
                if (is_array($msg['r']) && array_key_exists('nodes', $msg['r'])) {
                    DhtClient::response_action($msg, array($fdinfo['address'], $fdinfo['port']));
                }
            } elseif ($msg['y'] == 'q') {
                // 如果是请求, 则执行请求判断
                DhtClient::request_action($msg, array($fdinfo['address'], $fdinfo['port']));
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public static function task(Swoole\Server $server, Swoole\Server\Task $task)
    {
        global $config;
        $ip = $task->data['ip'];
        $port = $task->data['port'];
        $infohash = unserialize($task->data['infohash']);
        $client = new Swoole\Client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC);
        if (!@$client->connect($ip, $port, 0.8)) {
            @$client->close(true);
        } else {
            $rs = Metadata::download_metadata($client, $infohash);
            if ($rs != false) {
                DhtServer::send_response($rs, array($config['server_ip'], $config['server_port']));
            }
            $client->close(true);
        }
        $task->finish("OK");
    }

    public static function workerExit($serv, $worker_id)
    {
        Swoole\Timer::clearAll();
    }

    public static function finish($serv, $task_id, $data)
    {
    }
}
