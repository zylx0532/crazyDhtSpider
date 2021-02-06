<?php
/*
 * 安装swoole pecl install swoole
 * 设置服务器 ulimit -n 65535
 * 记得放开防火墙6882端口
 */
error_reporting(E_ERROR);
ini_set('date.timezone', 'Asia/Shanghai');
ini_set("memory_limit", "-1");
define('BASEPATH', dirname(__FILE__));
$config = require_once BASEPATH . '/config.php';
define('MAX_REQUEST', 0);// worker 进程的最大任务数,根据自己的实际情况设置
define('AUTO_FIND_TIME', 300000);//定时寻找节点时间间隔 /毫秒
define('MAX_NODE_SIZE', 200);//保存node_id最大数量,不要设置太大，没有必要
define('BIG_ENDIAN', pack('L', 1) === pack('N', 1));

require_once BASEPATH . '/inc/Node.class.php';
require_once BASEPATH . '/inc/Bencode.class.php';
require_once BASEPATH . '/inc/Base.class.php';
require_once BASEPATH . '/inc/Func.class.php';
require_once BASEPATH . '/inc/DhtClient.class.php';
require_once BASEPATH . '/inc/DhtServer.class.php';
require_once BASEPATH . '/inc/Metadata.class.php';

$nid = Base::get_node_id();// 伪造设置自身node id
$table = array();// 初始化路由表
$time = microtime(true);
// 长期在线node
$bootstrap_nodes = array(
    array('router.bittorrent.com', 6881),
    array('dht.transmissionbt.com', 6881),
    array('router.utorrent.com', 6881)
);

//记录启动日志
Func::Logs(date('Y-m-d H:i:s', time()) . " - 服务启动..." . PHP_EOL, 1);

//一键协程HOOK
Co::set(['hook_flags' => SWOOLE_HOOK_ALL]);

//SWOOLE_PROCESS 使用进程模式，业务代码在Worker进程中执行
//SWOOLE_SOCK_UDP 创建udp socket
$serv = new Swoole\Server('0.0.0.0', 6882, SWOOLE_PROCESS, SWOOLE_SOCK_UDP);
$serv->set($config);

$serv->on('WorkerStart', function ($serv, $worker_id) {
    if ($worker_id >= $serv->setting['worker_num']) {
        swoole_set_process_name("php_dht_client_task_worker");
    } else {
        swoole_set_process_name("php_dht_client_event_worker");
    }
    swoole_timer_tick(AUTO_FIND_TIME, function ($timer_id) {
        global $table, $bootstrap_nodes;
        if (count($table) == 0) {
            DhtServer::join_dht($table, $bootstrap_nodes);
        } else {
            DhtServer::auto_find_node($table, $bootstrap_nodes);
        }
    });
});

/*
$server，swoole_server对象
$fd，TCP客户端连接的文件描述符
$from_id，TCP连接所在的Reactor线程ID
$data，收到的数据内容，可能是文本或者二进制内容
 */
$serv->on('Packet', function ($serv, $data, $fdinfo) {
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
            if (array_key_exists('nodes', $msg['r'])) {
                DhtClient::response_action($msg, array($fdinfo['address'], $fdinfo['port']));
            }
        } elseif ($msg['y'] == 'q') {
            // 如果是请求, 则执行请求判断
            DhtClient::request_action($msg, array($fdinfo['address'], $fdinfo['port']));
        }
    } catch (Exception $e) {
        var_dump($e->getMessage());
    }
});

$serv->on('task', function (Swoole\Server $server, Swoole\Server\Task $task) {
    global $config;
    /*$server_stats = json_encode($server->stats());
    Func::Logs($server_stats.PHP_EOL,3);
    if ($server->stats()['tasking_num'] > 0) {
        return false;
    }*/
    $ip = $task->data['ip'];
    $port = $task->data['port'];
    $infohash = unserialize($task->data['infohash']);
    $client = new Swoole\Client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC);
    if (!@$client->connect($ip, $port, 0.5)) {
        //echo ("connect failed! '.$ip.':'.$port.'---'.Error: {$client->errCode}".PHP_EOL);
        @$client->close(true);
    } else {
        //echo 'connent success! '.$ip.':'.$port.PHP_EOL;
        $rs = Metadata::download_metadata($client, $infohash);
        if ($rs != false) {
            //echo $ip.':'.$port.' udp send！'.PHP_EOL;
            DhtServer::send_response($rs, array($config['server_ip'], $config['server_port']));
            //echo date('Y-m-d H:i:s').' '. $rs['name'].PHP_EOL;
        }
        $client->close(true);
    }
    $task->finish("OK");
});

$serv->on('WorkerExit', function ($server, $worker_id) {
    Swoole\Timer::clearAll();
});

$serv->on('finish', function ($server, $task_id, $data) {
    //var_dump($server->stats()).PHP_EOL;
    //echo "AsyncTask[$task_id] finished: {$data}\n";
});


$serv->start();