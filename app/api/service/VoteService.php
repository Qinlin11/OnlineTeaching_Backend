<?php
namespace app\api\service;

use app\api\model\Vote as VoteModel;
use app\common\lib\Editor;

class VoteService
{
    /**
     * 新建投票
     */
    public function createVote($directory_id,$vote_name,$start_time,$end_time,$content)
    {
        // 调用EditorController过滤富文本内容
        $richTextEditor = new Editor();
        $filteredcontent = $richTextEditor->saveContent($content);

        $vote_model = new VoteModel();
        $vote_model = $vote_model->createVote($directory_id,$vote_name,$start_time,$end_time,$filteredcontent);
        
        return $vote_model;
    }

    // 显示目录中的所有投票
    public function getVotes($directory_id)
    {
        $vote_model = new VoteModel();
        $voteInfo = $vote_model->getVotes($directory_id);
        return $voteInfo;
    }
    
    // 显示单个投票的信息
    public function showVote($vote_id)
    {
        $vote_model = new VoteModel();
        $voteInfo = $vote_model->showVote($vote_id);
        return $voteInfo;
    }

    // 根据目录 ID 软删除投票
    public function deleteVote($vote_id)
    {
        $vote = VoteModel::deleteVote($vote_id);
        return $vote;
    }
}