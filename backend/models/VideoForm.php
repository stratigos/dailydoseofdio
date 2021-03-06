<?php
/*************************************************************
* Basic class for validating Video embed codes and share URLs
*  - at this time, only Youtube videos supported
**************************************************************/
namespace backend\models;

use yii\base\Model;

class VideoForm extends Model
{
    /**
     * @var String | NULL
     *  video embed code / share url
     */
    public $code;

    /**
     * @var String
     *  regular expression used to parse the video ID from a Youtube code
     * @see https://gist.github.com/afeld/1254889
     *  - note: capture ID with match $5
     */
    private static $youtube_regex = '/(?:youtube(?:-nocookie)?\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/';

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['code'], 'string', 'min' => 10], // arbitrarily chosen minimal length
            [['code'], 'validateVideoService']
        ];
    }

    /**
     * Validates $code such that the correct service is being used,
     *  and that the structure of the embed code or share URL is correct.
     *  - at this time, only Youtube embeds/shares are allowed
     */
    public function validateVideoService($attribute)
    {
        $value   = $this->{$attribute};
        $matches = array();
        if(preg_match(self::$youtube_regex, $value, $matches)) {
            if(!isset($matches[1])) {
                $this->addError($attribute, 'Malformed video code - unable to extract ID');
            }
        } else {
            $this->addError($attribute, 'Video code is not a valid Youtube share URL or embed code.');
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'code' => 'Video Embed Code / Share URL'
        ];
    }

    /**
     * @todo Document
     */
    public function getVideoRegex()
    {
        return self::$youtube_regex;
    }
}
