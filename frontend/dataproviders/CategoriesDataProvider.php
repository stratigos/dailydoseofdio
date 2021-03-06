<?php
/****************************************************
* Selects all Category records for display in a list
*****************************************************/
namespace frontend\dataproviders;

use yii\data\ActiveDataProvider;
use common\models\Category;

class CategoriesDataProvider extends ActiveDataProvider
{
    public function init()
    {
        parent::init();
        $this->pagination->defaultPageSize = 50;
        $this->pagination->pageSizeParam   = false;
        $this->query 					   = Category::find()->publishedDesc();
    }
}
