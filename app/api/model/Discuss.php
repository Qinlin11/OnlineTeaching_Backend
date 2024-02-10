<?php
namespace app\api\model;

use thans\jwt\facade\JWTAuth;
use think\Model;
use think\facade\Filesystem;
use think\model\concern\SoftDelete;
use app\api\model\DiscussContent as DiscussContentModel;

class Discuss extends Model
{
    use SoftDelete;
    protected $table = 'think_discuss';
    protected $pk = 'id';
    // 自动维护时间戳
    protected $autoWriteTimestamp = true;

    protected $schema = [
        'id' => 'int',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
        'delete_time' => 'datetime',
        'directory_id' => 'int',
        'discuss_name' => 'string',
        'create_by' => 'int',
    ];

    // 定义与内容模型的一对一的关联关系(不能使用静态方法)
    public function content()
    {
        return $this->hasOne(DiscussContentModel::class,'discuss_id', 'id');
    }

    public static function createDiscuss($directory_id,$discuss_name,$filteredcontent)
    {
        // 通过token获取用户id
        $payload = JWTAuth::auth(); //可验证token, 并获取token中的payload部分
        $user_id = $payload['uid'];

        $discuss = new self();
        $discuss->save(['create_by' => $user_id,'directory_id' => $directory_id,'discuss_name' => $discuss_name]);
        DiscussContentModel::create(['discuss_id'=>$discuss->id,'content'=>$filteredcontent]);

        return $discuss;

    }
    // 显示单个目录内的所有讨论
    public static function getDiscusses($directory_id){
        $discuss = new self();
        $discussInfo = $discuss->where('directory_id',$directory_id)->select();
        return $discussInfo;
    }
    
    // 获取单个讨论的所有信息
    public static function showDiscuss($discuss_id){
        $discuss = new self();
        $discussInfo = $discuss->with('content')->find($discuss_id);
        
        return $discussInfo;
    }
    
    //删除指定目录中的讨论及其内容
    public static function deleteDiscuss($discuss_id)
    {
        // 查找要删除的讨论
        $discuss = self::find($discuss_id);
        // 找到时
        if ($discuss) {
            // 删除讨论中的内容
            DiscussContentModel::where('discuss_id',$discuss_id)->find()->delete();
            
            // 删除讨论
            return $discuss->delete();
        }
        // 没找到时
        return false;
    }
}