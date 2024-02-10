<?php
namespace app\api\validate;


use think\Validate;
use app\api\model\Resource;

class PersonResourceValidate extends Validate
{
    protected $rule = [
        'resource_name' => 'require|max:30',
        'resource_type' => 'require',
        'content' => 'require',
    ];

    protected $message = [
        'resource_name.require' => '资源名称不能为空',
        'resource_name.max' => '资源名长度不能超过30个字符',
        'resource_type.require' => '请选择资源类型',
        'content.require' => '文本内容不能为空',
    ];

    protected $scene = [
        'create'  =>  ['resource_name','resource_type','content'],
    ]; 

}