<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-backend',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'common\models\SessionFlow'],
    'controllerNamespace' => 'frontend\controllers',
    
    'name' => 'Trueloaded New',
    'defaultRoute' => 'index/index',
    
    'modules' => [],
    'components' => [

        'urlManager' => [
            'class' => 'app\components\TlUrlManager',
            'hostInfo' => HTTP_SERVER,
            'baseUrl' => rtrim(DIR_WS_HTTP_CATALOG, '/'),

            'enablePrettyUrl' => true,
            'enableStrictParsing' => false,
            'showScriptName' => false,
            'rules' => [
                ['class' => 'app\components\TlUrlRule', /* 'controller' => 'site' */],
            ],
        ],

        'view' => [
            'class' => 'yii\web\View',
            'defaultExtension' => 'tpl',
            'renderers' => [
                'tpl' => [
                    'class' => 'yii\smarty\ViewRenderer',
                    //'cachePath' => '@runtime/Smarty/cache',
                ],
            ],

            'theme' => [
                'basePath' => '@app/themes/basic',
                'baseUrl' => '@web/themes/basic',
                'pathMap' => [
/*                
    '@app/views' => [
        '@app/themes/christmas', // <-- @app/themes/christmas/site/index.php or @app/themes/basic/site/index.php, depending on which themed file exists.
        '@app/themes/basic',
    ],
*/
                    '@app/views' => '@app/themes/basic',
                    '@app/modules' => '@app/themes/basic/modules', // <-- It will allow you to theme @app/modules/blog/views/comment/index.php into @app/themes/basic/modules/blog/views/comment/index.php.
                    '@app/widgets' => '@app/themes/basic/widgets', // <-- This will allow you to theme @app/widgets/currency/views/index.php into @app/themes/basic/widgets/currency/index.php.
                ],
            ],

        ],
        
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'loginUrl'=>['/account/login'],
        ],
        'platform' => [
          'class' => 'common\classes\platform',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
                [
                    'categories' => ['sql_error'],
                    'logFile' => '@app/runtime/logs/sql_error.log',
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'info'],
                    'logVars' => ['_GET','_POST'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'index/error',
        ],

    ],

    'params' => $params,
];
