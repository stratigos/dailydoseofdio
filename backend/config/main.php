<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id'                  => 'app-backend',
    'basePath'            => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap'           => ['log'],
    'modules'             => [],
    'components'          => [
        'authManager' => [
            'class' => 'yii\rbac\PhpManager'
        ],
        'errorHandler' => [
            'errorAction' => 'site/error'
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets'    => [
                [
                    'class'  => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ]
            ]
        ],
        'request' => [
            'cookieValidationKey' => 'ihavenoideawhatiamdoing'
        ],
        'urlManager' => [
            'enablePrettyUrl'     => true,
            'showScriptName'      => false,
            'class'               => 'yii\web\UrlManager',
            /**
             * @todo MOVE URL RULES TO SOME KIND OF routes.php SCRIPT
             */
            'rules'               => [
                'blogs/<page:\d+>'             => 'blog/index',
                'blogs'                        => 'blog/index',
                'blog/view/<id:\d+>'           => 'blog/view',
                'blog/create'                  => 'blog/create',
                'blog/update/<id:\d+>'         => 'blog/update',
                'blog/delete/<id:\d+>'         => 'blog/delete',
                'bloggers/<page:\d+>'          => 'blogger/index',
                'bloggers'                     => 'blogger/index',
                'blogger/view/<id:\d+>'        => 'blogger/view',
                'blogger/create'               => 'blogger/create',
                'blogger/update/<id:\d+>'      => 'blogger/update',
                'blogger/delete/<id:\d+>'      => 'blogger/delete',
                'categories/<page:\d+>'        => 'category/index',
                'categories'                   => 'category/index',
                'category/view/<id:\d+>'       => 'category/view',
                'category/create'              => 'category/create',
                'category/update/<id:\d+>'     => 'category/update',
                'category/delete/<id:\d+>'     => 'category/delete',
                'diosites/<page:\d+>'          => 'diosite/index',
                'diosites'                     => 'diosite/index',
                'diosite/create'               => 'diosite/create',
                'diosite/update/<id:\d+>'      => 'diosite/update',
                'diosite/delete/<id:\d+>'      => 'diosite/delete',
                'pages/<page:\d+>'             => 'page/index',
                'pages'                        => 'page/index',
                'page/view/<id:\d+>'           => 'page/view',
                'page/create'                  => 'page/create',
                'page/update/<id:\d+>'         => 'page/update',
                'page/delete/<id:\d+>'         => 'page/delete',
                'posts/<page:\d+>'             => 'post/index',
                'posts'                        => 'post/index',
                'post/view/<id:\d+>'           => 'post/view',
                'post/create/<media_type:\d>'  => 'post/create',
                'post/update/<id:\d+>'         => 'post/update',
                'post/delete/<id:\d+>'         => 'post/delete',
                'promotedposts'                => 'promotedpost/index',
                'promotedpost/create'          => 'promotedpost/create',
                'promotedpost/update/<id:\d+>' => 'promotedpost/update',
                'promotedpost/delete/<id:\d+>' => 'promotedpost/delete',
                '/'                            => 'site/index',
                'logout'                       => 'site/logout',
                'login'                        => 'site/login',
                'tags/<page:\d+>'              => 'tag/index',
                'tags'                         => 'tag/index',
                'tag/view/<id:\d+>'            => 'tag/view',
                'tag/create'                   => 'tag/create',
                'tag/update/<id:\d+>'          => 'tag/update',
                'tag/delete/<id:\d+>'          => 'tag/delete',
                'users'                        => 'user/index',
                'user/create'                  => 'user/create',
                'user/update/<id:\d+>'         => 'user/update',
                'user/delete/<id:\d+>'         => 'user/delete'
            ]
        ],
        'user' => [
            'identityClass'   => 'common\models\User',
            'enableAutoLogin' => true
        ]
    ],
    'params' => $params
];
