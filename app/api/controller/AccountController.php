<?php

namespace app\api\controller;

use app\api\model\User;
use app\BaseController;
use app\common\lib\FileUploader;
use app\common\Result\Result;
use thans\jwt\facade\JWTAuth;
use think\Validate;

class AccountController extends BaseController
{
    //修改密码
    public function changePassword()
    {
        $data = request() ->post();
        $rule = [
            'oldPassword' => 'require',
            'newPassword' => 'require|min:6|max:16|different:oldPassword'
        ];
        $message = [
            'oldPassword.require' => '旧密码不能为空',
            'newPassword.require' => '新密码不能为空',
            'newPassword.min' => '新密码不能少于6位',
            'newPassword.max' => '新密码不能多于16位',
            'newPassword.different' => '新密码不能与旧密码相同'
        ];
        $validate = new Validate($rule, $message);
        if(!$validate->check($data)) {
            return Result::returnMsg($validate->getError());
        }

        // 获取当前登录用户
        $payload = JWTAuth::auth(); //可验证token, 并获取token中的payload部分
        $userId = $payload['uid'];
        //验证密码是否正确
        $result = User::updatePassword($userId, $data['oldPassword'], $data['newPassword']);
        if ($result['success']) {
            //让token失效
            // 使当前用户的 JWT Token 失效
            JWTAuth::invalidate(JWTAuth::token()->get());
            return Result::Success(null, $result['message']);
        } else {
            return Result::Error(null,$result['message']);
        }
    }

    //上传头像或修改头像
    public function uploadAvatar()
    {
        $data = request()->post();
        if (isset($_FILES['file'])) {
            // 获取当前登录用户
            $payload = JWTAuth::auth(); //可验证token, 并获取token中的payload部分
            $userId = $payload['uid'];

            $uploader = new FileUploader('file', 'avatar', 'fileSize:5242880|fileExt:jpg,jpeg,png');
            $result = $uploader->upload();
            if ($result['code'] == 200) {
                $updated = User::find($userId)->save(['avatar' => $result['fileName']]);
                if ($updated) {
                    return Result::Success(['url' => $result['fileName']], '上传成功');
                }
                return Result::Error(null, '上传失败');
            }

        }else{
            return Result::Error(null, '未选择文件');
        }
        return Result::Error(null, '上传失败');
    }

    //修改基本信息
    public function updateBasicInfo()
    {
        $data = request()->post();

        // 获取当前登录用户的用户ID
        $payload = JWTAuth::auth(); // 可验证token，并获取token中的payload部分
        $userId = $payload['uid'];

        $userInfo = User::updateBasicInfo($userId, $data['gender'], $data['username'], $data['organization']);
        if ($userInfo) {
            return Result::Success($userInfo, '修改成功');
        } else {
            return Result::Error(null, '修改失败');
        }
    }

    //修改手机号
    public function updatePhone()
    {
        $data =  request()->post();
        // 获取当前登录用户的用户ID
        $payload = JWTAuth::auth(); // 可验证token，并获取token中的payload部分
        $userId = $payload['uid'];

        // 验证规则
        $rules = [
            'mobile' => 'require|mobile|unique:user,mobile,'.$userId,
            'password' => 'require',
        ];
        $messages = [
            'mobile.require' => '请输入新的手机号',
            'mobile.mobile' => '请输入有效的手机号',
            'mobile.unique' => '手机号已被使用',
            'password.require' => '请输入密码',
        ];
        // 验证参数
        $validate = new Validate($rules, $messages);
        if(!$validate -> check($data)){
            return Result::returnMsg($validate->getError());
        }
        $user = User::where('id', $userId)->find();
        //校验密码
        if(!password_verify($data['password'], $user->password)){
            return Result::Error(null, '密码错误');
        }

        //更新用户手机号
        $user->phone = $data['phone'];
        $user->save();

        // 使当前用户的 JWT Token 失效
        JWTAuth::invalidate($userId);

        // 生成新的 JWT Token
        $newToken = JWTAuth::builder(['user_id' => $userId]);  // 根据用户 ID 生成新的 Token
        return Result::returnMsg(['token' => $newToken], '手机号修改成功，请重新登录');
    }
}