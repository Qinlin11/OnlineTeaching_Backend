<?php
namespace app\api\model;

use think\Model;
use think\model\concern\SoftDelete;
use thans\jwt\facade\JWTAuth;
use app\api\model\Resource as ResourceModel;

class Directory extends Model
{
    use SoftDelete;
    protected $table = 'think_directory';
    protected $pk = 'id';
    // 自动维护时间戳
    protected $autoWriteTimestamp = true;

    protected $schema = [
        'id' => 'int',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
        'delete_time' => 'datetime',
        'directory_name' => 'string',
        'host_id' => 'int',
        'activity_id' => 'int',
    ];

    //显示登录用户的所有目录
    public static function getDirectory()
    {

        // 通过token获取用户id
        $payload = JWTAuth::auth(); //可验证token, 并获取token中的payload部分
        $user_id = $payload['uid'];
        $directory = self::where('host_id',$user_id)->select();

        return $directory;
    }

    // 创建目录
    public static function createDirectory($directory_name,$user_id)
    {
        $directory = new self();
        
        $directory->save(['directory_name' => $directory_name, 'host_id' => $user_id]);
        return $directory;

    }

    // 定义与资源模型的关联关系(不能使用静态方法)
    public function resources()
    {
        return $this->hasMany(ResourceModel::class, 'directory_id', 'id');
    }

    // 删除指定目录及关联资源
    public static function deleteDirectory($directoryId)
    {
        $directory = self::find($directoryId);

        if ($directory) {
            // 删除目录关联的资源
            $directory->resources()->delete();
            
            // 删除目录
            return $directory->delete();
        }

        return false;
    }

    // 修改更新目录记录
    public static function updateDirectory($directory_id, $directory_name)
    {
        // 查找目录
        $directory = self::find($directory_id);
        if (!$directory) {
            return false;
        }
        
        // 修改目录名
        $directory->directory_name = $directory_name;
        return $directory->save();
    }
}