<?php
namespace app\api\controller;
// 最新动态
use think\facade\Db;



class DynamicController
{
    public function newdynamic()
    {
        $dynamic = Db::table('think_resource')
                        ->order('update_time', 'desc')
                        ->select();
        
        return json($dynamic);
    }
}