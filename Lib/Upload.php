<?php


namespace App\Lib;

use Cake\Filesystem\Folder;
use Cake\Routing\Router;
use Intervention\Image\ImageManager;
use Upload\File;
use Upload\Storage\FileSystem;

/**
 * 文件上传类
 * 依赖以下包
 * composer require intervention/image  // 图片处理
 * composer require codeguy/upload      // 公用上传
 *
 * Class Upload
 *
 * 使用方法：
 * $uploader = new Upload($options); // $options可配置项为 [path: 'webroot下相对存储地址，默认为files', size: '最大上传文件大小，默认为5M']
 * $res = $uploader->file(); // 文件上传
 * $res = $uploader->media(); // 媒体文件上传
 * $res = $uploader->image(); // 图片上传
 *
 * 若$res返回为true则代表上传成功，否则上传失败
 * 若成功：
 * $uploader->getFile(); // 上传成功后查看上传信息,['path' => '项目相对地址', 'fullPath' => '绝对地址']
 * $uploader->delete(); // 删除上传成功的文件
 * 失败：
 * $uploader->getMsg(); // 查看失败原因
 *
 * 图片专用上传：
 * 若上传为图片，可开启图片专用上传功能，提供了图片压缩，大小自适应改变等功能
 *
     $uploader->image([
        'quality' => 60, // 图片质量 0 - 100
        'format' => 'jpg', // 图片保存格式化类型, 若为空则自动根据类型判断
        'maxWidth' => 560 // 最大宽度，超过此宽度会比例缩小
    ])
 *
 *
 * @package App\Lib
 */
class Upload
{
    private $path = 'files'; // 基本存储地址
    private $size = '5M'; // 默认上传文件最大大小
    private $originalName;
    private $msg = null; // 消息提醒
    private $fileData = [];
    private $staticPath = false; // 是否用静态路径

    private $errorI18n = [
        'File size is too large' => '文件大小超过最大限制',
        'File size is too small' => '文件大小不足最小限制',
    ];

    private $mimeType = [
        'image' => [
            'image/png', 'image/gif', 'image/jpg', 'image/jpeg', 'image/x-icon', 'image/gif', 'image/x-ms-bmp'
        ],
        'media' => [
            'application/x-shockwave-flash', 'audio/mpeg', 'video/vnd.rn-realvideo', 'video/mpeg', 'audio/mp4', 'video/mp4', 'audio/wav
'
        ],
        'file' => [
            'application/zip', 'application/x-7z-compressed', 'application/vnd.ms-excel', '	application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', '	application/xml', 'application/x-tar', 'application/x-rar-compressed', 'application/pdf', 'application/msword', 'text/csv', 'application/x-bzip', 'application/octet-stream','image/png', 'image/gif', 'image/jpg', 'image/jpeg', 'image/x-icon', 'image/gif', 'image/x-ms-bmp',  'application/x-shockwave-flash', 'audio/mpeg', 'video/vnd.rn-realvideo', 'video/mpeg', 'audio/mp4', 'video/mp4', 'audio/wav'
        ]
    ];

    private $type = ['image', 'media', 'file'];

    public function __construct($options = [])
    {
        foreach (['path', 'size', 'staticPath'] as $item) {
            if (isset($options[$item])) {
                $this->$item = $options[$item];
            }
        }
        if (is_string($this->size)) {
            $this->size = \Upload\File::humanReadableToBytes($this->size);
        }
    }

    /**
     * 生成文件路径
     * @param null $type
     * @return bool|string
     */
    private function setFolder($type = null)
    {
        $time = date('Ymd');
        $folder = new Folder();

        if ($this->staticPath) {
            $path = $this->path;
        } else {
            if (in_array($type, $this->type)) {
                $path = $this->path . '/' . $type . '/' . $time;
            } else {
                $path = $this->path . '/' . 'default' . '/' . $time;
            }
        }

        if ($folder->create(WWW_ROOT . $path)) {
            return $path;
        } else {
            return false;
        }
    }

    /**
     * 通用保存公用方法
     * @param $path
     * @param File $file
     * @return bool
     */
    private function save($path, File $file)
    {
        $errors = false;

        try {
            // Success!
            $file->upload();
        } catch (\Exception $e) {
            // Fail!
            $errors = current($file->getErrors());
            if (isset($this->errorI18n[$errors])) {
                $errors = $this->errorI18n[$errors];
            }
        }

        if ($errors) {
            $this->msg = $errors;
            return false;
        }

        $filePath = $path . '/' . $file->getName() . '.' . $file->getExtension();
        $this->msg = '上传成功';
        $this->fileData = [
            'path' => $filePath,
            'fullPath' => WWW_ROOT . $filePath,
            'name' => $this->originalName . '.' . $file->getExtension(),
            'viewPath' => Router::url('/' . $filePath, true)
        ];
        return true;
    }

