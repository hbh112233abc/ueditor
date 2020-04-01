<?php
namespace bingher\ueditor\util;
use bingher\ueditor\util\FileUtil;
/**
 * 资源控制
 * 安装和更新扩展的时候,复制assets覆盖public/static/bingher/ueditor/
 */
class Recource
{
    /**
     * 安装及更新扩展 复制资源到public/static
     *
     * @return void
     */
    static public function install()
    {
        $assets = __DIR__.'/../../assets/';
        $static = root_path().'/public/static/bingher/ueditor/';
        $res = FileUtil::copyDir($assets,$static,true);

        if(!$res){
            throw new \Exception('资源安装失败');
        }
        return $res;
    }

    /**
     * 卸载扩展 删除资源文件
     *
     * @return void
     */
    static public function uninstall()
    {
        $static = root_path().'/public/static/bingher/';
        $res = FileUtil::unlinkDir($static);
        if(!$res){
            throw new \Exception('资源卸载失败');
        }
        return $res;
    }
}