<?php

namespace bingher\ueditor;

use think\Route;
use think\Service;
use bingher\ueditor\util\Recource;
use bingher\ueditor\command\Publish;

class UeditorService extends Service
{
    public function boot(Route $route)
    {
        $route->rule('ueditor/index', "\\bingher\\ueditor\\controller\\Ueditor@index");

        $route->rule('ueditor/setting/index', "\\bingher\\ueditor\\controller\\Setting@index");
        $route->rule('ueditor/setting/save', "\\bingher\\ueditor\\controller\\Setting@save");
        $route->rule('ueditor/setting/upload_imgage', "\\bingher\\ueditor\\controller\\Setting@uploadImage");

        $route->rule('ueditor/demo/view', "\\bingher\\ueditor\\controller\\Demo@view");
        $route->rule('ueditor/demo/setting', "\\bingher\\ueditor\\controller\\Demo@setting");

        Recource::install(false); //检查资源文件

        $this->commands(['ueditor:publish' => Publish::class]);
    }
}
