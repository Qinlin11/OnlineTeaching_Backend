<?php
namespace app\api\model;

use think\Model;
use think\model\concern\SoftDelete;

class AssignmentContent extends Model
{
    use SoftDelete;
    protected $autoWriteTimestamp = true;
    protected $table = 'think_assignment_content';
    protected $pk = 'id';

    protected $schema = [
        'id' => 'int',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
        'delete_time' => 'datetime',
        'assignment_id' => 'int',
        'content' => 'text',
    ];
    // 定义与作业模型的一对一的关联关系
    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }
}

// 资源内容表，与资源表一对一关联