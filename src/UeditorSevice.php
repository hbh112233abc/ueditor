<?php

namespace bingher\ueditor;

use think\Route;
use think\Service;

class UeditorService extends Service
{
    public function boot(Route $route)
    {
        $route->rule('ueditor/index', "\\bingher\\ueditor\\controller\\Ueditor@index");
    }
}