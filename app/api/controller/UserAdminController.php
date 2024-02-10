<?php
namespace app\api\controller;
//展示所有非管理员用户，删除用户，修改用户，搜索单个用户
use think\facade\Db;
use app\Request;
use think\Response;
use app\BaseController;



class UserAdminController extends BaseController
{

    public function showalluser()
    {
        //查询权限角色为普通用户的所有用户 
        $users = Db::table('think_user')
                    ->where('role', 2)  //查询非管理员用户
                    // ->field('id, username, phone') // 指定查询的字段
                    ->select();

        return json($users);
    }


    public function deleteuser()
    {
        // 获取传入的用户ID参数
        $userId = $this->request->param('id');

        // 验证用户ID是否有效
        if (!$userId) {
            return json(['message' => '无效的用户ID'], 400);
        }

        // 查询要删除的用户
        $user = Db::table('think_user')
                    ->where('role', '2')
                    ->where('id', $userId)
                    ->find();

        // 检查用户是否存在
        if (count($user) == 0) {
            return json(['message' => '用户不存在'], 404);
        }

        // 删除用户
        $user = Db::table('think_user')
                    ->where('id', $userId)
                    ->delete();

        // 返回成功响应
        return json(['message' => '用户删除成功']);
    }


    public function searchuser()
    {
        // 获取查询参数
        $keyword = $this->request->param('keyword');
  
        // 验证查询参数是否为空
        if (!$keyword) {
            return json(['message' => '查询参数不能为空'], 400);
        }
  
        // 查询普通用户，模糊查询用户名，邮箱，手机，id
        $users = Db::table('think_user')
                    ->where('role', '2')
                    ->where('username', 'like', "%{$keyword}%")
                    ->whereOr('email', 'like', "%{$keyword}%")
                    ->whereOr('phone', 'like', "%{$keyword}%")
                    ->whereOr('id', 'like', "%{$keyword}%")
                    ->select();
  
        // 处理查询结果
        if (count($users) == 0) {
            return json(['message' => '没有找到匹配的用户'], 404);
        }
  
        // 返回成功响应和用户列表
        return json(['message' => '用户搜索成功', 'users' => $users]);
    }


    public function updateuser()
    {
        // 获取POST请求中的数据
        $data = $this->request->post();
  
        // 验证数据是否完整  
        if ($data['id'] == NULL || $data['username'] == NULL) {
            return json(['error' => '数据不完整'], 400);
        }
  
        // 根据用户ID获取原始用户信息  
        $user = Db::table('think_user')
                    ->where('id', $data['id'])
                    ->find();
        if (!$user) {
            return json(['error' => '用户不存在'], 400);  
        }
  
        // 验证用户名是否已存在
        $existingUser = Db::table('think_user')
                            ->where('username', $data['username'])
                            ->find();

        if ($existingUser) {
            return json(['error' => '用户名已存在'], 400);
        }

        // 更新用户信息
        $result = Db::table('think_user')
                    ->where('id', $data['id'])
                    ->update(['username' => $data['username']]);

        if ($result) {
            return json(['message' => '用户信息更新成功']);
        } else {
            return json(['error' => '更新失败'], 500);
        }
    }
}





