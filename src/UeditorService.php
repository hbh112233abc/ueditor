<?php

namespace bingher\ueditor;

use bingher\ueditor\command\Publish;
use bingher\ueditor\util\Resources;
use think\Route;
use think\Service;

class UeditorService extends Service
{
    public function boot(Route $route)
    {
        $middleware = [
            \think\middleware\SessionInit::class,
            \think\middleware\AllowCrossDomain::class,
        ];
        $route->rule('ueditor/index', "\\bingher\\ueditor\\controller\\Ueditor@index")
            ->middleware($middleware);
        $route->rule('ueditor/setting/index', "\\bingher\\ueditor\\controller\\Setting@index")
            ->middleware($middleware);
        $route->rule('ueditor/setting/save', "\\bingher\\ueditor\\controller\\Setting@save")
            ->middleware($middleware);
        $route->rule('ueditor/setting/upload_imgage', "\\bingher\\ueditor\\controller\\Setting@uploadImage")
            ->middleware($middleware);

        $route->rule('ueditor/demo/view', "\\bingher\\ueditor\\controller\\Demo@view");
        $route->rule('ueditor/demo/setting', "\\bingher\\ueditor\\controller\\Demo@setting");

        Resources::install(false); //检查资源文件

        $this->commands(['ueditor:publish' => Publish::class]);
    }
}
