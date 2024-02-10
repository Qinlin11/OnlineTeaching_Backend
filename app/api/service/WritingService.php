<?php
namespace app\api\service;

use app\common\lib\Editor;
use app\api\model\Writing as WritingModel;

class WritingService
{
    /**
     * 新建写作
     */
    public function createWriting($directory_id,$writing_name,$writing_type,$start_time,$end_time,$content)
    {
        // 调用EditorController过滤富文本内容
        $richTextEditor = new Editor();
        $filteredcontent = $richTextEditor->saveContent($content);

        $writing_model = new WritingModel();       
        $writing_model = $writing_model->createWriting($directory_id,$writing_name,$writing_type,$start_time,$end_time,$filteredcontent);
        return $writing_model;
    }
    
    // 显示目录中的所有写作
    public function getWritings($directory_id)
    {
        $writing_model = new WritingModel();
        $writingInfo = $writing_model->getWritings($directory_id);
        return $writingInfo;
    }
    
    // 显示单个写作的信息
    public function showWriting($writing_id)
    {
        $writing_model = new WritingModel();
        $writingInfo = $writing_model->showWriting($writing_id);
        return $writingInfo;
    }
    
    // 根据目录 ID 软删除写作
    public function deleteWriting($writing_id)
    {
        $writing = WritingModel::deleteWriting($writing_id);
        return $writing;
    }
}