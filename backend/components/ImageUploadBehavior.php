<?php
/*****************************************************************
* Behavior component for uploading a Model's thumbnail attribute.
******************************************************************/

namespace backend\components;

use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;
use yii\image\drivers\Image; // Kohana Image
use backend\models\UploadForm;
use Aws\S3\S3Client;

class ImageUploadBehavior extends Behavior
{
    /**
     * Attribute which is assigned instance of UploadForm. Assigned
     *  to Owner upon initialization of this Behavior.
     * @see ImageUploadBehavior::initializeImageFileAttribute()
     * @see UploadForm::rules()
     */
    public $image_file;

    /**
     * Owner attribute which holds resultant image path.
     */
    public $image_path_field_name = 'image_path';

    /**
     * Owner attribute which holds image's extension (jpg, gif, png)
     */
    public $image_ext_field_name = 'image_ext';

    /**
     * Owner attribute with uniqueness, to assist with uniquely identifying 
     *  image file to model instance by prefixing filename with a 
     *  hashed value. Attribute must be available at time of validation, thus,
     *  primary key may not be suitable. If configured unset, will result in
     *  unix timestamp as image name prefix.
     */
    public $model_unique_attr;

    /**
     * base directory for file / image uploads
     * @see getUploadBaseDir()
     */
    private $upload_base_dir;

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_INIT            => 'initializeImageFileAttribute',
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'uploadToCDN',
            ActiveRecord::EVENT_AFTER_DELETE    => 'deleteFromCDN'
        ];
    }

    /**
     * returns $this->owner classname with no namespacing
     * @todo add this functionality to a child of ActiveRecord, and have
     *  all classes inherit from it (DDODActiveRecord?)
     * @return String
     */
    public function getOwnerClassName()
    {
        return substr($this->owner->className(), (strrpos($this->owner->className(), '\\') + 1));
    }

    /**
     * get base directory for uploads
     */
    public function getUploadBaseDir()
    {
        if(!isset($this->upload_base_dir)) {
            $this->upload_base_dir = Yii::$app->params['uploadBaseDir'];
        }
        return $this->upload_base_dir;
    }

    /**
     * get base directory path for image upload
     */
    public function getPartialDirPath()
    {
        return $this->uploadBaseDir . strtolower($this->ownerClassName);
    }

    /**
     * get full directory path to file, for directory creation, 
     *  and file streaming/upload to CDN
     */
    public function getFullDirPath()
    {
        return Yii::getAlias('@webroot') . '/' . $this->partialDirPath;
    }

    /**
     * assigns an instance of UploadForm to Owner's image file attribute
     */
    public function initializeImageFileAttribute()
    {
        $this->owner->image_file = new UploadForm();
    }

    /**
     * Retrieves uploaded instance from $owner, via $owner's instance of
     *  UploadForm, and performs upload. Image is loaded via UploadedFile,
     *  validated, saved locally, sent to the CDN, then deleted locally, and
     *  the resulting filename is saved to the $owner's 
     *  $image_path_field_name. Must be evoked before $owner->save().
     *  @param NULL
     *  @return Boolean
     *   Value representing successful upload, or failure.  
     */
    public function uploadToCDN()
    {
        // return value which describes if $owner should complete or fail
        //  on save() routine
        $process_complete = FALSE;

        // check to ensure UploadForm instance is loaded into $owner's upload field
        if(!empty($this->owner->image_file)) {
            $image_file        = $this->owner->image_file;
            $image_file->image = UploadedFile::getInstance($image_file, 'image');

            // check to ensure a file was selected
            if(!empty($image_file->image)) {
                if($image_file->validate()) {
                    // create local file, and local directory if necessary
                    $uploaded = NULL;
                    $filename = NULL;
                    if(!file_exists($this->fullDirPath) && !is_dir($this->fullDirPath)) {
                        mkdir($this->fullDirPath, 0774);         
                    } 
                    if(is_dir($this->fullDirPath)) {
                        $filename = $this->partialDirPath . '/' .
                            (
                                (isset($this->model_unique_attr) && isset($this->owner->{$this->model_unique_attr})) ?
                                    md5($this->owner->{$this->model_unique_attr}) :
                                    time()
                            ) .
                            '-' . md5($image_file->image->baseName);
                        ;
                        $filename_ext = $filename . '.' . $image_file->image->extension;
                        // save the file locally, then upload to CDN
                        if($image_file->image->saveAs($filename_ext)) {
                            $full_filename = Yii::getAlias('@webroot') . '/' . $filename_ext;
                            if(file_exists($full_filename)) {
                                $uploaded = $this->_uploadToS3($filename_ext, $full_filename);
                                // save the image's path to the model instance,
                                //  upload any additional sizes, and delete local file
                                if($uploaded) {
                                    $this->owner->{$this->image_path_field_name} = $filename;
                                    $this->owner->{$this->image_ext_field_name}  = $image_file->image->extension;
                                    $_ownerClassLC = strtolower($this->ownerClassName);
                                    if(
                                        isset(Yii::$app->params['imageSizes'][$_ownerClassLC]) &&
                                        !empty(Yii::$app->params['imageSizes'][$_ownerClassLC])
                                    ) {
                                        foreach(Yii::$app->params['imageSizes'][$_ownerClassLC] as $size_key => $sizes) {
                                            $_resized_name = $filename . $size_key . '.' . $image_file->image->extension;

                                            // create instance of Kohana image resizer
                                            $_full_resized_name = Yii::getAlias('@webroot') . '/' . $_resized_name;                                        
                                            $_image             = Yii::$app->image->load($full_filename);

                                            // resizes to WxH, keeping original aspect ratio, saving to new file
                                            $_image->
                                                resize($sizes['width'], $sizes['height'], Image::INVERSE)->
                                                save($_full_resized_name, $sizes['quality'])
                                            ;

                                            if(!($this->_uploadToS3($_resized_name, $_full_resized_name))) {
                                                // should probably flag as some kind of warning, and log the error
                                                error_log("\n\n UPLOAD RESIZED TO S3 FAIL: {$_full_resized_name} \n\n");
                                            }

                                            unlink($_full_resized_name);
                                        }
                                    }
                                    // TODO: GET MULTIPLE FLASH MESSAGES WORKING (setFlash line below wont display)
                                    // Yii::$app->session->setFlash('success', "Image {$filename} uploaded!");
                                    unlink($full_filename);
                                } else {
                                    Yii::$app->session->setFlash(
                                        'danger',
                                        "SOMETHING IS FUCKED WITH S3, IMAGE UPLOAD FAILED: {$full_filename}!"
                                    );
                                }
                                $process_complete = TRUE;
                            } else {
                                Yii::$app->session->setFlash(
                                    'error',
                                    "IMAGE UPLOAD FAILED: UNABLE TO ACCESS LOCALLY CREATED FILE: {$full_filename}!"
                                );
                            }
                        } else {
                            Yii::$app->session->setFlash(
                                'error',
                                "IMAGE UPLOAD FAILED: UNABLE TO SAVE IMAGE LOCALLY: {$filename}!"
                            );
                        }
                    } else {
                        Yii::$app->session->setFlash(
                            'error',
                            "IAMGE UPLOAD FAILED: UNABLE TO CREATE LOCAL DIRECTORY TO SAVE FILES: {$this->fullDirPath}!"
                        );
                    }

                } else {
                    $this->owner->addError('image_file', $this->owner->image_file->getErrors());
                }
            }
        } else {
            $process_complete = TRUE;
        }

        return $process_complete;
    }

    /**
     * helper function for Amazon S3 upload routine
     * @todo add AWS environment params to config, load into config from getenv
     *  to maintain consistency with using app config  
     * @param $filename String 
     *  name of object in S3
     * @param $source_file String
     *  full path to image being uploaded, and filename
     * @return Boolean
     */
    private function _uploadToS3($filename, $source_file)
    {
        $process_complete = FALSE;
        $client  = S3Client::factory(
            [
                'key'    => getenv('AWS_ACCESS_KEY_ID'),
                'secret' => getenv('AWS_SECRET_ACCESS_KEY')
            ]
        );
        $uploaded = $client->putObject(
            [
                'Bucket'     => Yii::$app->params['s3Bucket'],
                'Key'        => $filename,
                'SourceFile' => $source_file,
                'ACL'        => 'public-read'
            ]
        );
        if(!empty($uploaded) && isset($uploaded['ObjectURL']) && !empty($uploaded['ObjectURL'])) {
            $process_complete = TRUE;
        }

        return $process_complete;
    }

    /**
     * @todo THIS
     */
    public function deleteFromCDN()
    {
        error_log("\n\n DELETE THIS OBJECT'S IMAGE ASSETS FROM CDN! \n\n");
        return;
    }
}
