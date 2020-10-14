<?php

namespace bingher\ueditor;

use bingher\ueditor\command\Publish;
use bingher\ueditor\util\Recource;
use think\Route;
use think\Service;

class UeditorService extends Service
{
    public function boot(Route $route)
    {
        $route->rule('ueditor/index', "\\bingher\\ueditor\\controller\\Ueditor@index")
            ->middleware(\think\middleware\SessionInit::class);

        $route->rule('ueditor/setting/index', "\\bingher\\ueditor\\controller\\Setting@index")
            ->middleware(\think\middleware\SessionInit::class);
        $route->rule('ueditor/setting/save', "\\bingher\\ueditor\\controller\\Setting@save")
            ->middleware(\think\middleware\SessionInit::class);
        $route->rule('ueditor/setting/upload_imgage', "\\bingher\\ueditor\\controller\\Setting@uploadImage")
            ->middleware(\think\middleware\SessionInit::class);

        $route->rule('ueditor/demo/view', "\\bingher\\ueditor\\controller\\Demo@view");
        $route->rule('ueditor/demo/setting', "\\bingher\\ueditor\\controller\\Demo@setting");

        Recource::install(false); //检查资源文件

        $this->commands(['ueditor:publish' => Publish::class]);
    }
}
