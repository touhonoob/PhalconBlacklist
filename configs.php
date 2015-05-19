<?php

return new \Phalcon\Config([
    'database' => include __DIR__ . "/configs/database.php",
    'blacklist' => new \Phalcon\Config([
        'dataDir' => __DIR__ . '/data'
    ])
]);