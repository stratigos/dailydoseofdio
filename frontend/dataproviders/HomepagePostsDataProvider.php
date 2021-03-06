<?php
/*************************************************
* Selects pages of Posts for index vertical
**************************************************/
namespace frontend\dataproviders;

use yii\data\ActiveDataProvider;
use common\models\Post;

class HomepagePostsDataProvider extends ActiveDataProvider
{
    public function init()
    {
        parent::init();
        $this->pagination = false;
        $this->query      = Post::find()->publishedDesc()->with('blogger')->limit(10);
    }
}
