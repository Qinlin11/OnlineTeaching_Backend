<?php
namespace app\api\controller;

use app\BaseController;
use app\common\Result\Result;
use thans\jwt\facade\JWTAuth;

use app\api\validate\DirectoryValidate;
use app\api\service\DirectoryService;
use app\api\model\Directory as DirectoryModel;

class DirectoryController extends BaseController
{
    // //显示登录用户的所有目录名
    public function index()
    {
        $directory_service = new DirectoryService();
        $directory = $directory_service->getAllDirectory();
        // 判断$directory对象是否为空
        if($directory->isEmpty()){
            return Result::Success('暂无目录~','获取成功');
        }elseif($directory){
            return Result::Success($directory, '获取成功');
        }else{
            return Result::Error(null, '获取失败');
        }
    }
    /**
     * 创建目录
     * @return array
     */
    public function create()
    {   
        // 获取前端传递过来的目录名和用户ID
        $directory_name = request()->post('directory_name');
        // 通过token获取用户id
        $payload = JWTAuth::auth(); //可验证token, 并获取token中的payload部分
        $user_id = $payload['uid'];

        $directory_validate = new DirectoryValidate();
        $data = [
            'directory_name' =>$directory_name,
        ];
        // 验证用户输入
        if (!$directory_validate->check($data)) {
            return Result::Error(null, $directory_validate->getError());
        }
        // 判断该用户创建的目录是否已存在
        $count = DirectoryModel::where('host_id', $user_id)->where('directory_name', $directory_name)->count();
        if ($count > 0) {
            return Result::Error(null,"创建失败，目录名已存在");
        }
  
        // 调用服务 DirectoryService 处理请求
        $directory_service = new DirectoryService();
        if ($directory_service->createDirectory($directory_name, $user_id)) {
            return Result::Success($directory_name,"创建目录成功");
        } else {
            return Result::Error(null,"创建目录失败");
        }
    }

    // 修改目录名
    public function update($directory_id,DirectoryService $directoryService, DirectoryValidate $validator)
    {
        
        $params = request()->param();
        $directory_name = $params['directory_name'];
        // 验证请求参数
        if (!$validator->check($params)) {
            return Result::Error(null, $validator->getError());
        }

        // 调用服务层方法修改目录名
        $result = $directoryService->updateDirectory($directory_id,$directory_name);
        if ($result === false) {
            return Result::Error(null,"修改目录失败");
        }

        // 返回成功响应
        return Result::Success($result,"目录修改成功");
    }

    // 删除指定目录及关联资源
    public function delete($directory_id)
    {
        $directory_service = new DirectoryService();
        $result = $directory_service->deleteDirectory($directory_id);

        if ($result) {
            return Result::Success("$result","目录删除成功");
        } else {
            return Result::Error(null,"目录删除失败");
        }
    }
}