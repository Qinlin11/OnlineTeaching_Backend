<?php

namespace app\common\Result;

use think\Response;
class Result
{

    /**
     * 处理成功返回API数据
     * @param  mixed   $data 要返回的数据
     * @param  integer $code 返回的code
     * @param  mixed   $message 提示信息
     * @param  string  $type 返回数据格式
     * @param  array   $header 发送的Header信息
     * @return Response
     */
    public static function Success($data , string $message = '请求成功', int $code = 200, string $type = 'json',$header = []) :Response    {
        $result = [
            'code' => $code,
            'message' => $message,
            'time' => date('Y-m-d H:i:s',time()),
            'data' => $data
        ];
        return  json($result,$code);
    }


    /**
     * 处理失败返回API数据
     * @param  mixed   $data 要返回的数据
     * @param  integer $code 返回的code
     * @param  mixed   $message 提示信息
     * @param  string  $type 返回数据格式
     * @param  array   $header 发送的Header信息
     * @return Response
     */
    public static function Error($data , string $message = '请求失败', int $code = 500, string $type = 'json',$header = []) :Response    {
        $result = [
            'code' => $code,
            'message' => $message,
            'time' => date('Y-m-d H:i:s',time()),
            'data' => $data
        ];
        return  json($result,$code);
    }


    /**
     * @param int $code
     * @param string $message
     * @param array $data
     * @param array $header
     */
    public static function returnMsg($data = [], $message = '',$code = 500,$header = [])    {
        $res = [
            'code' => $code,
            'message' => $message,
            'time' => date('Y-m-d H:i:s',time()),
            'data' => $data
        ];
        return json($res,$code);

    }
}