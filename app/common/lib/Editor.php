<?php
namespace app\common\lib;

use app\BaseController;
use HTMLPurifier; // 导入 HTMLPurifier 类
use HTMLPurifier_Config;

class Editor
{
    // 过滤富文本编辑器内容
    public function saveContent($content)
    {
        // 进行输入验证和过滤
        $config = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);
        $filteredContent = $purifier->purify($content);

        // 返回过滤结果
        return $filteredContent;
    }
}