<?php
namespace app\api\validate;


use think\Validate;
use app\api\model\Directory;

class DirectoryValidate extends Validate
{
    protected $rule = [
        'directory_name' => 'require|max:30',

    ];

    protected $message = [
        
        'directory_name.require' => '目录名称不能为空',
        'directory_name.max' => '目录名长度不能超过30个字符',

    ];

}