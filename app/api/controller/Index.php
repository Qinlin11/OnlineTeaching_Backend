<?php
declare (strict_types = 1);

namespace app\api\controller;

use app\api\model\Seminar;
use app\api\model\User;
class Index
{
    public function index()
    {
        return Captcha::create();
    }

}
