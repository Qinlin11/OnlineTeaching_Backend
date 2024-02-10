<?php
namespace app\api\controller;

use app\BaseController;
use app\common\Result\Result;

use app\api\validate\VoteValidate;
use app\api\service\VoteService;
use app\api\model\Vote as VoteModel;

class VoteController extends BaseController
{
    // 显示单个目录中的所有投票名和更新时间
    public function index($directory_id)
    {   
        $vote_service = new VoteService();
        $voteInfo = $vote_service->getVotes($directory_id);
        if($voteInfo->isEmpty()){
            return Result::Success('暂无投票~','获取成功');
        }elseif($voteInfo){
            return Result::Success($voteInfo, '获取成功');
        }else{
            return Result::Error(null, '获取失败');
        }       
    }
    // 显示单个投票信息
    public function show($vote_id)
    {   
        $vote_service = new VoteService();
        $voteInfo = $vote_service->showVote($vote_id);
        if($voteInfo){
            return Result::Success($voteInfo, '获取成功');
        }else{
            return Result::Error(null, '获取失败');
        }
    }
    // 创建
    public function create($directory_id)
    {   
        // 获取前端传递过来的作业名称、开始时间、结束时间、文本框内容
        $vote_name = request()->param('vote_name');
        $start_time = request()->param('start_time');
        $end_time = request()->param('end_time');
        $content = request()->param('content');

        // 验证同目录下的写作名是否存在
        $count =VoteModel::where('directory_id',$directory_id)->where('vote_name', $vote_name)->count();
        if ($count > 0) {
            return Result::Error(null, '创建失败，投票名称已存在');
        }
        
        // 调用验证器
        $vote_validate = new VoteValidate();
        // 验证用户输入
        if (!$vote_validate->scene('create')->check(request()->param())) {
            return Result::Error(null, $vote_validate->getError());
        }
        
        // 调用服务处理请求
        $vote_service = new VoteService();
        if ($vote_service->createVote($directory_id,$vote_name,$start_time,$end_time,$content)) {
            return Result::Success($vote_name,"投票创建成功");
        } else {
            return Result::Error(null,"投票创建失败");
        }
    }
    // 删除指定投票及投票内容
    public function delete($vote_id)
    {
        $vote_service = new VoteService();
        $result = $vote_service->deleteVote($vote_id);
        
        if ($result) {
            return Result::Success("$result","投票删除成功");
        } else {
            return Result::Error(null,"投票删除失败");
        }
    }

}