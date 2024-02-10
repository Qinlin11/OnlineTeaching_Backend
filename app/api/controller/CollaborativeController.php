<?php

namespace app\api\controller;

use app\api\model\RichText;
use app\api\model\Seminar;
use app\BaseController;
use app\common\Result\Result;
use thans\jwt\facade\JWTAuth;
use think\Exception;
use think\facade\Db;
use think\Validate;

class CollaborativeController extends BaseController
{
    // 自定义文档即接收富文本内容
    public function uploadRichText()
    {
        $data = request() ->post();

        $rule = [
            'title' => 'require',
            'content' => 'require',
            'seminar_id' => 'require',
        ];

        $message = [
            'content.require' => '请输入内容',
            'title.require' => '请输入标题',
            'seminar_id.require' => '请选择教研组id'
        ];

        $validate = new Validate($rule, $message);
        if(!$validate->check($data)) {
            return Result::returnMsg($validate->getError());
        }
        //获取创建者id
        $payload = JWTAuth::auth(); //可验证token, 并获取token中的payload部分
        $uid = $payload['uid'];
        // 将uid添加到$data数组中
        $data['creator_id'] = (string)$uid->getValue();
        $res = (new \app\api\model\RichText)->save($data);


        if($res){
            return Result::Success($res, '上传成功');
        }else{
            return Result::Error(null, '上传失败');
        }

    }

    // 获取mou文档内容
    public function getAllRichText()
    {
        $data = request() ->get();
        $seminar_id = $data['seminar_id'];
        $page = $data['page'] ?? 1;
        $pageSize = $data['pageSize'] ?? 10;
        try {
            $res = RichText::where('seminar_id', $seminar_id)
                ->page($page, $pageSize)
                ->select();

            $totalCount = RichText::where('seminar_id', $seminar_id)->count();

            $data = [
                'list'       => $res->isEmpty() ? [] : $res->toArray(),
                'pagination' => [
                    'page'       => $page,
                    'page_size'  => $pageSize,
                    'total'      => $totalCount,
                    'total_page' => ceil($totalCount / $pageSize),
                ],
            ];

            return Result::Success($data, '获取成功');
        } catch (\Exception $e) {
            return Result::Error($e->getMessage(), '获取失败');
        }
    }


    //获取某一篇文档
    public function getOneRichText($id)
    {
        try {
            $res = RichText::where('id', $id)->find();

            if(!$res -> isEmpty()){
                return Result::Success($res, '获取成功');
            }else{
                return Result::Error(null, '获取失败');
            }
        }catch (\Exception $e){
            return Result::Error(null, '获取失败');
        }
    }

    //修改某一篇文档
    public function updateRichText()
    {
        $data = request() -> post();

        $rule = [
            'id' => 'require',
            'content' => 'require',
            'title' => 'require',
        ];

        $message = [
            'id.require' => '请选择文档',
            'content.require' => '请输入内容',
            'title.require' => '请输入标题',
        ];

        $validate = new Validate($rule, $message);
        $result = $validate->check($data);

        if (!$result) {
            return Result::Error($validate->getError(), '验证失败');
        }

        $res = RichText::where('id', $data['id'])->update($data);

        if($res){
            return Result::Success($res, '修改成功');
        }else{
            return Result::Error(null, '修改失败');
        }
    }

    //删除某一篇文档
    public function deleteRichText($id)
    {
        try {
            $richText = RichText::find($id);
            if (!$richText) {
                return Result::error(null, '文章不存在');
            }
            $payload = JWTAuth::auth(); //可验证token, 并获取token中的payload部分
            $userId = $payload['uid']->getValue();

            // 获取文章关联的讨论会ID
            $seminarId = $richText->seminar_id;
            // 通过讨论会ID查询对应的讨论会模型
            $seminar = Seminar::find($seminarId);
            // 获取讨论会的创建者ID（组长）
            $hostId = $seminar->host_id;
            if ($richText->creator_id != $userId && $hostId != $userId) {
                return Result::error(null, '没有权限删除该文章');
            }
            $res = $richText->delete();
            if ($res) {
                return Result::success(null, '删除成功');
            } else {
                return Result::error(null, '删除失败');
            }
        } catch (\Exception $e) {
            return Result::error(null, '发生错误：' . $e->getMessage());
        }
    }



    //搜索文档
    public function searchRichText()
    {
        $data = request()->get();
        //通过搜索title或content进行模糊搜索
        // 构造查询条件
        $map[] = ['title', 'like', "%{$data['keyword']}%"];
        $map[] = ['content', 'like', "%{{$data['keyword']}}%"];


        // 使用Query类进行模糊搜索并按updated_at字段排序
        $result = RichText::
        whereOr($map)
            ->order('updated_at', 'desc') // 按updated_at字段降序排序
            ->select();
        if($result){
            return Result::Success($result, '搜索成功');
        }else {
            return Result::Error(null, '搜索失败');
        }
    }
}