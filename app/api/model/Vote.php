<?php
namespace app\api\model;

use thans\jwt\facade\JWTAuth;
use think\Model;
use think\facade\Filesystem;
use think\model\concern\SoftDelete;
use app\api\model\VoteContent as VoteContentModel;

class Vote extends Model
{
    use SoftDelete;
    protected $table = 'think_vote';
    protected $pk = 'id';
    // 自动维护时间戳
    protected $autoWriteTimestamp = true;

    protected $schema = [
        'id' => 'int',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
        'delete_time' => 'datetime',
        'directory_id' => 'int',
        'vote_name' => 'string',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'create_by' => 'int',
    ];

    // 定义与内容模型的一对一的关联关系
    public function content()
    {
        return $this->hasOne(VoteContentModel::class,'vote_id', 'id');
    }

    public static function createVote($directory_id,$vote_name,$start_time,$end_time,$filteredcontent)
    {
        // 通过token获取用户id
        $payload = JWTAuth::auth(); //可验证token, 并获取token中的payload部分
        $user_id = $payload['uid'];

        $vote = new self();
        $vote->save(['create_by' => $user_id,'directory_id' => $directory_id,'vote_name' => $vote_name,'start_time'=>$start_time,'end_time'=>$end_time]);

        VoteContentModel::create(['vote_id'=>$vote->id,'content'=>$filteredcontent]);
        return $vote;
    }
    
    // 显示单个目录内的所有投票名和更新时间
    public static function getVotes($directory_id){
        $vote = new self();
        $voteInfo = $vote->where('directory_id',$directory_id)->select();
        return $voteInfo;
    }
    
    // 获取单个投票的所有信息
    public static function showVote($vote_id){
        $vote = new self();
        $voteInfo = $vote->with('content')->find($vote_id);
        
        return $voteInfo;
    }
    
    //删除指定目录中的投票及其内容
    public static function deleteVote($vote_id)
    {
        // 查找要删除的投票
        $vote = self::find($vote_id);
        // 找到时
        if ($vote) {
            // 删除投票中的内容
            VoteContentModel::where('vote_id',$vote_id)->find()->delete();
            
            // 删除投票
            return $vote->delete();
        }
        // 没找到时
        return false;
    }
}