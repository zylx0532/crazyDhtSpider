<?php
use Medoo\Medoo;
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
        try {
            if (!DEBUG) {
                $database = new Medoo([
                    'database_type' => 'mysql',
                    'database_name' => $config['db']['name'],
                    'server' => $config['db']['host'],
                    'username' => $config['db']['user'],
                    'password' => $config['db']['pass'],
                ]);
                $serv->mysql = $database;
            }
        } catch (Exception $e) {
            Func::Logs("数据库连接失败" . PHP_EOL);
        }
    }

    public static function packet($serv, $data, $fdinfo)
    {
        if (strlen($data) == 0) {
            $serv->close(true);
            return false;
        }
        $rs = Base::decode($data);
        if (is_array($rs) && isset($rs['infohash'])) {
            $length = 0;
            if (!empty($rs['files'])) {
                $files = json_encode(Func::array_transcoding($rs['files']), JSON_UNESCAPED_UNICODE);
                if (!$files) {
                    return false;
                }
                foreach ($rs['files'] as $value) {
                    $length += $value['length'];
                }
            } else {
                $length = $rs['length'];
            }
            $bt_data = [
                'name' => $rs['name'],
                'keywords' => Func::getKeyWords($rs['name']),
                'infohash' => $rs['infohash'],
                'files' => $files,
                'length' => $length,
                'piece_length' => $rs['piece_length'],
                'hits' => 0,
                'time' => date('Y-m-d H:i:s'),
                'lasttime' => date('Y-m-d H:i:s'),
            ];
            try {
                if (!DEBUG) {
                    $data = $serv->mysql->count("history", [
                        "infohash" => $rs['infohash']
                    ]);
                    if ($data > 0) {
                        $serv->mysql->update("bt", [
                            "hot[+]" => 1,
                            "lasttime" => date('Y-m-d H:i:s'),
                        ], [
                            "infohash" => $rs['infohash']
                        ]);
                    } else {
                        $serv->mysql->insert("history", [
                            "infohash" => $rs['infohash']
                        ]);
                        $serv->mysql->insert("bt", $bt_data);
                    }
                } else {
                    Func::Logs(json_encode($bt_data, JSON_UNESCAPED_UNICODE) . PHP_EOL, 2);
                }
            } catch (Exception $e) {
                Func::Logs("数据插入失败" . $e->getMessage() . PHP_EOL);
            }
        }
        $serv->close(true);
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