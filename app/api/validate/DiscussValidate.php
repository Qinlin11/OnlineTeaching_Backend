<?php
namespace app\api\validate;


use think\Validate;

class DiscussValidate extends Validate
{
    protected $rule = [
        'discuss_name' => 'require|max:30',
        'content' => 'require',
    ];

    protected $message = [
        'discuss_name.require' => '讨论标题不能为空',
        'discuss_name.max' => '标题长度不能超过30个字符',
        'content.require' => '文本内容不能为空',
    ];

    protected $scene = [
        'create'  =>  ['discuss_name','content'],
    ]; 

}