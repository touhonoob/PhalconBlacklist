<?php

require __DIR__ . "/../vendor/autoload.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);
\date_default_timezone_set('UTC');

$configs = include __DIR__ . "/../configs.php";

$di = new \Phalcon\DI\FactoryDefault();

$di->setShared('db', function() use($configs) {
    $connection = new \Phalcon\Db\Adapter\Pdo\Mysql($configs['database']->toArray());
    $connection->query("SET time_zone = '+00:00';");
    return $connection;
});

$di->setShared('redis', function() use($configs) {
    $redis = new \Redis();
    $redis->connect($configs['redis']['host'], $configs['redis']['port']);
    return $redis;
});

\Phalcon\Mvc\Model::setup([
    'exceptionOnFailedSave' => true
]);