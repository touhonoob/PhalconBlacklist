<?php

return new \Phalcon\Config([
    'database' => include __DIR__ . "/configs/database.php",
    'redis' => include __DIR__ . '/configs/redis.php',
    'blacklist' => new \Phalcon\Config([
        'dataDir' => __DIR__ . '/data'
    ])
]);