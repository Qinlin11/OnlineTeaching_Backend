<?php
namespace app\api\service;

use app\api\model\Discuss as DiscussModel;
use app\common\lib\Editor;

class DiscussService
{
    /**
     * 新建讨论
     */
    public function createDiscuss($directory_id,$discuss_name,$content)
    {
        // 调用EditorController过滤富文本内容
        $richTextEditor = new Editor();
        $filteredcontent = $richTextEditor->saveContent($content);

        $discuss_model = new DiscussModel();       
        $discuss_model = $discuss_model->createDiscuss($directory_id,$discuss_name,$filteredcontent);
        return $discuss_model;
    }
    // 显示目录中的所有讨论
    public function getDiscusses($directory_id)
    {
        $discuss_model = new DiscussModel();
        $discussInfo = $discuss_model->getDiscusses($directory_id);
        return $discussInfo;
    }
    
    // 显示单个讨论的信息
    public function showDiscuss($discuss_id)
    {
        $discuss_model = new DiscussModel();
        $discussInfo = $discuss_model->showDiscuss($discuss_id);
        return $discussInfo;
    }

    // 根据目录 ID 软删除讨论
    public function deleteDiscuss($discuss_id)
    {
        $discuss = DiscussModel::deleteDiscuss($discuss_id);
        return $discuss;
    }
}