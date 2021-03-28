<?php
namespace bingher\ueditor\util;

/**
 * 扩展安装更新及卸载的资源文件操作
 * 1. 安装更新时,复制assets/ueditor文件到/public/static/bingher/ueditor/
 * 2. 卸载时,删除/public/static/bingher/ueditor/,如果/public/static/bingher为空也一并删除了
 */
class Resources
{
    /**
     * 安装动作
     *
     * @param bool $overWrite 是否覆盖,默认:true
     *
     * @return boolean
     */
    public static function install(bool $overWrite = true)
    {
        $assets = __DIR__ . '/../../assets/ueditor/';
        $static = root_path() . '/public/static/bingher/ueditor/';
        $res    = FileUtil::copyDir($assets, $static, $overWrite);

        if (!$res) {
            throw new \Exception('资源安装失败');
        }
        return $res;
    }

    /**
     * 卸载操作
     *
     * @return boolean
     */
    public static function uninstall()
    {
        $parentDir  = root_path() . '/public/static/bingher/';
        $ueditorDir = $parentDir . 'ueditor/';
        $res        = FileUtil::unlinkDir($ueditorDir);
        if ($res) {
            if (FileUtil::isDirEmpty($parentDir)) {
                $res = FileUtil::unlinkDir($parentDir);
            }
        }
        if (!$res) {
            throw new \Exception('资源删除失败,请手动删除' . $ueditorDir);
        }
        return $res;
    }
}
