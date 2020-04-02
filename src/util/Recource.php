<?php
namespace bingher\ueditor\util;
<<<<<<< HEAD

/**
 * 扩展安装更新及卸载的资源文件操作
 * 1. 安装更新时,复制assets文件到/public/static/bingher/ueditor/
 * 2. 卸载时,删除/public/static/bingher/ueditor/,如果/public/static/bingher为空也一并删除了
=======
use bingher\ueditor\util\FileUtil;
/**
 * 资源控制
 * 安装和更新扩展的时候,复制assets覆盖public/static/bingher/ueditor/
>>>>>>> 3f92b042d030c8e68abbdd997b09ee7e97a63ca6
 */
class Recource
{
    /**
<<<<<<< HEAD
     * 安装动作
     *
     * @return boolean
=======
     * 安装及更新扩展 复制资源到public/static
     *
     * @return void
>>>>>>> 3f92b042d030c8e68abbdd997b09ee7e97a63ca6
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
     * 卸载操作
     *
     * @return boolean
     */
    static public function uninstall()
    {
        $parentDir = root_path().'/public/static/bingher/';
        $ueditorDir = $parentDir.'/ueditor/';
        $res = FileUtil::unlinkDir($ueditorDir);
        if($res){
            if(FileUtil::isDirEmpty($parentDir)){
                $res = FileUtil::unlinkDir($parentDir);
            }
        }
        if(!$res){
            throw new \Exception('资源删除失败,请手动删除'.$ueditorDir);
        }        
        return $res;
    }
}