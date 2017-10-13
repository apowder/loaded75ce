<?php
return [
    'timeZone' => date_default_timezone_get(),
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
      'cache' => [
        'class' => 'yii\caching\FileCache',
      ],
      'db' => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host='.DB_SERVER.';dbname='.DB_DATABASE,
        'username' => DB_SERVER_USERNAME,
        'password' => DB_SERVER_PASSWORD,
        'charset' => 'utf8',
        'enableSchemaCache' => true,
        'schemaCache' => 'cache',
      ],
        /*'cache' => [
            'class' => 'yii\caching\FileCache',
        ],*/
        /*'cache' => [
            'class' => 'yii\caching\MemCache',
            'servers' => [
                [
                    'host' => '127.0.0.1',
                    'port' => 11211,
                    'weight' => 60,
                ],
            ],
        ],*/
        
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
            'defaultRoles' => ['user'],
        ],
        'authClientCollection' => [
            'class' => 'yii\authclient\Collection',
            // all Auth clients will use this configuration for HTTP client:
            /*'httpClient' => [
                'transport' => 'yii\httpclient\CurlTransport',
            ],*/
            'clients' => [],
        ]
    ],
];
