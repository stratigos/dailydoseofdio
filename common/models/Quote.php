<?php
/*************************************************************************
* A Quote is a media instance belonging to a Post, which features
*  special stylized text display, and stores wisdoms pertaining to Dio.
**************************************************************************/
namespace common\models;

use yii\db\ActiveRecord;

class Quote extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%quotes}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['post_id'], 'required'],
            [['post_id'], 'integer'],
            [['body'], 'required'],
            [['body'], 'string', 'length' => [3, 65535]],
            [['source'], 'string', 'length' => [3, 2000]],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'post_id' => 'Post ID',
            'body'    => 'Quote Body',
            'source'  => 'Quote Source'
        ];
    }

    /**
     * relation to Post
     */
    public function getPost()
    {
        return $this->hasOne(Post::className(), ['id' => 'post_id'])->inverseOf('quote');
    }
}
