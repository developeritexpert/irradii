<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;
use app\components\AlertsMessagesProcessor;

/**
 * This is the model class for table "{{%alerts_messages}}".
 *
 * @property int $id
 * @property string|null $document
 */
class AlertsMessages extends ActiveRecord
{
    /**
     * @var UploadedFile
     */
    public $document;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%alerts_messages}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['document'], 'file', 'skipOnEmpty' => false, 'extensions' => 'csv'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if (($this->isNewRecord || $this->scenario == 'update') && $this->document instanceof UploadedFile) {
            $this->deleteDocument();

            // Define upload path
            $uploadPath = Yii::getAlias('@webroot/upload');
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $fileName = $this->document->baseName . '.' . $this->document->extension;
            $filePath = $uploadPath . DIRECTORY_SEPARATOR . $fileName;

            if ($this->document->saveAs($filePath)) {
                $processor = new AlertsMessagesProcessor();
                $processor->processAlertsMessagesFile($filePath);

                // Set the document name to be saved in DB
                $this->document = $fileName;
            }
        }

        return true;
    }

    public function deleteDocument()
    {
        if ($this->document) {
            $documentPath = Yii::getAlias('@webroot/upload') . DIRECTORY_SEPARATOR . $this->document;
            if (is_file($documentPath)) {
                unlink($documentPath);
            }
        }
    }
}
