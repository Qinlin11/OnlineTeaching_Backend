<?php
namespace app\api\controller;

use app\BaseController;
use app\common\Result\Result;

use app\api\validate\DiscussValidate;
use app\api\service\DiscussService;
use app\api\model\Discuss as DiscussModel;

class DiscussController extends BaseController
{
    // 显示单个目录中的所有讨论名和更新时间
    public function index($directory_id)
    {   
        $discuss_service = new DiscussService();
        $discussInfo = $discuss_service->getDiscusses($directory_id);
        if($discussInfo->isEmpty()){
            return Result::Success('暂无讨论~','获取成功');
        }elseif($discussInfo){
            return Result::Success($discussInfo, '获取成功');
        }else{
            return Result::Error(null, '获取失败');
        }       
    }
    // 显示单个讨论信息
    public function show($discuss_id)
    {   
        $discuss_service = new DiscussService();
        $discussInfo = $discuss_service->showDiscuss($discuss_id);
        if($discussInfo){
            return Result::Success($discussInfo, '获取成功');
        }else{
            return Result::Error(null, '获取失败');
        }
    }
    // 创建
    public function create($directory_id)
    {   
        // 获取前端传递过来的讨论标题和文本框内容
        $discuss_name = request()->param('discuss_name');
        $content = request()->param('content');
        $content = request()->param('content');

        // 验证同目录下的讨论标题是否存在
        $count = DiscussModel::where('directory_id',$directory_id)->where('discuss_name', $discuss_name)->count();
        if ($count > 0) {
            return Result::Error(null, '创建失败，讨论标题已存在');
        }
        
        // 调用验证器
        $discuss_validate = new DiscussValidate();
        // 验证用户输入
        if (!$discuss_validate->scene('create')->check(request()->param())) {
            return Result::Error(null, $discuss_validate->getError());
        }
        
        // 调用服务处理请求
        $discuss_service = new DiscussService();
        if ($discuss_service->createDiscuss($directory_id,$discuss_name,$content)) {
            return Result::Success($discuss_name,"讨论创建成功");
        } else {
            return Result::Error(null,"讨论创建失败");
        }
    }
    // 删除指定讨论及讨论内容
    public function delete($discuss_id)
    {
        $discuss_service = new DiscussService();
        $result = $discuss_service->deleteDiscuss($discuss_id);
        
        if ($result) {
            return Result::Success("$result","讨论删除成功");
        } else {
            return Result::Error("$result","讨论删除失败");
        }
    }
    
}