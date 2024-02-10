<?php
namespace app\api\model;

use thans\jwt\facade\JWTAuth;
use think\Model;
use think\facade\Filesystem;
use think\model\concern\SoftDelete;
use app\api\model\WritingContent as WritingContentModel;

class Writing extends Model
{
    use SoftDelete;
    protected $table = 'think_writing';
    protected $pk = 'id';
    // 自动维护时间戳
    protected $autoWriteTimestamp = true;
    // protected $createTime = 'create_time';
    // protected $updateTime = 'update_at';

    protected $schema = [
        'id' => 'int',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
        'delete_time' => 'datetime',
        'directory_id' => 'int',
        'writing_name' => 'string',
        'writing_type' => 'string',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'create_by' => 'int',
    ];

    // 定义与内容模型的一对一的关联关系
    public function content()
    {
        return $this->hasOne(WritingContentModel::class,'writing_id', 'id');
    }

    public static function createWriting($directory_id,$writing_name,$writing_type,$start_time,$end_time,$filteredcontent)
    {
        // 通过token获取用户id
        $payload = JWTAuth::auth(); //可验证token, 并获取token中的payload部分
        $user_id = $payload['uid'];

        $writing = new self();
        $writing->save(['create_by' => $user_id,'directory_id' => $directory_id,'writing_name' => $writing_name,'writing_type' => $writing_type,'start_time'=>$start_time,'end_time'=>$end_time]);

        WritingContentModel::create(['writing_id'=>$writing->id,'content'=>$filteredcontent]);

        return $writing;

    }
    
    // 显示单个目录内的所有写作名和更新时间
    public static function getWritings($directory_id){
        $writing = new self();
        $writingInfo = $writing->where('directory_id',$directory_id)->select();
        return $writingInfo;
    }

    // 获取单个写作的所有信息
    public static function showWriting($writing_id){
        $writing = new self();
        $writingInfo = $writing->with('content')->find($writing_id);
        
        return $writingInfo;
    }
    
    //删除指定目录中的写作及其内容
    public static function deleteWriting($writing_id)
    {
        // 查找要删除的写作
        $writing = self::find($writing_id);
        // 找到时
        if ($writing) {
            // 删除写作中的内容
            WritingContentModel::where('writing_id',$writing_id)->find()->delete();
            
            // 删除写作
            return $writing->delete();
        }
        // 没找到时
        return false;
    }
}