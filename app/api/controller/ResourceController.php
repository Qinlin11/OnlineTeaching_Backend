<?php

namespace app\api\controller;

use app\api\model\Resource;
use app\BaseController;
use app\common\lib\FileUploader;
use app\common\Result\Result;
use think\facade\Db;
use think\Response;
use think\Validate;
use thans\jwt\facade\JWTAuth;

use app\api\validate\ResourceValidate;
use app\api\service\ResourceService;
use app\api\model\Resource as ResourceModel;

class ResourceController extends BaseController
{
    //组内资源上传
    /**
     * @return Response
     */
    public function groupResource(): \think\Response
    {
        // 检查是否有文件上传
        if (isset($_FILES['file'])) {
            //获取类别和教研组id
            $data = request()->param();
            //验证参数
            $rule = [
                'resource_name|资源名称' => 'require|max:25',
                'seminar_id|组id' => 'require|number',
                'type|资源类型' => 'require|number'
            ];
            $message = [
                'resource_name.require' => '资源名称不能为空',
                'resource_name.max' => '资源名称不能超过25个字符',
                'seminar_id.require' => '组id不能为空',
                'seminar_id.number' => '组id必须是数字',
                'type.require' => '资源类型不能为空',
                'type.number' => '资源类型必须是数字'
            ];
            $validate = new Validate($rule, $message);
            if(!$validate->check($data)) {
                return Result::returnMsg($validate->getError());
            }
            $seminar_id =  $data['seminar_id'];

            $uploader = new FileUploader('file', $seminar_id, 'fileSize:5242880|fileExt:jpg,jpeg,png,pdf,doc,docx');
            $result = $uploader->upload();
            //获取上传者的id和教研组的信息
            if ($result['code'] == 200 && Resource::upload($data, $result)) {

                return Result::Success(['url'=>$result['fileName'], 'fileExtension' => $result['fileExtension']], '上传成功');
            } else {
                return Result::Error(400, $result['msg']);
            }
        } else {
            return Result::Error(400, '未选择文件');
        }
    }

    //获取所用上传文件
    public function getAllUploadFile()
    {
        $data = request()->get();
        $resource_type = $data['resource_type'] ?? null;

        try {
            $query = Db::name('resource')->whereNotNull('file_type')
                ->whereNotNull('file_size')
                ->whereNotNull('file_url');

            if ($resource_type) {
                $query->where('resource_type', $resource_type);
            }

            $results = $query->select();

            return Result::Success($results, '获取成功');
        } catch (\Exception $e) {
            // 这里是发生异常时的处理逻辑，例如记录日志或返回错误信息
            return Result::Error(null, '获取失败');
        }
    }

    //删除组内文件
    public function deleteGroupFile($resource_id)
    {
        // 通过token获取用户id
        $payload = JWTAuth::auth(); //可验证token, 并获取token中的payload部分
        $user_id = $payload['uid']->getValue();

        // 调用服务 ResourceService 处理请求
        $resource_service = new ResourceService();

        // 查询资源的创建者
        $creator_id = $resource_service->getResourceCreator($resource_id);
        // 判断当前用户是否为资源的创建者，若不是则拒绝删除
        if ($user_id !== $creator_id) {
            return Result::Error(null,"您无权删除该资源");
        }

        try {
            $result = Db::name('resource')->where('id', $resource_id)->delete();
            return Result::Success($result, '删除成功');
        } catch (\Exception $e) {
            // 这里是发生异常时的处理逻辑，例如记录日志或返回错误信息
            return Result::Error(null, $e->getMessage());
        }
    }

    //图片上传
    public function uploadImg()
    {
        $data = request()->post();
        if (isset($_FILES['file'])) {
            $rule = [
                'file' => 'require|fileSize:5242880|fileExt:jpg,jpeg,png',
            ];
            $message = [
                'file.require' => '文件不能为空',
                'file.fileSize' => '文件大小不能超过5M',
                'file.fileExt' => '文件格式不正确'
            ];
            $validate = new Validate($rule, $message);
            if(!$validate->check($data)) {
                return Result::returnMsg($validate->getError());
            }
            $uploader = new FileUploader('file', 'uploads', 'fileSize:5242880|fileExt:jpg,jpeg,png');
            $result = $uploader->upload();
//            dump($result);
            //获取上传者的id和教研组的信息
            if ($result['code'] == 200) {
                return Result::Success(['url'=>$result['fileName']], '上传成功');
            }else {
                return Result::Error(400, $result['msg']);
            }

        }else {
            return  Result::Error(400, '未选择文件');
        }
    }


