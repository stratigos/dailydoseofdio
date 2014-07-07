<?php

use yii\db\Schema;
use common\models\User;

/**
 * Migration to initialize DDoD Web Application
 */
class m130524_201442_init extends \yii\db\Migration
{
    /**
     * creates the following tables:
     *  + tags
     *  + users
     * creates the following instances/records:
     *  + user: admin 
     */
    public function up()
    {
        $tableOptions = NULL;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable(
            '{{%tags}}',
            [
                'id'         => Schema::TYPE_PK,
                'name'       => Schema::TYPE_STRING  . '(32) NOT NULL',
                'created_at' => Schema::TYPE_INTEGER . ' UNSIGNED NOT NULL',
                'updated_at' => Schema::TYPE_INTEGER . ' UNSIGNED NOT NULL',
                'deleted_at' => Schema::TYPE_INTEGER . ' UNSIGNED NOT NULL'
            ],
            $tableOptions
        );

        $this->createTable(
            '{{%users}}',
            [
                'id'                   => Schema::TYPE_PK,
                'username'             => Schema::TYPE_STRING   . ' NOT NULL',
                'auth_key'             => Schema::TYPE_STRING   . '(32) NOT NULL',
                'password_hash'        => Schema::TYPE_STRING   . ' NOT NULL',
                'password_reset_token' => Schema::TYPE_STRING,
                'email'                => Schema::TYPE_STRING   . ' NOT NULL',
                'role'                 => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 10',
                'status'               => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 10',
                'created_at'           => Schema::TYPE_INTEGER  . ' NOT NULL',
                'updated_at'           => Schema::TYPE_INTEGER  . ' NOT NULL',
            ],
            $tableOptions
        );

        // create initial admin user
        $password       = 'admin';
        $user           = new User();
        $user->username = 'admin';
        $user->password = $password;
        $user->email    = 'dont@spam.me';
        try {
            $user->save();
        } catch(Exception $e) {
            echo("\n");
            echo($e->getMessage());
            echo("\n");
        }
        echo(
            "\n    New user with username: '{$user->username}' and password '{$password}' created." . 
            "\n    ~ !! After migrations finish, log in, and update this user with a secure password !! ~\n"
        );
    }

    public function down()
    {
        $this->dropTable('{{%tags}}');
        $this->dropTable('{{%users}}');
    }
}
