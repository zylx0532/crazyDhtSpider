<?php

use Medoo\Medoo;

class DbPool
{
    private $config;
    function __construct($config)
    {
        $this->config=$config;
    }
    public function medoo():Medoo
    {
        return new Medoo([
            'database_type' => 'mysql',
            'database_name' => $this->config['db']['name'],
            'server' => $this->config['db']['host'],
            'username' => $this->config['db']['user'],
            'password' => $this->config['db']['pass'],
        ]);
    }
}