<?php

namespace app\api\model;

use thans\jwt\facade\JWTAuth;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Model;

class User extends Model
{
    protected $name = 'user';

    protected $schema = [
        'id' => 'int',                     // 用户ID
        'username' => 'string',            // 用户名
        'password' => 'string',            // 密码
        'email' => 'string',               // 邮箱
        'phone' => 'string',               // 手机号码
        'registration_time' => 'timestamp',// 注册时间
        'avatar' => 'string',              // 头像
        'last_login_time' => 'timestamp',  // 最后登录时间
        'account_status' => 'int',         // 账号状态：1正常、0禁用、-1锁定
        'role' => 'int',                   // 权限角色：1管理员、2普通用户
        'delete_time' => 'datetime',       // 软删除时间
    ];

    /**
     * 注册用户
     * @param $phone
     * @param $password
     * @return bool
     */
    public static function register($phone, $password): bool
    {
        if(self::isRegistered($phone)){
            return false;
        }else {
            $user = new self();
            $user->phone = $phone;
            $user->username = $phone; // 设置默认用户名为手机号
            $user->avatar = 'http://8.130.174.199/storage/uploads/20231230/292659609fceede8c8170dd30ce29ef7.png'; // 设置默认头像地址

            $user->password = password_hash($password, PASSWORD_DEFAULT);

            // 保存用户对象到数据库
            $user->save();

            return true;
        }
    }


    //

    /**
     * 用户登录
     * @param $phone
     * @param $password
     * @return array|null
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function login($phone, $password): ?array
    {
        $user = self::where('phone', $phone)->find();
        //查找用户id
        $user_id = $user->id;

        if ($user && password_verify($password, $user->password)) {


            return [
                'avatar' => $user->avatar,
                'username' => $user->username,
                'phone' => $user->phone,
                'email' => $user->email,
                'token' => JWTAuth::builder(['uid' => $user_id]), // 生成 Token
            ];
        }

        return null;
    }

    /**
     * 判断用户是否注册
     * @param $phone
     * @return User|array|mixed|Model|null
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public static function isRegistered($phone)
    {
        return self::where('phone', $phone)->find();
    }

    //退出登录
    public static function logout($token)
    {
        JWTAuth::invalidate($token);
        return true;
    }

    //通过搜索用户名来查找用户
    public static function searchUser($keyword)
    {
        try {
            $users = self::where(function($query) use ($keyword) {
                $query->where('username', 'like', '%' . $keyword . '%')
                    ->whereOr('phone', $keyword);
            })->select();

            $result = [];
            foreach ($users as $user) {
                $result[] = [
                    'id' => $user->id,
                    'avatar' => $user->avatar,
                    'username' => $user->username,
                    'phone' => $user->phone,
                    'email' => $user->email,
                ];
            }

            return $result; // 返回查询结果
        } catch (DataNotFoundException | ModelNotFoundException | DbException $e) {
            // 异常处理
            return []; // 如果发生异常，返回空数组或者其他适当的默认值
        }
    }

    //修改密码
    public static function updatePassword($userId, $oldPassword, $newPassword)
    {
        // 获取用户
        $user = self::find($userId);
        if ($user) {
            // 验证旧密码
            if (!password_verify($oldPassword, $user->password)) {
                // 旧密码不匹配
                return ['success' => false, 'message' => '旧密码不正确'];
            }

            // 生成新密码的哈希值
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

            // 更新用户密码
            try {
                $user->save(['password' => $hashedPassword]);
                return ['success' => true, 'message' => '密码更新成功, 请重新登录'];
            } catch (\Exception $e) {

                // 更新密码失败
                return ['success' => false, 'message' => '密码更新失败'];
            }
        } else {
            // 用户不存在
            return ['success' => false, 'message' => '用户不存在'];
        }
    }

    public static function updateBasicInfo($userId, $gender = null, $username = null, $organization = null) {
        $user = self::find($userId);
        if ($user) {
            if ($gender !== null) {
                $user->gender = $gender;
            }
            if ($username !== null) {
                $user->username = $username;
            }
            if ($organization !== null) {
                $user->organization = $organization;
            }
            try {
                $user->save();
                return [
                    'id' => $user->id,
                    'avatar' => $user->avatar,
                    'username' => $user->username,
                    'phone' => $user->phone,
                    'email' => $user->email,
                    'organization' => $user->organization,
                    'gender' => $user->gender,
                    ]; // 返回更新后的用户信息
            } catch (\Exception $e) {
                // 更新失败，进行相应的错误处理
                return null;
            }
        }
        return null;
    }

}