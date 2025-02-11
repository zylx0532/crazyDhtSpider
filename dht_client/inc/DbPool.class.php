<?php

use Medoo\Medoo;

class DbPool
{
    public static function medoo():Medoo
    {
        global $database_config;
        return new Medoo([
            'database_type' => 'mysql',
            'database_name' => $database_config['db']['name'],
            'server' => $database_config['db']['host'],
            'username' => $database_config['db']['user'],
            'password' => $database_config['db']['pass'],
        ]);
    }
    public static function sourceQuery($rs,$bt_data):void
    {
        $data = self::medoo()->select("history", ['infohash'], [
            "infohash" => $rs['infohash']
        ]);
        if (!empty($data)) {
            self::medoo()->update("bt", [
                "hot[+]" => 1,
                "lasttime" => date('Y-m-d H:i:s'),
            ], [
                "infohash" => $rs['infohash']
            ]);
        } else {
            self::medoo()->insert("history", [
                "infohash" => $rs['infohash']
            ]);
            self::medoo()->insert("bt", $bt_data);
        }
    }
}