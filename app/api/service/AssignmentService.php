<?php
namespace app\api\service;

use app\api\model\Assignment as AssignmentModel;
use app\common\lib\Editor;

class AssignmentService
{
    /**
     * 新建作业
     */
    public function createAssignment($directory_id,$assignment_name,$start_time,$end_time,$content)
    {
        // 调用EditorController保存富文本内容
        $richTextEditor = new Editor();
        $filteredcontent = $richTextEditor->saveContent($content);

        $assignment_model = new AssignmentModel();
        $assignment_model = $assignment_model->createAssignment($directory_id,$assignment_name,$start_time,$end_time,$filteredcontent);

        return $assignment_model;
    }
    // 显示目录中的所有作业
    public function getAssignments($directory_id)
    {
        $assignment_model = new AssignmentModel();
        $assignmentInfo = $assignment_model->getAssignments($directory_id);
        return $assignmentInfo;
    }
    
    // 显示单个作业的信息
    public function showAssignment($assignment_id)
    {
        $assignment_model = new AssignmentModel();
        $assignmentInfo = $assignment_model->showAssignment($assignment_id);
        return $assignmentInfo;
    }
    
    // 根据目录 ID 软删除作业
    public function deleteAssignment($assignment_id)
    {
        $assignment = AssignmentModel::deleteAssignment($assignment_id);
        return $assignment;
    }

    //搜索作业
    public function searchAssignment($directory_id,$keyword)
    {
        $assignment_model = new AssignmentModel();

        $results = $assignment_model->searchAssignment($directory_id,$keyword);

        return $results;
    }

}