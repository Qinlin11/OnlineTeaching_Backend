<?php
namespace app\api\controller;

use app\BaseController;
use app\common\Result\Result;

use app\api\validate\WritingValidate;
use app\api\service\WritingService;
use app\api\model\Writing as WritingModel;

class WritingController extends BaseController
{
    // 显示单个目录中的所有写作名和更新时间
    public function index($directory_id)
    {   
        $writing_service = new WritingService();
        $writingInfo = $writing_service->getWritings($directory_id);
        if($writingInfo->isEmpty()){
            return Result::Success('暂无写作~','获取成功');
        }elseif($writingInfo){
            return Result::Success($writingInfo, '获取成功');
        }else{
            return Result::Error(null, '获取失败');
        }       
    }
    // 显示单个写作信息
    public function show($writing_id)
    {   
        $writing_service = new WritingService();
        $writingInfo = $writing_service->showWriting($writing_id);
        if($writingInfo){
            return Result::Success($writingInfo, '获取成功');
        }else{
            return Result::Error(null, '获取失败');
        }
    }
    // 创建
    public function create($directory_id)
    {   
        // 获取前端传递过来的作业名称、开始时间、结束时间、文本框内容
        $writing_name = request()->param('writing_name');
        $writing_type = request()->param('writing_type');
        $start_time = request()->param('start_time');
        $end_time = request()->param('end_time');
        $content = request()->param('content');

        // 验证同目录下的写作名是否存在
        $count = WritingModel::where('directory_id',$directory_id)->where('writing_name', $writing_name)->count();
        if ($count > 0) {
            return Result::Error(null, '创建失败，写作名称已存在');
        }
        
        // 调用验证器
        $writing_validate = new WritingValidate();
        // 验证用户输入
        if (!$writing_validate->scene('create')->check(request()->param())) {
            return Result::Error(null, $writing_validate->getError());
        }
        
        // 调用服务处理请求
        $writing_service = new WritingService();
        if ($writing_service->createWriting($directory_id,$writing_name,$writing_type,$start_time,$end_time,$content)) {
            return Result::Success($writing_name,"写作创建成功");
        } else {
            return Result::Error(null,"写作创建失败");
        }
    }
    // 删除指定写作及写作内容
    public function delete($writing_id)
    {
        $writing_service = new WritingService();
        $result = $writing_service->deleteWriting($writing_id);
        
        if ($result) {
            return Result::Success("$result","写作删除成功");
        } else {
            return Result::Error(null,"写作删除失败");
        }
    }

}