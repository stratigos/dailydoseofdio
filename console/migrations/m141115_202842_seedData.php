<?php
/*****************************************************************************
* Add Posts and other data to the db for testing, etc.
******************************************************************************/
use yii\db\Schema;
use yii\db\Migration;
use common\models\Page;
use common\models\Post;
use common\models\Category;
use common\models\Tag;
use common\models\Blog;
use common\models\Blogger;
use common\models\User;

class m141115_202842_seedData extends Migration
{

    public function up()
    {
        // create initial Page
        $page            = new Page;
        $page->title     = 'About';
        $page->shortname = 'about';
        $page ->body     = '<p><h3><em>We Rock!</em></h3></p>';
        try {
            $page->save();
        } catch(Exception $e) {
            echo("\n");
            echo($e->getMessage());
            echo("\n");
        }
        echo("\n    FINISHED ADDING ABOUT PAGE \n");

        // create initial Posts
        for($i = 0; $i < 24; $i++) {
            $post              = new Post;
            $post->type_id     = 0;
            $post->category_id = 0;
            $post->blog_id     = 0;
            $post->blogger_id  = 0;
            $post->status      = 0;
            $post->title       = "Test Post {$i}";
            $post->shortname   = "test-post-{$i}";
            $post->body        = '<p>This is some initial content: ' . mt_rand() . '</p>';
            try {
                if(!$post->save()) {
                    echo("\n        ERROR SAVING POST {$i} : \n");
                    echo(print_r($post->getErrors(), 1));
                }
            } catch(Exception $e) {
                echo("\n");
                echo($e->getMessage());
                echo("\n");
            }
        }
        echo("\n    FINISHED ADDING INITIAL DRAFT POSTS \n");

        // create initial Categories
        $_cats = [
            ['name' => 'Rainbow',       'shortname' => 'rainbow'],
            ['name' => 'Black Sabbath', 'shortname' => 'black-sabbath'],
            ['name' => 'Dio',           'shortname' => 'dio'] // the band...
        ];
        foreach($_cats as $_cat) {
            $category            = new Category;
            $category->name      = $_cat['name'];
            $category->shortname = $_cat['shortname'];
            try {
                $category->save();
                // if(!$category->save()) {
                //     echo(print_r($category->getErrors(), 1));
                // }
            } catch(Exception $e) {
                echo("\n");
                echo($e->getMessage());
                echo("\n");
            }
        }
        echo("\n    FINISHED ADDING CATEGORIES \n");

        // create some sample Tags
        $_tags = [
            ['name' => '70s',              'shortname' => '70s'],
            ['name' => '80s',              'shortname' => '80s'],
            ['name' => 'Music Industry',   'shortname' => 'music-industry'],
            ['name' => 'Poetic Lyrics',    'shortname' => 'poetic-lyrics'],
            ['name' => 'Rockin Solos',     'shortname' => 'rockin-solos'],
            ['name' => 'Live Performance', 'shortname' => 'live-performance'],
            ['name' => 'Music Videos',     'shortname' => 'rockin-solos'],
            ['name' => 'Covers',           'shortname' => 'covers']
        ];
        foreach($_tags as $_tag) {
            $tag            = new Tag;
            $tag->name      = $_tag['name'];
            $tag->shortname = $_tag['shortname'];
            try {
                $tag->save();
            } catch(Exception $e) {
                echo("\n");
                echo($e->getMessage());
                echo("\n");
            }
        }
        echo("\n    FINISHED ADDING SAMPLE TAGS \n");

        // create initial Blog
        $blog            = new Blog;
        $blog->title     = 'Doses';
        $blog->shortname = 'doses';
        try {
            $blog->save();
        } catch (Exception $e) {
            echo("\n");
            echo($e->getMessage());
            echo("\n");
        }
        echo("\n    FINISHED ADDING FIRST BLOG \n");

        // create initial Blogger
        $blogger            = new Blogger;
        $blogger->name      = 'Todd Stargazer';
        $blogger->shortname = 'todd-stargazer';
        try {
            $blogger->save();
        } catch (Exception $e) {
            echo("\n");
            echo($e->getMessage());
            echo("\n");
        }
        echo("\n    FINISHED ADDING FIRST BLOGGER \n");

        // create initial admin User
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

        return TRUE;
    }

    public function down()
    {
        echo("\n DELETING ALL Post, Page, Category, Tag, Blog, AND Blogger RECORDS... \n");
        Page::deleteAll();
        Post::deleteAll();
        Category::deleteAll();
        Tag::deleteAll();
        Blog::deleteAll();
        Blogger::deleteAll();
        echo("\n ...FINISHED \n");

        return TRUE;
    }
}