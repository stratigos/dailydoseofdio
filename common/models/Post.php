<?php
/****************************************************************************
* Post is the 'atomic' content piece - representing a single content entity 
* (i.e., a blog post). Each instance may have relational media, such as
* a Video or Quote. 
*****************************************************************************/
namespace common\models;

use Yii;
use yii\db\ActiveRecord;

class Post extends ActiveRecord
{
    /**
     * default values for posts.status
     */
    const STATUS_DRAFT     = 0;
    const STATUS_PUBLISHED = 1;

    /**
     * values for posts.type_id, representing Post media types
     */
    const POST_TYPE_TEXT  = 0;
    const POST_TYPE_VIDEO = 1;
    const POST_TYPE_QUOTE = 2;
    const POST_TYPE_IMAGE = 3;

    /**
     * value to represent user-facing, presentable date format
     */
    const DATE_FORMAT = 'F dS, Y';

    /**
     * Regular expression used to validate posts.shortname such that only lc letters,
     *  numbers, and dashes are allowed.
     */
    private static $shortnameFriendlyPattern = '/[^a-z0-9-]+/';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%posts}}';
    }

    /**
     * Overriding find() to allow for custom scopes
     * @inheritdoc
     * @return PostQuery
     */
    public static function find()
    {
        return new PostQuery(get_called_class());
    }

    /**
     * returns an array of each posts.type_id value as keys, with
     *  text representations of the types as values.
     */
    public static function getMediaTypes()
    {
        return [
            self::POST_TYPE_TEXT  => 'text',
            self::POST_TYPE_VIDEO => 'video',
            self::POST_TYPE_QUOTE => 'quote'
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ]
            ],
            'permaLinkBehavior' => [
                'class'         => 'common\behaviors\PermaLinkBehavior',
                'pathname'      => 'dose',
                'shortnameAttr' => 'shortname',
                'linknameAttr'  => 'title'
            ]
        ];

        if (isset(Yii::$app->params['isBackend']) && Yii::$app->params['isBackend']) {
            $behaviors['imageUpload'] = [
                'class'             => 'backend\components\ImageUploadBehavior',
                'model_unique_attr' => 'shortname'
            ];
            $behaviors['postTagsAttribution'] = [
                'class' => 'backend\components\PostTagsAttributionBehavior',
            ];
        }

        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type_id'], 'required'],
            [['type_id'], 'integer', 'min' => 0, 'max' => 3],
            [['category_id'], 'required'],
            [['category_id'], 'integer', 'min' => 0],
            [['blog_id'], 'required'],
            [['blog_id'], 'integer', 'min' => 0],
            [['blogger_id'], 'required'],
            [['blogger_id'], 'integer', 'min' => 0],
            [['title'], 'required'],
            [['title'], 'string', 'length' => [3, 128]],
            [['shortname'], 'required'],
            [['shortname'], 'string', 'length' => [3, 128]],
            [['shortname'], 'unique'],
            [['shortname'], 'validateShortnameURLFriendly'],
            [['body'], 'string', 'max' => 65535],
            [['status'], 'required'],
            [['status'], 'integer', 'min' => self::STATUS_DRAFT, 'max' => self::STATUS_PUBLISHED],
        ];
    }

    /**
     * validate posts.shortname such that only url-friendly characters are allowed
     */
    public function validateShortnameURLFriendly($attribute)
    {
        $value = $this->$attribute;
        if (preg_match(self::$shortnameFriendlyPattern, $value)) {
            $this->addError(
                $attribute,
                'Post shortname can only contain lower-case letters, numbers, and dashes.'
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'type_id'     => 'Type ID',
            'category_id' => 'Category ID',
            'blog_id'     => 'Blog ID',
            'blogger_id'  => 'Blogger ID',
            'title'       => 'Title',
            'shortname'   => 'Shortname',
            'body'        => 'Body',
            'status'      => 'Status'
        ];
    }

    /**
     * get the string literal associated with a Post's media type_id.
     * Does not return a value for the default type, 'text'.
     * @return String
     *  String literal representation of media type (e.g., 'quote'),
     *  or NULL if no media.
     */
    public function getMediaTypeName()
    {
        $name        = NULL;
        $media_types = self::getMediaTypes();
        if ($this->type_id && isset($media_types[$this->type_id])) {
            $name = $media_types[$this->type_id];
        }

        return $name;
    }

    /**
     * return the relation to the media type associated with a Post
     * @see getMediaTypes()
     * @return Multi
     *  Instance of this Post's Video, Quote, or Image, if Post has
     *  related object, else NULL.
     */
    public function getMedia()
    {
        $media = NULL;
        switch($this->type_id) {
            case self::POST_TYPE_VIDEO :
                $media = $this->video;
                break;
            case self::POST_TYPE_QUOTE :
                $media = $this->quote;
                break;
        }
        return $media;
    }

    /**
     * relation to Blog
     */
    public function getBlog()
    {
        return $this->hasOne(Blog::className(), ['id' => 'blog_id'])->inverseOf('posts');
    }

    /**
     * relation to Blogger
     */
    public function getBlogger()
    {
        return $this->hasOne(Blogger::className(), ['id' => 'blogger_id'])->inverseOf('posts');
    }

    /**
     * relation to Category
     */
    public function getCategory()
    {
        return $this->hasOne(Category::className(), ['id' => 'category_id'])->inverseOf('posts');
    }

    /**
     * intermediate relation to Tags (PostTag)
     * @see Post::getTags()
     */
    public function getPostTags()
    {
        return $this->hasMany(PostTag::className(), ['post_id' => 'id']);
    }

    /**
     * relation to Tags
     * @see Post::getPostTags()
     */
    public function getTags()
    {
        return $this->hasMany(Tag::className(), ['id' => 'tag_id'])->via('postTags');
    }

    /**
     * relation to a Quote
     */
    public function getQuote()
    {
        return $this->hasOne(Quote::className(), ['post_id' => 'id'])->inverseOf('post');
    }

    /**
     * relation to a Video
     */
    public function getVideo()
    {
        return $this->hasOne(Video::className(), ['post_id' => 'id'])->inverseOf('post');
    }

    /**
     * relation to PromotedPosts
     */
    public function getPromotedPosts()
    {
        return $this->hasMany(PromotedPost::className(), ['post_id' => 'id'])->inverseOf('post');
    }

    /**
     * get full url to a Post's image.
     * @param $size_key String
     *  name of a configured image size (e.g., '250x155')
     * @return String
     */
    public function getImage($size_key = '')
    {
        return isset($this->image_path) ?
            Yii::$app->params['imageDomain'] . $this->image_path  . $size_key . '.' . $this->image_ext :
            null
        ;
    }

    /**
     * get a summary of the Post body, for display in lists
     * @param $length Int
     *  Length of summarized text, after tags removed. Defaults to 200.
     * @param $allowable_tags String
     *  List of HTML tags to allow.
     *  @see http://php.net/manual/en/function.strip-tags.php
     * @return String
     */
    public function getSummary($length = 200, $allowable_tags = '<i><b><em><strong>')
    {
        $summary = null;

        if (!empty($this->body)) {
            $summary = substr(strip_tags($this->body, $allowable_tags), 0, $length);
            if ($length < strlen($this->body)) {
                $summary .= ' &hellip;';
            }
        }

        return $summary;
    }
}
