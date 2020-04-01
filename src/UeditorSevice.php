<?php

namespace bingher\ueditor;

use think\Route;
use think\Service;

class UeditorService extends Service
{
    public function boot(Route $route)
    {
        $route->get('ueditor/index', "\\bingher\\ueditor\\controller\\Ueditor@index");
    }
}