    /**
     * 文件上传公共方法
     * @param $type null|string 文件类型
     * @param $fileName string upload字段
     * @return array|bool
     */
    private function upload($type = null, $fileName = 'file')
    {
        // 文件类型，image,media,file
        $type = empty($type) ? 'file' : $type;

        if ($path = $this->setFolder($type)) {
            if ($type == 'image' && $this->_imageConfig) {
                return $this->__uploadImg($path, $fileName);
            }
            $storage = new FileSystem($path);
            $file = new File($fileName, $storage);
            $this->originalName = $file->getName();

            $new_filename = uniqid();
            $file->setName($new_filename);

            $validate = array(
                new \Upload\Validation\Size($this->size)
            );
            if ($type != 'file') {
                if (in_array($type, $this->type)) {
                    $mimeType = $this->mimeType[$type];
                } else {
                    $mimeType = [];
                    foreach ($this->mimeType as $item) {
                        $mimeType = array_merge($mimeType, $item);
                    }
                }
                $validate[] = new \Upload\Validation\Mimetype($mimeType);
            }


            $file->addValidations($validate);

            return $this->save($path, $file);
        }

        $this->msg = '上传文件失败！请检查路径权限';
        return false;
    }


    /**
     * 图片专用上传
     *
     * @param $path
     * @param $fileName
     * @return bool
     */
    private function __uploadImg($path, $fileName)
    {
        $manager = new ImageManager();
        $img = $manager->make($_FILES[$fileName]['tmp_name']);
        $size = $img->filesize();

        if ($size > $this->size) {
            $this->msg = '文件大小超过最大限制';
            return false;
        }

        $originalName = $_FILES[$fileName]['name'];
        $this->originalName = basename($originalName);
        if ($this->_imageConfig['maxWidth']) {
            $img->widen($this->_imageConfig['maxWidth'], function ($constraint) {
                $constraint->upsize();
            });
        }
        if ($this->_imageConfig['format'] === null) {
            $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        } else {
            $extension = $this->_imageConfig['format'];
        }
        $filePath = $path . '/' . uniqid() . '.' . $extension;
        try {
            $img->save($filePath, $this->_imageConfig['quality']);
        } catch (\Exception $e) {
            $this->msg = '上传失败';
            return false;
        }
        $this->msg = '上传成功';
        $this->fileData = [
            'path' => $filePath,
            'fullPath' => WWW_ROOT . $filePath,
            'name' => $originalName,
            'viewPath' => Router::url('/' . $filePath, true)
        ];
        return true;
    }


    /**
     * 图片上传专用配置项
     * @var array|null
     */
    private $_imageDefault = [
        'quality' => 90, // 图片质量 0 - 100
        'format' => null, // 图片保存格式化类型, 若为空则自动根据类型判断
        'maxWidth' => false // 最大宽度
    ];

    /**
     * 图片配置，若为null则为通用上传，否则为专用上传。
     * @var null|array
     */
    private $_imageConfig = null;

    /**
     * 图片上传
     * @param $options null|array
     * @return array|bool
     */
    public function image($options = null)
    {
        if (is_array($options)) {
            $options = array_merge($this->_imageDefault, $options);
        } else {
            $options = null;
        }
        $this->_imageConfig = $options;
        return $this->upload('image');
    }

    /**
     * 媒体上传
     * @return array|bool
     */
    public function media()
    {
        return $this->upload('media');
    }

    /**
     * 其他文件上传
     * @return array|bool
     */
    public function file()
    {
        return $this->upload('file');
    }


    /**
     * 回传消息
     * @return null
     */
    public function getMsg()
    {
        return $this->msg;
    }

    /**
     * 获取已保存文件地址信息
     * @return array
     */
    public function getFile()
    {
        return $this->fileData;
    }


    /**
     * 删除上传文件
     * @return bool|null
     */
    public function delete()
    {
        if (isset($this->fileData['fullPath']) && !empty($this->fileData['fullPath'])) {
            return (new \Cake\Filesystem\File($this->fileData['fullPath'], false))->delete();
        }
        return null;
    }



}
