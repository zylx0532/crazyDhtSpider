<?php
/*此配置为2核4Gvps上的参数，其他配置服务器请自行设置
 *task_worker_num 越多，占用内存越大，可以按照每个woker平均占用20M内存来计算总的内存需求
 */
return array(
    'daemonize' => true, //是否后台守护进程
    'worker_num' => 4, // 主进程数, 一般为CPU的1至4倍 同时执行任务数量 根据自己的实际情况设置
    'task_worker_num' => 120, //task进程的数量 值越大内存占用越高 根据自己的实际情况设置
    'server_ip' => '127.0.0.1', //服务端ip
    'server_port' => 2345, //服务端端口
    'max_conn' => 65535, //最大连接数
    'reload_async' => true, //设置为 true 时，将启用异步安全重启特性，Worker 进程会等待异步事件完成后再退出
    'max_request' => 0, //防止 PHP 内存溢出, 一个工作进程处理 X 次任务后自动重启 (注: 0,不自动重启)
    'max_wait_time' => 3, //worker退出之前最大等待时间
    'dispatch_mode' => 1, //收到会轮循分配给每一个 Worker 进程
    'discard_timeout_request' => false, //是否丢弃已关闭链接的数据请求
    'log_level' => SWOOLE_LOG_WARNING, //日志级别设置,生产环境可以配置为SWOOLE_LOG_ERROR
    'log_file' => BASEPATH . '/logs/error.log', //错误日志目录
    'heartbeat_check_interval' => 5, //启用心跳检测，此选项表示每隔多久轮循一次，单位为秒
    'heartbeat_idle_time' => 10, //与heartbeat_check_interval配合使用。表示连接最大允许空闲的时间
    'task_enable_coroutine' => true, //task协程开启
    'task_max_request' => 0, //防止 PHP 内存溢出, 一个task进程处理 X 次任务后自动重启 (注: 0,不自动重启)
    'enable_coroutine' => true, //开启协程
);
