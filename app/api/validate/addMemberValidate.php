<?php

namespace app\api\validate;

use think\facade\Db;
use think\Validate;

class addMemberValidate extends Validate
{
    protected $rule = [
        'particpant_ids' => 'require|checkMembers',
        'seminar_id' => 'require'
    ];
    protected $message = [
        'particpant_ids.require' => '参与人不能为空',
        'particpant_ids.checkMembers' => '参与人已存在',
        'seminar_id.require' => '请选择教研组'
    ];

    protected function checkMembers($value, $rule, $data = [], $field = '')
    {
        // 验证每个成员是否有效
        foreach ($data['particpant_ids'] as $memberId) {
            //如果$value为空，则直接返回true
            if (!$memberId) {
                return false;
            }
            if (!is_numeric($memberId)) {
                return false; // 成员不是有效的数字
            }

            // 检查成员是否存在于用户表中
            $user = Db::name('seminar_participant')->where('particpant_id', $memberId);
            if (!$user) {
                return false; // 成员不存在
            }
        }

        return true; // 所有成员都有效
    }

}