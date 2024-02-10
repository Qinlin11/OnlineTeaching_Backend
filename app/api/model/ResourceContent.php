<?php
namespace app\api\model;

use think\Model;
use think\model\concern\SoftDelete;

class ResourceContent extends Model
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;
    protected $deleteTime = 'delete_time';

    protected $schema = [
        'id' => 'int',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
        'delete_time' => 'datetime',
        'resource_id' => 'int',
        'content' => 'text',
    ];
    // 定义与资源模型的一对一的关联关系
    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }
}

// 个人资源内容表，与资源表一对一关联