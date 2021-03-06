<?php
/************************************
* CRUD operations for Users (admins)
*************************************/
namespace backend\controllers;

use common\models\User;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\HttpException;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class UserController extends Controller
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
                        'actions' => ['index', 'create', 'update', 'delete'],
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
     * render inventory as list of Users
     */
    public function actionIndex()
    {
        $userDP                              = new ActiveDataProvider();
        $userDP->pagination->defaultPageSize = 10;
        $userDP->pagination->pageSizeParam   = false;
        $userDP->query                       = User::find();

        return $this->render(
            'index',
            [
                'createUserUrl'     => Yii::$app->urlManager->createUrl('user/create'),
                'usersDataProvider' => $userDP
            ]
        );
    }

    /**
     * create a new User record
     */
    public function actionCreate()
    {
        $user   = new User();
        $errors = [];

        if (Yii::$app->request->isPost) {
            $user->load(Yii::$app->request->post());
            if($user->save()) {
                return $this->redirect(['index']);
            } else {
                $errors = $user->getErrors();
            }
        }

        return $this->render(
            'create',
            [
                'user'   => $user,
                'errors' => $errors
            ]
        );
    }

    /**
     * edit an existing User record
     */
    public function actionUpdate($id)
    {
        $user   = User::find()->where('id = :_id', [':_id' => $id])->one();
        $errors = [];

        if ($user === NULL) {
            throw new HttpException(404, "User {$id} Not Found");
        }

        if (Yii::$app->request->isPost) {
            $post_data = Yii::$app->request->post();
            $user->load($post_data);

            // Crude way of updating password. TODO: implement a UserAdminForm
            //  model, which handles validation for passphrases / pass
            //  confirms, and any other rules associated with setting the 
            //  password value.
            // Currently, no error is thrown if User pass/conf dont match.
            if (isset($post_data['user_password']) && !empty($post_data['user_password'])) {
                if (isset($post_data['user_password_conf']) &&
                   ($post_data['user_password_conf'] == $post_data['user_password'])
                ) {
                    $user->new_password = $post_data['user_password'];
                }
            }

            if($user->save()) {
                return $this->redirect(['index']);
            } else {
                $errors = $user->getErrors();
            }
        }

        return $this->render(
            'update',
            [
                'user'   => $user,
                'errors' => $errors
            ]
        );
    }

    /**
     * soft delete a User record
     */
    public function actionDelete($id)
    {   
        if ($user = User::find()->where('id = :_id', [':_id' => $id])->one()) {
            $user->deleted_at = time();
            if ($user->save()) {
                return $this->redirect(['index']);
            } else {
                $errors[] = "Error deleting user: {$id}";
            }
        }
    }
}
