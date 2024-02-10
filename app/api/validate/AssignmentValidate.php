<?php
namespace app\api\validate;


use think\Validate;
use app\api\model\Resource;

class AssignmentValidate extends Validate
{
    protected $rule = [
        'assignment_name' => 'require|max:30',
        'start_time' => 'require',
        'end_time' => 'require',
        'content' => 'require',
        'keyword' => 'require',
    ];

    protected $message = [
        'assignment_name.require' => '作业名称不能为空',
        'assignment_name.max' => '作业名长度不能超过30个字符',
        'start_time.require' => '请填写开始时间',
        'end_time.require' => '请填写结束时间',
        'content.require' => '文本内容不能为空',
        'keyword.require' => '关键词不能为空',
    ];

    protected $scene = [
        'create'  =>  ['assignment_name','start_time','end_time','content'],
        'search' => ['keyword']
    ]; 

}