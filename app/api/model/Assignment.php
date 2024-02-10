<?php
namespace app\api\model;

use thans\jwt\facade\JWTAuth;
use think\Model;
use think\facade\Filesystem;
use think\model\concern\SoftDelete;
use app\api\model\AssignmentContent as AssignmentContentModel;

class Assignment extends Model
{
    use SoftDelete;
    protected $table = 'think_assignment';
    protected $pk = 'id';
    // 自动维护时间戳
    protected $autoWriteTimestamp = true;

    protected $schema = [
        'id' => 'int',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
        'delete_time' => 'datetime',
        'directory_id' => 'int',
        'assignment_name' => 'string',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'create_by' => 'int',
    ];

    // 定义与内容模型的一对一的关联关系
    public function content()
    {
        return $this->hasOne(AssignmentContentModel::class,'assignment_id', 'id');
    }
    // 创建
    public static function createAssignment($directory_id,$assignment_name,$start_time,$end_time,$filteredcontent)
    {
        // 通过token获取用户id
        $payload = JWTAuth::auth(); //可验证token, 并获取token中的payload部分
        $user_id = $payload['uid'];

        $assignment = new self();
        $assignment->save(['create_by' => $user_id,'directory_id' => $directory_id,'assignment_name' => $assignment_name,'start_time'=>$start_time,'end_time'=>$end_time]);

        AssignmentContentModel::create(['assignment_id'=>$assignment->id,'content'=>$filteredcontent]);

        return $assignment;
    }

    // 显示单个目录内的所有作业
    public static function getAssignments($directory_id){
        $assignment = new self();
        $assignmentInfo = $assignment->where('directory_id',$directory_id)->select();
        return $assignmentInfo;
    }
    
    // 获取单个作业的所有信息
    public static function showAssignment($assignment_id){
        $assignment = new self();
        $assignmentInfo = $assignment->with('content')->find($assignment_id);
        
        return $assignmentInfo;
    }
    
    //删除指定目录中的作业及其内容
    public static function deleteAssignment($assignment_id)
    {
        // 查找要删除的作业
        $assignment = self::find($assignment_id);
        // 找到时
        if ($assignment) {
            // 删除作业中的内容
            AssignmentContentModel::where('assignment_id',$assignment_id)->find()->delete();
            
            // 删除作业
            return $assignment->delete();
        }
        // 没找到时
        return false;
    }

    //搜索作业
    public static function searchAssignment($directory_id,$keyword)  
    {  
        $assignment = new self();  

        $results = $assignment->where('directory_id', $directory_id)
                            ->where('assignment_name', 'like', "%{$keyword}%")  
                            ->order('update_time', 'desc') 
                            ->select();
    
        return $results;  
    }
}