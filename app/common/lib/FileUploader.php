<?php

namespace app\common\lib;

use think\Exception;
use think\facade\Filesystem;

class FileUploader
{
    protected $filename;
    protected $filepath;
    protected $rule;

    public function __construct($filename, $filepath, $rule)
    {
        $this->filename = $filename;
        $this->filepath = $filepath;
        $this->rule = $rule;
    }

    public function upload()
    {
        $files = request()->file($this->filename);
        if (!is_array($files)) {
            $files = [$files];
            $isOne = true;
        }
        $error = ['code' => 200];
        foreach ($files as $file) {
            try {
                validate([$this->filename => $this->rule])->check([$this->filename => $file]);
            } catch (Exception $e) {
                $error = array('code' => 500, 'msg' => $e->getMessage());
                break;
            }
        }
        if ($error['code'] === 200) {
            $fileList = [];
            foreach ($files as $file) {
                $savename = Filesystem::disk('public')->putFile($this->filepath, $file);
                $savename = $this->tranPath($savename, true);
                $extension = pathinfo($file->getOriginalName(), PATHINFO_EXTENSION);
                //获取文件大小
                $fileSize = $file->getSize();
                $fileList[] = $savename;
            }
            if (count($files) === 1 && isset($isOne)) {
                $fileList = config('app.base_url'). '/'. $fileList[0];
            }
            return ['code' => 200, 'fileName' => $fileList, 'fileExtension' => $extension, 'fileSize' => $fileSize];
        } else {
            return $error;
        }
    }

    /**
     * @param $path
     * @param $isAbsolutePath
     * @return string
     */
    protected function tranPath($path, $isAbsolutePath)
    {
        if ($isAbsolutePath) {
            // 如果是绝对路径，去掉public/storage部分，并在开头添加storage/uploads
//            return 'storage/uploads' . substr($path, strlen('public/storage'));
            // 如果是绝对路径，使用dirname获取storage目录下的相对路径，并拼接原始文件名
            $relativePath = 'storage/' . pathinfo($path, PATHINFO_DIRNAME);
            $fileName = pathinfo($path, PATHINFO_BASENAME);
            return $relativePath . '/' . $fileName;
        } else {
            // 在ThinkPHP 6中，默认的文件存储路径是"public/uploads"，可以根据实际情况修改
            $basePath = 'uploads/';
            return $basePath . $path;
        }
    }
}
