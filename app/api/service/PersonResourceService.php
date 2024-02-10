<?php
namespace app\api\service;

use app\common\lib\Editor;
use app\api\model\Resource as ResourceModel;

class PersonResourceService
{
    // 显示目录中的所有资源
    public function getPersonResources($directory_id,$resource_type)
    {
        $resource_model = new ResourceModel();
        $resourceInfo = $resource_model->getPersonResources($directory_id,$resource_type);
        return $resourceInfo;
    }
    // 显示单个个人资源的信息
    public function showPersonResource($resource_id)
    {
        $resource_model = new ResourceModel();
        $resourceInfo = $resource_model->showPersonResource($resource_id);
        return $resourceInfo;
    }
    // 显示资源中的内容
    // public function showContent($resource_id)
    // {
    //     $resource_model = new ResourceModel();
    //     $contentInfo = $resource_model->showContent($resource_id);
    //     return $contentInfo;
    // }

    /**
     * 创建个人
     */
    public function createPersonResource($directory_id,$resource_name,$resource_type,$content)
    {
        // 调用EditorController过滤富文本内容
        $richTextEditor = new Editor();
        $filteredcontent = $richTextEditor->saveContent($content);

        $resource_model = new ResourceModel();
        $resource_model = $resource_model->createPersonResource($directory_id,$resource_name,$resource_type,$filteredcontent);
        
        return $resource_model;
    }

    // 软删除个人资源
    public function deletePersonResource($resource_id)
    {
        $resource = ResourceModel::deletePersonResource($resource_id);
        return $resource;
    }

}