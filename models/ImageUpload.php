<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

/*Сюда прописывается логика загрузки картинки*/
class ImageUpload extends Model {
    public $image; //атрибут

    //валидация картинки при загрузке
    public function rules()
    {
        return [
            [['image'], 'required'],
            /*валидация по расширению*/
            [['image'], 'file', 'extensions' => 'jpg,png'],
        ];
    }

    /*метод получения картинки из view, загрузка картинки на сервер
    $currentImage получает название текушей картинки*/
    public function uploadFile(UploadedFile $file, $currentImage)
    {
        $this->image = $file;

        if ($this->validate())
        {
            $this->deleteCurrentImage($currentImage);
            return $this->saveImage();
        }
    }

    /*путь к папке хранения картинок*/
    private function getFolder()
    {
        return Yii::getAlias('@web') . 'uploads/';
    }

    /*здесь генерируется название картинки*/
    private function generateFilename()
    {
        /*хешируем название картинки на md5, затем уникализируем имя uniqid и добавляем расширение*/
        return strtolower(md5(uniqid($this->image->baseName)) . '.' . $this->image->extension);
    }

    public function deleteCurrentImage($currentImage)
    {
        if ($this->fileExists($currentImage))
        {
            unlink($this->getFolder() . $currentImage);
        }
    }

    /*если картинки нет на сервере (удалена), то загрузить новую
    проверяется наличие файла и имя файла перед удалением*/
    public function fileExists($currentImage)
    {
        if (!empty($currentImage) && $currentImage != null)
        {
            return file_exists($this->getFolder() . $currentImage);
        }
    }

    public function saveImage()
    {
        $filename = $this->generateFilename();

        $this->image->saveAs($this->getFolder() . $filename);

        return $filename;
    }

    /*так все выглядит до того, как каждый кусок кода будет запечатан в функцию.*/
//    public function uploadFile(UploadedFile $file, $currentImage)
//    {
//        $this->image = $file;
//        /*валидация каринки*/
//        if ($this->validate())
//        {
//            /*если картинка уже загружена, то заменить каринку
//            за определение наличия картинки отвечает функция file_exists*/
//            if (file_exists(Yii::getAlias('@web') . '/uploads' . $currentImage))
//            {
//                /*если file_exists = true, то заменить картинку*/
//                unlink(Yii::getAlias('@web') . '/uploads' . $currentImage);
//            }
//            /* генерируется цникальное имя картинки, гду strtoolwer написание строчными буквами
//            md5 шифрование названия, uniqid уникализация нпазвания, baseName имя файля */
//            $filename = strtolower(md5(uniqid($file->baseName) . '.' . $file->extension));
//            /*загружаем картинку на сервер*/
//            $file->saveAs(Yii::getAlias('@web') . '/uploads' . $filename);
//            /*возвращаем имя картинки*/
//            return $filename;
//        }
//    }

}