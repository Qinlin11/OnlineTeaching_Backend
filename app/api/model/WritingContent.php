<?php
namespace app\api\model;

use think\Model;
use think\model\concern\SoftDelete;

class WritingContent extends Model
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;
    protected $table = 'think_writing_content';
    protected $pk = 'id';

    protected $schema = [
        'id' => 'int',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
        'delete_time' => 'datetime',
        'writing_id' => 'int',
        'content' => 'text',
    ];
    // 定义与资源模型的一对一的关联关系
    public function writing()
    {
        return $this->belongsTo(Writing::class);
    }
}

// 写作内容表，与资源表一对一关联