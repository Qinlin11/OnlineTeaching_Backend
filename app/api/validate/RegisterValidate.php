<?php
declare (strict_types = 1);

namespace app\api\validate;

use app\api\model\User as  UserModel;
use think\Validate;

class RegisterValidate extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'phone' => 'require|mobile|unique:user,phone',
        'password' => 'require|length:6,20',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */
    protected $message = [
        'phone.require' => '手机号不能为空',
        'phone.mobile' => '手机号格式不正确',
        'phone.unique' => '手机号已注册',
        'password.require' => '密码不能为空',
        'password.length' => '密码长度必须在6-20个字符之间',

    ];



}
