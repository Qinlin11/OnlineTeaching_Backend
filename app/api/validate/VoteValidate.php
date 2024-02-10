<?php
namespace app\api\validate;


use think\Validate;

class VoteValidate extends Validate
{
    protected $rule = [
        'vote_name' => 'require|max:30',
        'start_time' => 'require',
        'end_time' => 'require',
        'content' => 'require',
    ];

    protected $message = [
        'vote_name.require' => '投票名称不能为空',
        'vote_name.max' => '命名长度不能超过30个字符',
        'start_time.require' => '请填写开始时间',
        'end_time.require' => '请填写结束时间',
        'content.require' => '文本内容不能为空',
    ];

    protected $scene = [
        'create'  =>  ['vote_name','start_time','end_time','content'],
    ]; 

}