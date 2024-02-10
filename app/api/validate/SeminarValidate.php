<?php
namespace app\api\validate;


use think\Validate;

class SeminarValidate extends Validate
{
    protected $rule = [
        'title' => 'require|unique:seminar,title',
        'seminar_name' => 'require|unique:seminar,seminar_name',


    ];

    protected $message = [
        'title.require' => '教研组主题不能为空',
        'seminar_name.require' => '教研组名称不能为空',
        'title.unique' => '教研组主题已存在',

    ];
}