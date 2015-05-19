<?php
require __DIR__ . "/../vendor/autoload.php";
error_reporting(E_ALL);
ini_set('display_errors', 1);

$configs = include __DIR__ . "/../configs.php";

$di = new \Phalcon\DI\FactoryDefault();
$di->setShared('db', function() use($configs) {
    $connection = new \Phalcon\Db\Adapter\Pdo\Mysql($configs['database']->toArray());
    return $connection;
});