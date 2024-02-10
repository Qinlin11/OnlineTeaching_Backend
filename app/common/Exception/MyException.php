<?php

namespace app\common\Exception;

use app\common\Result\Result;
use ErrorException;
use Exception;
use InvalidArgumentException;
use ParseError;
use thans\jwt\exception\BadMethodCallException;
use thans\jwt\exception\JWTException;
use thans\jwt\exception\TokenBlacklistException;
use thans\jwt\exception\TokenBlacklistGracePeriodException;
use thans\jwt\exception\TokenInvalidException;
use think\exception\ClassNotFoundException;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\RouteNotFoundException;
use think\exception\ValidateException;
use think\Response;
use Throwable;
use TypeError;

//use PDOException;
class MyException extends Handle
{
    public function render($request, Throwable $e): Response
    {
        //如果处于调试模式
        if (env('app_debug')){
            //return Result::Error(1,$e->getMessage().$e->getTraceAsString());
            return parent::render($request, $e);
        }
        // 参数验证错误
        if ($e instanceof ValidateException) {
            return Result::Error(422,$e->getError());
        }

        // 请求404异常 , 不返回错误页面
        if (($e instanceof ClassNotFoundException || $e instanceof RouteNotFoundException) || ($e instanceof HttpException && $e->getStatusCode() == 404)) {
            return Result::Error(null,'当前请求资源不存在，请稍后再试', 404);
        }
        //捕获token的错误
        if($e instanceof TokenBlacklistException){
            return Result::Error(null,$e->getMessage(), 401);
        }else if($e instanceof JWTException){
            return Result::Error(null,$e->getMessage(),  401);
        }else if($e instanceof TokenBlacklistGracePeriodException){
            return Result::Error(null,$e->getMessage(), 401);
        }else if($e instanceof BadMethodCallException){
            return Result::Error(null,$e->getMessage(), 404);
        }else if($e instanceof TokenInvalidException){
            return Result::Error(null,$e->getMessage(), 401);
        }

        //请求500异常, 不返回错误页面
        //$e instanceof PDOException ||
        if ($e instanceof Exception ||  $e instanceof HttpException || $e instanceof InvalidArgumentException || $e instanceof ErrorException || $e instanceof ParseError || $e instanceof TypeError)  {

            $this->reportException($request, $e);
            return Result::Error(null,'系统异常，请稍后再试');
        }

        //其他错误
        $this->reportException($request, $e);
        return Result::Error(null,"应用发生错误", 1);
    }

    //记录exception到日志
    private function reportException($request, Throwable $e):void {
        $errorStr = "url:".$request->host().$request->url()."\n";
        $errorStr .= "code:".$e->getCode()."\n";
        $errorStr .= "file:".$e->getFile()."\n";
        $errorStr .= "line:".$e->getLine()."\n";
        $errorStr .= "message:".$e->getMessage()."\n";
        $errorStr .=  $e->getTraceAsString();

        trace($errorStr, 'error');
    }

}