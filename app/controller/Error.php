<?php
namespace app\controller;

class Error 
{
    public function __call($method, $args)
    {
        return '404 找不到该控制器';
    }
}