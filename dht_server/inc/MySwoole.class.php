<?php

class MySwoole
{
    public static function workStart($serv, $worker_id)
    {
        global $config;
        swoole_set_process_name("php_dht_server_event_worker");
        //每分钟向文件覆盖写入一次work status信息，用来监控运行状态
        swoole_timer_tick(60000, function ($timer_id) use ($serv) {
            Func::Logs(json_encode($serv->stats()) . PHP_EOL, 3);
        });
        if (!DEBUG) {
            try {
                $serv->db = new DbPool($config);
            } catch (Exception $e) {
                Func::Logs("数据库连接失败" . PHP_EOL);
            }
        }
    }

    public static function packet($serv, $data, $fdinfo)
    {
        if (strlen($data) == 0) {
            $serv->close(true);
            return false;
        }
        $rs = Base::decode($data);
        if (!is_array($rs) || !isset($rs['infohash'])) {
            return false;
        }
        if (empty(Func::getBtFiles($rs))) {
            return false;
        }
        $rs = Func::getBtFiles($rs);
        $bt_data = Func::getBtData($rs);
        if (DEBUG) {
            Func::Logs(json_encode($bt_data, JSON_UNESCAPED_UNICODE) . PHP_EOL, 2);
            return false;
        }
        try {
            go(function () use ($bt_data, $rs) {
                DbPool::sourceQuery($rs, $bt_data);
            });

        } catch (Exception $e) {
            Func::Logs("数据插入失败" . $e->getMessage() . PHP_EOL);
        }

        $serv->close(true);
        return true;
    }

    public static function task(Swoole\Server $server, Swoole\Server\Task $task)
    {

    }

    public static function workerExit($server, $worker_id)
    {
        Swoole\Timer::clearAll();
    }

    public static function finish($server, $task_id, $data)
    {

    }
}