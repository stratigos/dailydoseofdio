<?php
/*******************************************
* Blog indices/verticals
********************************************/
namespace frontend\controllers;

use common\models\Blog;
use frontend\dataproviders\BlogsDataProvider;
use frontend\dataproviders\BlogPostsDataProvider;
use yii\web\HttpException;

class BlogController extends FrontendController
{

    /**
     * Produce list of Blog names, images, and URLs
     * @return VOID
     */
    public function actionIndex()
    {
        return $this->render('index', ['blogsDP' => new BlogsDataProvider()]);
    }

    /**
     * Lists all Posts in a Blog
     * @return VOID
     */
    public function actionBlog($shortname)
    {
        if(!($blog = Blog::find()->published()->andWhere(['shortname' => $shortname])->one())) {
            throw new HttpException(404, 'YOUR BLOG AINT FOUND');
        }

        return $this->render(
            'blog',
            [
                'blog'    => $blog,
                'postsDP' => new BlogPostsDataProvider(['blog_id' => $blog->id])
            ]
        );
    }
}
