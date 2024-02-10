<?php
namespace app\api\model;

use app\common\Result\Result;
use thans\jwt\facade\JWTAuth;
use think\Model;
use think\facade\Filesystem;
use think\model\concern\SoftDelete;
use app\api\model\ResourceContent as ResourceContentModel;

class Resource extends Model
{
    use SoftDelete;
    protected $table = 'think_resource';
    protected $pk = 'id';
    // 自动维护时间戳
    protected $autoWriteTimestamp = true;

    protected $schema = [
        'id' => 'int',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
        'delete_time' => 'datetime',
        'directory_id' => 'int',
        'seminar_id' => 'int',
        'create_by' => 'int',
        'resource_name' => 'string',
        'resource_type' => 'int',
        'file_size' => 'string',
        'file_type' => 'string',
        'file_url' => 'string',
    ];

    //组内资源上传
    public static function upload($data, $result)
    {
        try {
            $resource = new self();
            $resource->resource_name = $data['resource_name'];
            $resource->resource_type = $data['type'];
            $resource->seminar_id = $data['seminar_id'];
            $resource->file_url = $result['fileName'];
            $resource->file_size = $result['fileSize'];
            $resource->file_type = $result['fileExtension'];

            // 通过token获取用户id
            $payload = JWTAuth::auth();
            $uid = $payload['uid'];
            $resource->create_by = $uid;

            $resource->save();
        } catch (\Exception $e) {
            // 输出异常信息或者记录到日志中
            return Result::Error(null, $e->getMessage());
        }
        return true;
    }

    // 显示单个目录内的所有教案、导学案
    public static function getPersonResources($directory_id,$resource_type){
        $resource = new self();
        $resourceInfo = $resource->where(['directory_id'=>$directory_id,'resource_type'=>$resource_type])->select();
        return $resourceInfo;
    }

    // 定义与内容模型的一对一的关联关系
    public function content()
    {
        return $this->hasOne(ResourceContentModel::class,'resource_id', 'id');
    }

    // 获取单个教案、导学案的所有信息
    public static function showPersonResource($resource_id) {
        $resource = new self();
        $resourceInfo = $resource->with('content')->find($resource_id);
        
        return $resourceInfo;
    }
    // // 展示单个教案、导学案的内容
    // public static function showContent($resource_id) {
    //     $resource = new self();
    //     $resource = $resource->with('content')->find($resource_id);
    //     $contentInfo = $resource->content->content;
    //     return $contentInfo;
    // }

    // 创建目录中的教案、导学案
    public static function createPersonResource($directory_id,$resource_name,$resource_type,$filteredcontent)
    {
        // 通过token获取用户id
        $payload = JWTAuth::auth(); //可验证token, 并获取token中的payload部分
        $user_id = $payload['uid'];

        $resource = new self();       
        $resource->save(['create_by' => $user_id,'directory_id' => $directory_id,'resource_name' => $resource_name,'resource_type'=>$resource_type]);

        ResourceContent::create(['resource_id'=>$resource->id,'content'=>$filteredcontent]);
        
        return $resource;

    }

    //删除指定目录中的教案、导学案及其内容
    public static function deletePersonResource($resource_id)
    {
        // 查找要删除的资源
        $resource = self::find($resource_id);
        // 找到时
        if ($resource) {
            // 删除资源中的内容
            ResourceContentModel::where('resource_id',$resource_id)->find()->delete();
            
            // 删除资源
            return $resource->delete();
        }
        // 没找到时
        return false;
    }


    // 创建资源库中的资源
    public static function createResource($resource_name,$resource_type,$filteredcontent)
    {
        // 通过token获取用户id
        $payload = JWTAuth::auth(); //可验证token, 并获取token中的payload部分
        $user_id = $payload['uid'];

        $resource = new self();       
        $resource->save(['create_by' => $user_id,'resource_name' => $resource_name,'resource_type'=>$resource_type]);

        ResourceContent::create(['resource_id'=>$resource->id,'content'=>$filteredcontent]);
        
        return $resource;

    }
    // 显示资源库中的所有资源
    public static function getResources(){
        $resource = new self();
        $resourceInfo = $resource->where(['directory_id'=>NULL,'seminar_id'=>NULL])->select();
        return $resourceInfo;
    }

    // 显示资源库中各类别的资源名
    public static function showTypeResources($resource_type){
        $resource = new self();
        $resourceInfo = $resource->where(['directory_id'=>NULL,'seminar_id'=>NULL,'resource_type'=>$resource_type])->select();
        return $resourceInfo;
    }
    
    // 获取资源库资源的创建者
    public static function getResourceCreator($resource_id)
    {
        $resource = new self();
        //获取资源库资源的创建者id
        $resource = $resource->where('id',$resource_id)->find();
        if ($resource) {
            return $resource->create_by;
        } else {
            return null;
        }
    }

    // 获取单个教案、导学案的所有信息
    public static function showResource($resource_id){
        $resource = new self();
        $resourceInfo = $resource->with('content')->find($resource_id);
        
        return $resourceInfo;
    }

    //删除指定目录中的教案、导学案及其内容
    public static function deleteResource($resource_id)
    {
        // 查找要删除的资源
        $resource = self::find($resource_id);
        // 找到时
        if ($resource) {
            // 删除资源中的内容
            ResourceContentModel::where('resource_id',$resource_id)->find()->delete();
            
            // 删除资源
            return $resource->delete();
        }
        // 没找到时
        return false;
    }

    // 搜索资源库资源
    public static function searchResource($keyword)  
    {  
        $resource = new self();  

        $results = $resource->where('resource_name', 'like',"%{$keyword}%")
                            ->order('update_time', 'desc') 
                            ->select();
    
        return $results;  
    }
}