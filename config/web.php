<?php

use sizeg\jwt\Jwt;

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
   'id' => 'basic',
   'name' => 'Yii2 Oauth2 Client',
   'basePath' => dirname(__DIR__),
   'bootstrap' => ['log'],
   'aliases' => [
      '@bower' => '@vendor/bower-asset',
      '@npm' => '@vendor/npm-asset',
   ],
   'components' => [
      'authClientCollection' => [
         'class' => 'yii\authclient\Collection',
         'clients' => [
            'google' => [
               'class' => 'yii\authclient\clients\Google',
               'clientId' => 'google_client_id',
               'clientSecret' => 'google_client_secret',
            ],
            'my-oauth2' => [
               'class' => '\app\components\MyOAuth2AuthClient',
               'clientId' => 'testclient',
               'clientSecret' => 'testpass',
               'authUrl' => 'http://localhost:8080/authorize',
               'tokenUrl' => 'http://localhost:8080/oauth2/token',
               'returnUrl' => 'http://localhost:8081/authorize?authclient=my-oauth2',
               'apiBaseUrl' =>  'http://localhost:8080/oauth2/v1',
               'apiUserInfo' => 'http://localhost:8080/oauth2/user-info',
               'viewOptions' => [
                  'icon' => 'https://cdn-icons-png.flaticon.com/512/2376/2376399.png'
               ]
            ],
         ],
      ],
      'authManager' => [
         'class' => \yii\rbac\DbManager::class,
      ],
      'request' => [
         // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
         'cookieValidationKey' => 'fw7ZbJNH1gnFKAPxMiLfRufY6im_cmey',
      ],
      'cache' => [
         'class' => 'yii\caching\FileCache',
      ],
      'user' => [
         'identityClass' => 'app\models\User',
         'enableAutoLogin' => true,
      ],
      'errorHandler' => [
         'errorAction' => 'site/error',
      ],
      'mailer' => [
         'class' => \yii\symfonymailer\Mailer::class,
         'viewPath' => '@app/mail',
         // send all mails to a file by default.
         'useFileTransport' => true,
      ],
      'log' => [
         'traceLevel' => YII_DEBUG ? 3 : 0,
         'targets' => [
            [
               'class' => 'yii\log\FileTarget',
               'levels' => ['error', 'warning'],
            ],
         ],
      ],
      'db' => $db,
      'urlManager' => [
         'enablePrettyUrl' => true,
         'showScriptName' => false,
         'rules' => [
            '<alias:\w+>' => 'site/<alias>',
         ],
      ],
      'jwt' => [
         'class' => Jwt::class,
         'key' => 'secret',
      ],
   ],
   'params' => $params,
   'modules' => [
      'admin' => [
         'class' => 'mdm\admin\Module',
      ],
   ],
   'as access' => [
      'class' => 'mdm\admin\components\AccessControl',
      'allowActions' => [
         'site/*',
         'admin/*',
      ]
   ],
];

if (YII_ENV_DEV) {
   // configuration adjustments for 'dev' environment
   $config['bootstrap'][] = 'debug';
   $config['modules']['debug'] = [
      'class' => 'yii\debug\Module',
      //'allowedIPs' => ['127.0.0.1', '::1'],
   ];

   $config['bootstrap'][] = 'gii';
   $config['modules']['gii'] = [
      'class' => 'yii\gii\Module',
      //'allowedIPs' => ['127.0.0.1', '::1'],
   ];
}

return $config;