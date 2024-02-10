<?php

// app/validate/LoginValidate.php

namespace app\api\validate;

use app\api\controller\UserController;
use think\Validate;
use app\api\model\User as UserModel;
class LoginValidate extends Validate
{
    protected $rule = [
        'phone' => 'require|mobile|checkRegistered',
        'password' => 'require|min:6|max:32',
//        'code' => 'require|captcha'
    ];

    protected $message = [
        'phone.require' => '手机号不能为空',
        'phone.mobile' => '手机号格式不正确',
        'phone.checkRegistered' => '手机号未注册',
        'password.require' => '密码不能为空',
        'password.max'     => '密码不能超过32个字符',
        'password.min'     => '密码不能小于6个字符',
    ];
    protected function checkRegistered($value, $rule, $data = [], $field = '')
    {
        //查找user表，查看Phone是否存在
        $user = UserModel::where('phone', $value)->find();
        //如果存在，返回正确信息
        //如果不存在，返回错误信息
        if (!$user) {
            return  false;
        }
        return true;
    }
}
