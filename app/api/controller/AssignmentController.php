<?php
namespace app\api\controller;

use app\BaseController;
use app\common\Result\Result;

use app\api\validate\AssignmentValidate;
use app\api\service\AssignmentService;
use app\api\model\Assignment as AssignmentModel;

class AssignmentController extends BaseController
{
    // 显示单个目录中的所有作业名和更新时间
    public function index($directory_id)
    {   
        $assignment_service = new AssignmentService();
        $assignmentInfo = $assignment_service->getAssignments($directory_id);
        if($assignmentInfo->isEmpty()){
            return Result::Success('暂无作业~','获取成功');
        }elseif($assignmentInfo){
            return Result::Success($assignmentInfo, '获取成功');
        }else{
            return Result::Error(null, '获取失败');
        }       
    }
    // 获取单个作业信息
    public function show($assignment_id)
    {   
        // 实例化作业服务
        $assignment_service = new AssignmentService();
        // 调用作业服务中的showAssignment方法获取作业信息
        $assignmentInfo = $assignment_service->showAssignment($assignment_id);
        // 判断获取的作业信息是否为空
        if($assignmentInfo){
            // 返回成功结果
            return Result::Success($assignmentInfo, '获取成功');
        }else{
            // 返回失败结果
            return Result::Error(null, '获取失败');
        }
    }
    // 创建作业
    public function create($directory_id)
    {   
        // 获取前端传递过来的作业名称、开始时间、结束时间、文本框内容
        $assignment_name = request()->param('assignment_name');
        $start_time = request()->param('start_time');
        $end_time = request()->param('end_time');
        $content = request()->param('content');

        // 验证同目录下的作业名是否存在
        $count = AssignmentModel::where('directory_id',$directory_id)->where('assignment_name', $assignment_name)->count();
        if ($count > 0) {
            return Result::Error(null, '创建失败，作业名已存在');
        }
        
        // 调用验证器
        $assignment_validate = new AssignmentValidate();
        // 验证用户输入
        if (!$assignment_validate->scene('create')->check(request()->param())) {
            return Result::Error(null, $assignment_validate->getError());
        }
        
        // 调用服务处理请求
        $assignment_service = new AssignmentService();
        if ($assignment_service->createAssignment($directory_id,$assignment_name,$start_time,$end_time,$content)) {
            return Result::Success($assignment_name,"作业创建成功");
        } else {
            return Result::Error(null,"作业创建失败");
        }
    }
    // 删除指定作业及作业内容
    public function delete($assignment_id)
    {
        $assignment_service = new AssignmentService();
        $result = $assignment_service->deleteAssignment($assignment_id);
        
        if ($result) {
            return Result::Success("$result","作业删除成功");
        } else {
            return Result::Error(null,"作业删除失败");
        }
    }
   // 搜索作业  
    public function search($directory_id)  
    {  
        // 获取搜索关键词  
        $keyword = request()->param('keyword');  
    
        // 调用验证器
        $assignment_validate = new AssignmentValidate();  
        // 验证用户输入
        if (!$assignment_validate->scene('search')->check(request()->param())) {  
            return Result::Error(null, $assignment_validate->getError());  
        }  
        
        $assignment_service = new AssignmentService();
        $search = $assignment_service->searchAssignment($directory_id,$keyword);
        if($search){
            return Result::Success($search, '搜索成功');
        }else{
            return Result::Error(null, '搜索失败');
        }         
    }
    
}

