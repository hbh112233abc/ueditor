<?php

namespace bingher\ueditor;

use think\Route;
use think\Service;
use bingher\ueditor\util\Recource;

class UeditorService extends Service
{
    public function boot(Route $route)
    {
        Recource::install(false);
        $route->rule('ueditor/index', "\\bingher\\ueditor\\controller\\Ueditor@index");
    }
}