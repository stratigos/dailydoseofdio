<?php
use backend\assets\AppAsset;
use frontend\widgets\Alert;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;

/* @var $this \yii\web\View */
/* @var $content string */
if(empty($this->title)) {
    $this->title = Yii::$app->params['defaultTitle'];
}
AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
    <?php $this->beginBody() ?>
    <div class="wrap">
        <?php
            NavBar::begin([
                'brandLabel' => 'Daily Dose of Dio: Management',
                'brandUrl'   => Yii::$app->homeUrl,
                'options'    => [
                    'class' => 'navbar-inverse navbar-fixed-top',
                ]
            ]);
            $menuItems = [
                [
                    'label' => 'Home',
                    'url'   => ['/site/index']
                ],
                [
                    'label' => 'Content',
                    'items' => [
                        [
                            'label' => 'Posts',
                            'url'   => Yii::$app->urlManager->createUrl('post/index')
                        ],
                        [
                            'label' => 'Pages',
                            'url'   => Yii::$app->urlManager->createUrl('page/index')
                        ],
                        [
                            'label' => 'Dio Sites',
                            'url'   => Yii::$app->urlManager->createUrl('diosite/index')
                        ]
                    ]
                ],
                [
                    'label' => 'Taxonomy',
                    'items' => [
                        [
                            'label' => 'Blogs',
                            'url'   => Yii::$app->urlManager->createUrl('blog/index')
                        ],
                        [
                            'label' => 'Bloggers',
                            'url'   => Yii::$app->urlManager->createUrl('blogger/index')
                        ],
                        [
                            'label' => 'Categories',
                            'url'   => Yii::$app->urlManager->createUrl('category/index')
                        ],
                        [
                            'label' => 'Tags',
                            'url'   => Yii::$app->urlManager->createUrl('tag/index')
                        ]
                    ]
                ],
                [
                    'label' => 'Promotionals',
                    'items' => [
                        [
                            'label' => 'Promoted Posts',
                            'url'   => Yii::$app->urlManager->createUrl('promotedpost/index')
                        ],
                        '<li class="divider"></li>',
                        '<li class="dropdown-header">More TBD</li>'
                    ]
                ]
            ];
            if (Yii::$app->user->isGuest) {
                $menuItems[] = ['label' => 'Login', 'url' => ['/site/login']];
            } else {
                $menuItems[] = [
                    'label' => 'Hello, ' . Yii::$app->user->identity->username,
                    'items' => [
                        '<li class="divider"></li>',
                        '<li class="dropdown-header">Cool Header Bro</li>',
                        [
                            'label' => 'Profile',
                            'url'   => '#'
                        ],
                        [
                            'label' => 'Some Crap',
                            'url'   => '#'
                        ],
                        '<li class="divider"></li>',
                        '<li class="dropdown-header">Misc</li>',
                        [
                            'label' => 'Home',
                            'url'   => [Yii::$app->urlManager->createUrl('site/index')]
                        ],
                        [
                            'label'       => 'Logout',
                            'url'         => [Yii::$app->urlManager->createUrl('site/logout')],
                            'linkOptions' => ['data-method' => 'post']
                        ]
                    ]
                ];
            }
            echo Nav::widget([
                'options' => ['class' => 'navbar-nav navbar-right'],
                'items'   => $menuItems,
            ]);
            NavBar::end();
        ?>

        <div class="container">
            <?= Breadcrumbs::widget([
                'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
            ]) ?>
            <?php $alerts = new Alert(); /* @todo find means of producing alert without var assign */ ?>
            <?= $content ?>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p class="pull-left">&copy; Todd <?= date('Y') ?></p>
            <p class="pull-right"><?= Yii::powered() ?></p>
        </div>
    </footer>

    <?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
