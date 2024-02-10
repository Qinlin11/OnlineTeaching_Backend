<?php

namespace app\api\model;

use thans\jwt\facade\JWTAuth;
use think\facade\Db;
use think\Model;

class Seminar extends Model
{
    protected $table = 'think_seminar';
    protected $pk = 'id';
    protected $autoWriteTimestamp = true;

    protected $createTime = 'start_time';
    protected $schema = [
        'id' => 'int',
        'title' => 'string',
        'seminar_name' => 'string',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'host_id' => 'int',
        'meeting_id' => 'int',
    ];

    /**
     * 创建教研组
     * @param $title
     * @param $name
     * @param $end_time
     * @param $host_id
     * @return mixed
     */
    public static function createSeminar($data)
    {
        $seminar = new self();
        $seminar->title = $data['title'];
        $seminar->seminar_name = $data['seminar_name'];
        $seminar->end_time = $data['end_time'];
        //通过token获取用户id
        $payload = JWTAuth::auth(); //可验证token, 并获取token中的payload部分
        $uid = $payload['uid'];
        $seminar->host_id = $uid;
        if($seminar->save()){
            if (isset($data['ids'])) {
                //添加创建者id
                Db::name('seminar_participant')
                    ->save([
                        'seminar_id' => $seminar->id,
                        'particpant_id' => $seminar->host_id,
                        'is_leader' => 0]);
                // 添加成员到关联表（假设关联表名为group_member）
                foreach ($data['ids'] as $memberId) {
                    Db::name('seminar_participant')->insert([
                        'seminar_id' => $seminar->id,
                        'particpant_id' => $memberId,
                    ]);
                }
            }
        }
        return [
            'seminar_id' => $seminar->id,
            'host_id' => $seminar->host_id,
            'title' => $seminar->title,
            'seminar_name' => $seminar->seminar_name,
            'end_time' => $seminar->end_time,
        ];

    }

    //添加教研组成员 向seminar_participant写出数据
    //seminar_id 教研组ID
    //particpant_id 成员id
    public static function addSeminarMember($seminar_id, $particpant_ids)
    {

        // 添加成员到关联表（假设关联表名为group_member）
        foreach ($particpant_ids as $memberId) {
            Db::name('seminar_participant')->insert([
                'seminar_id' => $seminar_id,
                'particpant_id' => $memberId,
            ]);
        }
        return true;

    }

    //删除教研组成员
    //seminar_id 教研组ID
    //particpant_id 成员id
    public static function deleteSeminarMember($seminar_id, $particpant_ids)
    {
        //通过token获取用户id
        $payload = JWTAuth::auth(); //可验证token, 并获取token中的payload部分
        $uid = $payload['uid'];
        //判断你是否是组长
        $is_leader = Db::name('seminar_participant')->where(['seminar_id' => $seminar_id,
            'particpant_id' => $uid])->value('is_leader');
        if ($is_leader == 0) {
            foreach ($particpant_ids as $memberId) {
                Db::name('seminar_participant')->where([
                    'seminar_id' => $seminar_id,
                    'particpant_id' => $memberId,
                ])->delete();
            };

            return 0;
        }else{
            return  1; //无权限
        }



    }

    //获取所有成员
    //seminar_id 教研组ID
    public static function getAllMember($seminarId)
    {
        $userInfo = Db::name('seminar_participant')
            ->alias('sp')
            ->leftJoin('User u', 'u.id = sp.particpant_id')
            ->where('sp.seminar_id', $seminarId)
            ->field('u.id, u.username, u.email, u.phone, u.avatar, sp.is_leader')
            ->order('u.username', 'desc')
            ->select();

        // 遍历结果并标记角色
        // 使用 array_map 函数遍历数组并添加角色字段
        // 将查询结果对象转换为数组
        $userInfoArray = json_decode(json_encode($userInfo), true);

        // 使用 array_map 函数遍历数组并添加角色字段
        $userInfoArray = array_map(function ($user) {
            $user['role'] = $user['is_leader'] == 0 ? '组长' : '成员';
            return $user;
        }, $userInfoArray);

        return $userInfoArray;
    }

    //修改教研组名称
    //seminar_id 教研组ID
    public static function updateSeminarName($seminar_id, $seminar_name = null, $seminar_title = null) {
        //通过token获取用户id
        $payload = JWTAuth::auth(); //可验证token, 并获取token中的payload部分
        $uid = $payload['uid'];
        //判断你是否是组长
        $is_leader = Db::name('seminar_participant')->where(['seminar_id' => $seminar_id,
            'particpant_id' => $uid])->value('is_leader');
        if ($is_leader == 0) {
            $seminar = self::where('id', $seminar_id)->find();
            if ($seminar_name !== null) {
                $seminar->seminar_name = $seminar_name;
            }
            if ($seminar_title !== null) {
                $seminar->title = $seminar_title;
            }
            $seminar->save();
            return 1;
        }else{
            return 0;
        }

    }










}