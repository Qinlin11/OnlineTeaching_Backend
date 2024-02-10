<?php

namespace app\api\model;

use think\Model;
use think\model\concern\SoftDelete;

class RichText extends Model
{


    protected $autoWriteTimestamp = 'datetime';
    use SoftDelete;
    protected $deleteTime = 'delete_time';
//    protected $dateFormat = 'Y-m-d H:i:s';

    protected $schema = [
        'id' => 'int',
        'title' => 'string',
        'content' => 'text',
        'create_at' => 'datetime',
        'update_at' => 'datetime',
        'delete_time' => 'datetime',
        'seminar_id' => 'int',
        'creator_id' => 'int'
    ];
}