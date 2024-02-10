<?php
namespace app\api\service;

use app\api\model\Directory as DirectoryModel;

class DirectoryService
{
    
    // 获取所有目录的服务方法
    public function getAllDirectory()
    {
        // 查询所有目录
        $directory = DirectoryModel::getDirectory();

        return $directory;
    }

    /* 创建目录  
     */
    public function createDirectory($directory_name, $user_id)
    {
        $directory_model = new DirectoryModel();

        return $directory_model->createDirectory($directory_name,$user_id);
    }

    // 根据目录 ID 更新目录
    public function updateDirectory($directory_id, $directory_name)
    {
        $directory = DirectoryModel::updateDirectory($directory_id, $directory_name);

        return $directory;
    }
    // 根据目录 ID 软删除目录
    public function deleteDirectory($directory_id)
    {
        $directory = DirectoryModel::deleteDirectory($directory_id);
        return $directory;
    }
}