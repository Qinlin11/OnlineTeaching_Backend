<?php

namespace app\api\controller;

use app\api\model\Seminar;
use app\api\model\User;
use app\api\validate\addMemberValidate;
use app\api\validate\SeminarValidate;
use app\BaseController;
use app\common\Result\Result;
use thans\jwt\facade\JWTAuth;
use think\facade\Db;
use think\Response;
use think\Validate;

class SeminarController extends BaseController
{
    //创建教研组
    public function createSeminar()
    {
        $data = request() ->param();

        $validate = new SeminarValidate();
        if(!$validate->check($data)){
            return Result::returnMsg($validate->getError());
        }

        $seminar = Seminar::createSeminar($data);

        if($seminar){
            return Result::Success($seminar, '创建成功');
        }else{
            return Result::returnMsg(400, '创建失败');
        }

    }

    //添加教研组成员
    public function addSeminarMember()
    {
        $data = request() ->post();

        $validate = new addMemberValidate();
        if(!$validate->check($data)) {
            return Result::returnMsg($validate->getError());
        }

        $seminar_id = $data['seminar_id'];
        $particpant_ids = $data['particpant_ids'];

        $seminar = Seminar::addSeminarMember($seminar_id, $particpant_ids);

        if($seminar){
            return Result::Success(null, '添加成功');
        }else{
            return Result::Error(null,'添加失败');
        }
    }

    //删除教研组成员
    public function deleteSeminarMember()
    {
        $data = request() ->post();

        $rule = [
            'seminar_id' => 'require',
            'particpant_ids' => 'require'
        ];
        $message = [
            'seminar_id.require' => '请选择教研组',
            'particpant_id.require' => '请选择成员'
        ];

        $validate = new Validate($rule, $message);
        if(!$validate->check($data)) {
            return Result::returnMsg($validate->getError());
        }

        $seminar_id = $data['seminar_id'];
        $particpant_ids = $data['particpant_ids'];

        $seminar = Seminar::deleteSeminarMember($seminar_id, $particpant_ids);

        if($seminar == 0){
            return Result::Success(null, '删除成功');
        }else if($seminar == 1){
            return Result::Error(null,'无权限');
        }{
            return Result::Error(null,'删除失败');
        }
    }

    //获取教研组所有成员
    public function getAllSeminarMember()
    {
        $data = request() ->get();

        $rule = [
            'seminar_id' => 'require'
        ];
        $message = [
            'seminar_id.require' => '请选择教研组'
        ];

        $validate = new Validate($rule, $message);
        if(!$validate->check($data)) {
            return Result::returnMsg($validate->getError());
        }

        $seminar_id = $data['seminar_id'];

        $seminar = Seminar::getAllMember($seminar_id);
//        $seminar1 = Seminar::where('id',$seminar_id)->leftJoin('user u','u.id=seminar.host_id')->field(['id','title','seminar_name'])
//            ->select();
        $seminar1 = Seminar::alias('s')
            ->join('user u', 'u.id = s.host_id')
            ->where('s.id', $seminar_id)
            ->field('s.id, s.title, s.seminar_name, u.username')
            ->find();

        if($seminar){
            return Result::Success([
                'seminarInfer'=>$seminar1,
                'seminarMember'=>$seminar
            ], '获取成功');
        }else{
            return Result::Error(null,'获取失败');
        }
    }

    //修改教研组主题和名称
    public function editSeminar()
    {
        $data = request() ->post();


        $rule = [
            'seminar_id' => 'require',
            'seminar_name' => 'max:8',
            'seminar_title' => 'max:8'
        ];
        $message = [
            'seminar_id.require' => '请选择教研组',
            'seminar_name.max' => '最大长度为8',
            'seminar_title.require' => '最大长度为8'
        ];

        $validate = new Validate($rule, $message);
        if(!$validate->check($data)) {
            return Result::returnMsg($validate->getError());
        }

        $seminar_id = $data['seminar_id'];
        $seminar_name = $data['seminar_name']?? null;
        $seminar_title = $data['seminar_title']?? null;

        $seminar = new Seminar();
        $res = $seminar -> updateSeminarName($seminar_id, $seminar_name, $seminar_title);

        if($res==1){
            return Result::Success(null, '修改成功');
        }else if($res==0){
            return Result::Error(null, '无权限');
        }else{
            return Result::Error(null, '修改失败');
        }
    }

    //获取用户加入的教研组
    /**
     * @param $userId
     * @return Response
     */
    public function getUserSeminar()
    {
        try {
            //通过token获取用户id
            $payload = JWTAuth::auth(); //可验证token, 并获取token中的payload部分
            $uid = $payload['uid'];
            // 通过参与者表查询对应的seminar_id
            $seminarIds = Db::name('seminar_participant')
                ->where('particpant_id', $uid)
                ->column('seminar_id');
            // 查询seminar_id对应的所有数据
            // 查询seminar_id对应的所有数据，并关联用户表
            $seminars = Seminar::whereIn('s.id', $seminarIds)
                ->alias('s')
                ->join('user u', 'u.id = s.host_id')
                ->field('s.*, u.username as host_name') // 添加字段别名
                ->select();

            return Result::Success($seminars, '获取成功');
        }catch (\Exception $e){
            return Result::Error($e->getMessage());
        }
    }

    //删除教研组
    public function deleteSeminar($id)
    {
        // 使用事务确保数据的一致性
        Db::startTrans();
        try {
            // 删除rich_text表中相关的数据
            Db::name('rich_text')->where('seminar_id', $id)->delete();

            // 删除seminar_participant表中相关的数据
            Db::name('seminar_participant')->where('seminar_id', $id)->delete();

            // 删除seminar表中的数据
            Db::name('seminar')->where('id', $id)->delete();

            Db::commit();
            return Result::Success(null, '删除成功');
        } catch (\Exception $e) {
            Db::rollback();
            return Result::Error(null, $e->getMessage());
        }
    }

    //通过phone添加教研组成员
    public function addMemberSeminar()
    {
        $data = request()->post();
        //数据校验
        $rules = [
            'seminar_id' => 'require|integer',
            'phone' => 'require|mobile',
        ];
        $message = [
            'seminar_id.require' => '教研组id不能为空',
            'seminar_id.integer' => '教研组id必须为整数',
            'phone.require' => '手机号不能为空',
            'phone.mobile' => '手机号格式错误',
        ];
        $result = $this->validate($data, $rules, $message);
        if ($result !== true) {
            return Result::Error(null, $result);
        }
        //教研组id
        $seminarId =  $data['seminar_id'];
        $phone = $data['phone'];

        //通过phone查询用户信息id
        $userId = Db::name('user')->where('phone', $phone)->value('id');
        //判断用户是否存在
        if (!$userId) {
            return Result::Error(null, '该用户不存在');
        }
        // 检查该用户是否已经是该教研组的成员
        $isMember = Db::name('seminar_participant')
            ->where('seminar_id', $seminarId)
            ->where('particpant_id', $userId)
            ->count();

        if ($isMember > 0) {
            return Result::Error(null, '该用户已经是该教研组的成员');
        }

        // 添加成员
        $add = [
            'seminar_id' => $seminarId,
            'particpant_id'=> $userId,
        ];
        try {
            Db::name('seminar_participant')->save($add);
            return Result::Success(null, '添加成功');
        }catch (\Exception $e){
            return Result::Error(null, $e->getMessage());
        }


    }
}