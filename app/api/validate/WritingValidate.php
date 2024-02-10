<?php
namespace app\api\validate;

use think\Validate;

class WritingValidate extends Validate
{
    protected $rule = [
        'writing_name' => 'require|max:30',
        'writing_type' => 'require',
        'start_time' => 'require',
        'end_time' => 'require',
        'content' => 'require',
    ];

    protected $message = [
        'writing_name.require' => '写作名称不能为空',
        'writing_name.max' => '命名长度不能超过30个字符',
        'writing_type.require' => '请填写写作类型',
        'start_time.require' => '请填写开始时间',
        'end_time.require' => '请填写结束时间',
        'content.require' => '文本内容不能为空',
    ];

    protected $scene = [
        'create'  =>  ['writing_name','writing_type','start_time','end_time','content'],
    ]; 

}