    // 显示资源库所有资源
    public function index()
    {   
        $resource_service = new ResourceService();
        $resourceInfo = $resource_service->getResources();
        if($resourceInfo->isEmpty()){
            return Result::Success('暂无资源~','获取成功');
        }elseif($resourceInfo){
            return Result::Success($resourceInfo, '获取成功');
        }else{
            return Result::Error(null, '获取失败');
        }
        
    }
    // 显示资源库中各类别的资源
    public function showTypeResources($resource_type)
    {   
        $resource_service = new ResourceService();
        $resourceInfo = $resource_service->showTypeResources($resource_type);
        if($resourceInfo->isEmpty()){
            return Result::Success('暂无资源~','获取成功');
        }elseif($resourceInfo){
            return Result::Success($resourceInfo, '获取成功');
        }else{
            return Result::Error(null, '获取失败');
        }
    }
    /**
     * 创建资源
     * @return array
     */
    public function create()
    {   
        // 获取前端传递过来的资源名称、资源类型和文本框内容
        $resource_name = request()->param('resource_name');
        $resource_type = request()->param('resource_type');
        $content = request()->param('content');      
        
        // 验证用户输入
        $resource_validate = new ResourceValidate();
        if (!$resource_validate->scene('create')->check(request()->param())) {
            return Result::Error(null, $resource_validate->getError());
        }

        $count = ResourceModel::where(['directory_id'=>NULL,'resource_type'=>$resource_type])->where('resource_name', $resource_name)->count();
        if ($count > 0) {
            return Result::Error(null,"创建失败，资源名已存在");
        }

        // 调用服务 ResourceService 处理请求
        $resource_service = new ResourceService();
        if ($resource_service->createResource($resource_name,$resource_type,$content)) {
            return Result::Success($resource_name,"资源创建成功");
        } else {
            return Result::Error(null,"资源创建失败");
        }
    }   
    // 删除指定资源及资源内容
    public function delete($resource_id)
    {
        // 通过token获取用户id
        $payload = JWTAuth::auth(); //可验证token, 并获取token中的payload部分
        $user_id = $payload['uid']->getValue();

        // 调用服务 ResourceService 处理请求
        $resource_service = new ResourceService();
        
        // 查询资源的创建者
        $creator_id = $resource_service->getResourceCreator($resource_id);
        // 判断当前用户是否为资源的创建者，若不是则拒绝删除
        if ($user_id !== $creator_id) {
            return Result::Error(null,"您无权删除该资源");
        }

        $result = $resource_service->deleteResource($resource_id);    
        if ($result) {
            return Result::Success("$result","资源删除成功");
        } else {
            return Result::Error(null,"资源删除失败");
        }
    }

    // 显示单个资源信息
    public function info($resource_id)
    {   
        $resource_service = new ResourceService();
        $resourceInfo = $resource_service->showResource($resource_id);
        if($resourceInfo){
            return Result::Success($resourceInfo, '获取成功');
        }else{
            return Result::Error(null, '获取失败');
        }
    }

    // 搜索资源库资源
    public function search()  
    {  
        // 获取搜索关键词  
        $keyword = request()->param('keyword');  
    
        // 调用验证器
        $resource_validate = new ResourceValidate();  
        // 验证用户输入
        if (!$resource_validate->scene('search')->check(request()->param())) {  
            return Result::Error(null, $resource_validate->getError());  
        }  
        
        $resource_service = new ResourceService();
        $search = $resource_service->searchResource($keyword);
        if($search){
            return Result::Success($search, '搜索成功');
        }else{
            return Result::Error(null, '搜索失败');
        }         
    }
}