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
            'charset' => 'utf8mb4'
        ]);
    }

    public static function checkInfoHash($infohash): bool
    {
        $info = self::medoo()->select("history", "infohash", [
            "infohash" => $infohash
        ]);
        if (!empty($info)) {
            self::medoo()->update("bt", [
                "hot" => Medoo::raw("hot + 1")
            ], [
                "infohash" => $infohash
            ]);
            return true;
        } else {
            return false;
        }
    }
}