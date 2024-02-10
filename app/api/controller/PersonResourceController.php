<?php
namespace app\api\controller;

use app\BaseController;
use app\common\Result\Result;
use thans\jwt\facade\JWTAuth;

use app\api\validate\PersonResourceValidate;
use app\api\service\PersonResourceService;
use app\api\model\Resource as ResourceModel;

class PersonResourceController extends BaseController
{
    // 显示单个目录中的所有资源名和更新时间
    public function index($directory_id,$resource_type)
    {   
        $resource_service = new PersonResourceService();
        $resourceInfo = $resource_service->getPersonResources($directory_id,$resource_type);
        if($resourceInfo->isEmpty()){
            return Result::Success('暂无资源~','获取成功');
        }elseif($resourceInfo){
            return Result::Success($resourceInfo, '获取成功');
        }else{
            return Result::Error(null, '获取失败');
        }
        
    }
    // 显示单个个人资源
    public function show($resource_id)
    {   
        $resource_service = new PersonResourceService();
        $resourceInfo = $resource_service->showPersonResource($resource_id);
        if($resourceInfo){
            return Result::Success($resourceInfo, '获取成功');
        }else{
            return Result::Error(null, '获取失败');
        }
    }
    // // 显示资源中的内容
    // public function show($resource_id)
    // {   
    //     $resource_service = new PersonResourceService();
    //     $contentInfo = $resource_service->showContent($resource_id);
    //     if($contentInfo){
    //         return Result::Success($contentInfo, '获取成功');
    //     }else{
    //         return Result::Error(null, '获取失败');
    //     }
    // }

    /**
     * 创建个人资源
     * @return array
     */
    public function create($directory_id)
    {   
        // 获取前端传递过来的资源名称、资源类型和文本框内容
        $resource_name = request()->param('resource_name');
        $resource_type = request()->param('resource_type');
        $content = request()->param('content');      
        
        // 验证用户输入
        $resource_validate = new PersonResourceValidate();
        if (!$resource_validate->scene('create')->check(request()->param())) {
            return Result::Error(null, $resource_validate->getError());
        }

        $count = ResourceModel::where(['directory_id'=>$directory_id,'resource_type'=>$resource_type])->where('resource_name', $resource_name)->count();
        if ($count > 0) {
            return Result::Error(null,"创建失败，资源名已存在");
        }

        // 调用服务 PersonResourceService 处理请求
        $resource_service = new PersonResourceService();
        if ($resource_service->createPersonResource($directory_id,$resource_name,$resource_type,$content)) {
            return Result::Success($resource_name,"资源创建成功");
        } else {
            return Result::Error(null,"资源创建失败");
        }
    }

    // 删除指定资源及资源内容
    public function delete($resource_id)
    {
        $resource_service = new PersonResourceService();
        $result = $resource_service->deletePersonResource($resource_id);
        
        if ($result) {
            return Result::Success("$result","资源删除成功");
        } else {
            return Result::Error(null,"资源删除失败");
        }
    }

}