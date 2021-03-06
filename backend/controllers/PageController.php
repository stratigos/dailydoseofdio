<?php
/*****************************
* CRUD operations for Pages
******************************/
namespace backend\controllers;

use Yii;
use yii\web\HttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use backend\dataproviders\PageControllerIndexDataProvider;
use common\models\Page;

class PageController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index', 'view'],
                        'allow'   => true,
                        'roles'   => ['author']
                    ],
                    [
                        'actions' => ['create', 'update', 'delete'],
                        'allow'   => true,
                        'roles'   => ['admin']
                    ]
                ],
                'denyCallback' => function ($rule, $action) {
                    throw new HttpException(403, "Invalid authorization for this action.");
                }
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction'
            ]
        ];
    }

    /**
     * render inventory as list of Pages
     */
    public function actionIndex()
    {
        return $this->render(
            'index',
            [
                'createPageUrl'     => Yii::$app->urlManager->createUrl('page/create'),
                'pagesDataProvider' => new PageControllerIndexDataProvider()
            ]
        );
    }

    /**
     * render a view of a Page's data
     * @param Int $id
     *  valid pages.id value
     */
    public function actionView($id)
    {
        $page = Page::find()->where('id = :_id', [':_id' => $id])->one();

        if($page === NULL) {
            throw new HttpException(404, "Page {$id} Not Found");
        }

        return $this->render(
            'view',
            [
                'indexUrl' => Yii::$app->urlManager->createUrl('page/index'),
                'page'     => $page
            ]
        );
    }

    /**
     * create a new Page record
     */
    public function actionCreate()
    {
        $page   = new Page();
        $errors = [];

        if(Yii::$app->request->isPost) {
            $page->load(Yii::$app->request->post());
            if($page->save()) {
                return $this->redirect(['index']);
            } else {
                $errors = $page->getErrors();
            }
        }

        return $this->render(
            'create',
            [
                'page'   => $page,
                'errors' => $errors
            ]
        );
    }

    /**
     * edit an existing Page record
     */
    public function actionUpdate($id)
    {
        $page   = Page::find()->where('id = :_id', [':_id' => $id])->one();
        $errors = [];

        if($page === NULL) {
            throw new HttpException(404, "Page {$id} Not Found");
        }

        if(Yii::$app->request->isPost) {
            $page->load(Yii::$app->request->post());
            if($page->save()) {
                return $this->redirect(['index']);
            } else {
                $errors = $page->getErrors();
            }
        }

        return $this->render(
            'update',
            [
                'page'   => $page,
                'errors' => $errors
            ]
        );
    }

    /**
     * soft delete a Page record
     */
    public function actionDelete($id)
    {   
        //$errors = [];
        if($page = Page::find()->where('id = :_id', [':_id' => $id])->one()) {
            $page->deleted_at = time();
            if($page->save()) {
                return $this->redirect(['index']);
            } else {
                $errors[] = "Error deleting Page: {$id}";
            }
        }
        //  else {
        //     $errors[] = "Unable to locate Page: {$id}";
        // }

        // display errors
    }
}
