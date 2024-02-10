<?php
namespace app\api\controller;

use app\api\model\User as UserModel;
use app\api\validate\LoginValidate;
use app\api\validate\RegisterValidate;
use app\BaseController;
use app\common\Result\Result;
use app\Request;
use thans\jwt\facade\JWTAuth;
use think\facade\Db;
use think\Response;


class UserController extends BaseController
{
    // 注册接口
    /**
     * @param Request $request
     * @return Response
     */
    public function register(Request $request): \think\Response
    {
        // 获取请求参数
        $data = request()->post();
        // 使用验证器验证请求数据
        $validate = new RegisterValidate;
        if (!$validate->check($data)) {
            // 验证失败，返回错误信息
            return Result::Error(null,$validate -> getError(), 400);
        }

        $phone = $data['phone'];
        $password = $data['password'];

        // 注册用户
        $user = UserModel::register($phone, $password);


        if ($user) {
            return Result::Success( null,'注册成功');
        } else {
            return Result::Error(null, '注册失败');
        }
    }

    public function login(Request $request): \think\Response
    {
        // 获取请求参数
        $data = $request->post();

        // 使用验证器验证请求数据
        $validate = new LoginValidate();
        if (!$validate->check($data)) {
            // 验证失败，返回错误信息
            return Result::error(null, $validate->getError());
        }
        $phone = $data['phone'];
        $password = $data['password'];

        // 用户登录
        $user = UserModel::login($phone, $password);

        if ($user) {
            return Result::Success($user, '登录成功');
        } else {
            return Result::Error(null, '账号或密码错误');
        }
    }

    public function logout(Request $request): \think\Response
    {
        //获取token
        $token =  JWTAuth::token()->get();;
        // 用户登出
        $user = UserModel::logout($token);

        if ($user) {
            return Result::Success(null, '登出成功');
        }
        return Result::Error(500, '登出失败');

    }

    //通过搜索用户名来查找用户
    public function searchUser(): \think\Response
    {
        $data  = request() ->get();
        $user = UserModel::searchUser($data['keyword']);
        return Result::Success($user, '搜索成功');
    }


    public function getAllUsersExceptSelf()
    {
        try {
            // 通过token获取用户id
            $payload = JWTAuth::auth(); // 可验证token, 并获取token中的payload部分
            $uid = $payload['uid'];

            // 查询用户表中除了自己以外的所有用户
            $users = Db::name('user')
                ->where('id', '<>', $uid)
                ->field('id, username, email, phone, gender, organization, avatar')
                ->select();

            return Result::Success($users, '获取成功');
        } catch (\Exception $e) {
            return Result::Error($e->getMessage());
        }
    }



}