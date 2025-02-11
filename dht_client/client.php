<?php
/*
 * 设置服务器 ulimit -n 65535
 * 记得放开防火墙6882端口
 */
error_reporting(E_ERROR );
define('BASEPATH', dirname(__FILE__));
define('AUTO_FIND_TIME', 1000); //定时寻找节点时间间隔 /毫秒
define('MAX_NODE_SIZE', 200); //保存node_id最大数量,不要设置太大，没有必要
define('BIG_ENDIAN', pack('L', 1) === pack('N', 1));

$config = require_once BASEPATH . '/config.php';
$database_config = require_once BASEPATH . '/database.php';
require_once BASEPATH . '/inc/Node.class.php';
require_once BASEPATH . '/inc/Bencode.class.php';
require_once BASEPATH . '/inc/Base.class.php';
require_once BASEPATH . '/inc/DbPool.class.php';
require_once BASEPATH . '/inc/Func.class.php';
require_once BASEPATH . '/inc/DhtClient.class.php';
require_once BASEPATH . '/inc/DhtServer.class.php';
require_once BASEPATH . '/inc/Metadata.class.php';
require_once BASEPATH . '/inc/MySwoole.class.php';
require_once "vendor/autoload.php";

$nid = Base::get_node_id();
$table = array();
$time = microtime(true);
$bootstrap_nodes = array(
    array('router.bittorrent.com', 6881),
    array('dht.transmissionbt.com', 6881),
    array('router.utorrent.com', 6881)
);

Func::Logs(date('Y-m-d H:i:s', time()) . " - 服务启动..." . PHP_EOL, 1);
Swoole\Coroutine::set(['hook_flags' => SWOOLE_HOOK_ALL]);
$serv = new Swoole\Server('0.0.0.0', 6882, SWOOLE_PROCESS, SWOOLE_SOCK_UDP);
$serv->set($config);
$serv->on('start', 'MySwoole::start');
$serv->on('WorkerStart', 'MySwoole::workStart');
$serv->on('Packet', 'MySwoole::packet');
$serv->on('task', 'MySwoole::task');
$serv->on('WorkerExit', 'MySwoole::workerExit');
$serv->on('finish', 'MySwoole::finish');
$serv->start();
