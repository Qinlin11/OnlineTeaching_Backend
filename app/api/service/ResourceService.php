<?php
namespace app\api\service;

use app\common\lib\Editor;
use app\api\model\Resource as ResourceModel;

class ResourceService
{
    // 显示资源库中的所有资源名和更新时间
    public function getResources()
    {
        $resource_model = new ResourceModel();
        $resourceInfo = $resource_model->getResources();
        return $resourceInfo;
    }
    
    // 显示资源库中的所有资源名和更新时间
    public function showTypeResources($resource_type)
    {
        $resource_model = new ResourceModel();
        $resourceInfo = $resource_model->showTypeResources($resource_type);
        return $resourceInfo;
    }
    // 显示单个资源的信息
    public function showResource($resource_id)
    {
        $resource_model = new ResourceModel();
        $resourceInfo = $resource_model->showResource($resource_id);
        return $resourceInfo;
    }

    /**
     * 创建资源
     */
    public function createResource($resource_name,$resource_type,$content)
    {
        // 调用EditorController过滤富文本内容
        $richTextEditor = new Editor();
        $filteredcontent = $richTextEditor->saveContent($content);

        $resource_model = new ResourceModel();
        $resource_model = $resource_model->createResource($resource_name,$resource_type,$filteredcontent);
        
        return $resource_model;
    }
    
    // 获取资源库资源的创建者
    public function getResourceCreator($resource_id)
    {
        $creator_id = ResourceModel::getResourceCreator($resource_id);
        return $creator_id;
    }

    // 删除资源
    public function deleteResource($resource_id)
    {
        $resource = ResourceModel::deleteResource($resource_id);
        return $resource;
    }

    // 搜索资源库资源
    public function searchResource($keyword)
    {
        $assignment_model = new ResourceModel();

        $results = $assignment_model->searchResource($keyword); 

        return $results;
    }

}