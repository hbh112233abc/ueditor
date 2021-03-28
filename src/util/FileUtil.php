<?php
namespace bingher\ueditor\util;

/**
 * 操纵文件类
 *
 * 例子：
 * FileUtil::createDir('a/1/2/3');                测试建立文件夹 建一个a/1/2/3文件夹
 * FileUtil::createFile('b/1/2/3');               测试建立文件        在b/1/2/文件夹下面建一个3文件
 * FileUtil::createFile('b/1/2/3.exe');           测试建立文件        在b/1/2/文件夹下面建一个3.exe文件
 * FileUtil::copyDir('b','d/e');                  测试复制文件夹 建立一个d/e文件夹，把b文件夹下的内容复制进去
 * FileUtil::copyFile('b/1/2/3.exe','b/b/3.exe'); 测试复制文件        建立一个b/b文件夹，并把b/1/2文件夹中的3.exe文件复制进去
 * FileUtil::moveDir('a/','b/c');                 测试移动文件夹 建立一个b/c文件夹,并把a文件夹下的内容移动进去，并删除a文件夹
 * FileUtil::moveFile('b/1/2/3.exe','b/d/3.exe'); 测试移动文件        建立一个b/d文件夹，并把b/1/2中的3.exe移动进去
 * FileUtil::unlinkFile('b/d/3.exe');             测试删除文件        删除b/d/3.exe文件
 * FileUtil::unlinkDir('d');                      测试删除文件夹 删除d文件夹
 */
class FileUtil
{
    /**
     * 建立文件夹
     *
     * @param string $aimUrl 路径
     *
     * @return bool
     */
    public static function createDir($aimUrl)
    {
        $aimUrl = str_replace('', '/', $aimUrl);
        $aimDir = '';
        $arr    = explode('/', $aimUrl);
        $result = true;
        foreach ($arr as $str) {
            $aimDir .= $str . '/';
            if (!file_exists($aimDir)) {
                $result = mkdir($aimDir);
            }
        }
        return $result;
    }
    /**
     * 建立文件
     *
     * @param string  $aimUrl    文件路径
     * @param boolean $overWrite 该参数控制是否覆盖原文件
     *
     * @return boolean
     */
    public static function createFile($aimUrl, $overWrite = false)
    {
        if (file_exists($aimUrl) && $overWrite == false) {
            return false;
        } elseif (file_exists($aimUrl) && $overWrite == true) {
            FileUtil::unlinkFile($aimUrl);
        }
        $aimDir = dirname($aimUrl);
        FileUtil::createDir($aimDir);
        touch($aimUrl);
        return true;
    }

    /**
     * 移动文件夹
     *
     * @param string  $oldDir    旧文件路径
     * @param string  $aimDir    新文件路径
     * @param boolean $overWrite 该参数控制是否覆盖原文件
     *
     * @return boolean
     */
    public static function moveDir($oldDir, $aimDir, $overWrite = false)
    {
        $aimDir = str_replace('', '/', $aimDir);
        $aimDir = substr($aimDir, -1) == '/' ? $aimDir : $aimDir . '/';
        $oldDir = str_replace('', '/', $oldDir);
        $oldDir = substr($oldDir, -1) == '/' ? $oldDir : $oldDir . '/';
        if (!is_dir($oldDir)) {
            return false;
        }
        if (!file_exists($aimDir)) {
            FileUtil::createDir($aimDir);
        }
        @$dirHandle = opendir($oldDir);
        if (!$dirHandle) {
            return false;
        }
        while (false !== ($file = readdir($dirHandle))) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            if (!is_dir($oldDir . $file)) {
                FileUtil::moveFile($oldDir . $file, $aimDir . $file, $overWrite);
            } else {
                FileUtil::moveDir($oldDir . $file, $aimDir . $file, $overWrite);
            }
        }
        closedir($dirHandle);
        return rmdir($oldDir);
    }

    /**
     * 移动文件
     *
     * @param string  $fileUrl   文件路径
     * @param string  $aimUrl    新文件路径
     * @param boolean $overWrite 该参数控制是否覆盖原文件,默认false
     *
     * @return boolean
     */
    public static function moveFile($fileUrl, $aimUrl, bool $overWrite = false)
    {
        if (!file_exists($fileUrl)) {
            return false;
        }
        if (file_exists($aimUrl) && !$overWrite) {
            return false;
        }
        if (file_exists($aimUrl) && $overWrite) {
            FileUtil::unlinkFile($aimUrl);
        }
        $aimDir = dirname($aimUrl);
        FileUtil::createDir($aimDir);
        rename($fileUrl, $aimUrl);
        return true;
    }

    /**
     * 删除文件夹
     *
     * @param string $aimDir 文件路径
     *
     * @return boolean
     */
    public static function unlinkDir($aimDir)
    {
        $aimDir = str_replace('', '/', $aimDir);
        $aimDir = substr($aimDir, -1) == '/' ? $aimDir : $aimDir . '/';
        if (!is_dir($aimDir)) {
            return false;
        }
        $dirHandle = opendir($aimDir);
        while (false !== ($file = readdir($dirHandle))) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            if (!is_dir($aimDir . $file)) {
                FileUtil::unlinkFile($aimDir . $file);
            } else {
                FileUtil::unlinkDir($aimDir . $file);
            }
        }
        closedir($dirHandle);
        return rmdir($aimDir);
    }

    /**
     * 删除文件
     *
     * @param string $aimUrl 文件路径
     *
     * @return boolean
     */
    public static function unlinkFile($aimUrl)
    {
        if (file_exists($aimUrl)) {
            unlink($aimUrl);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 复制文件夹
     *
     * @param string  $oldDir    旧文件路径
     * @param string  $aimDir    新文件路径
     * @param boolean $overWrite 该参数控制是否覆盖原文件
     *
     * @return boolean
     */
    public static function copyDir($oldDir, $aimDir, $overWrite = false)
    {
        $aimDir = str_replace('', '/', $aimDir);
        $aimDir = substr($aimDir, -1) == '/' ? $aimDir : $aimDir . '/';
        $oldDir = str_replace('', '/', $oldDir);
        $oldDir = substr($oldDir, -1) == '/' ? $oldDir : $oldDir . '/';
        if (!is_dir($oldDir)) {
            return false;
        }
        if (!file_exists($aimDir)) {
            FileUtil::createDir($aimDir);
        }
        $dirHandle = opendir($oldDir);
        while (false !== ($file = readdir($dirHandle))) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            if (!is_dir($oldDir . $file)) {
                FileUtil::copyFile($oldDir . $file, $aimDir . $file, $overWrite);
            } else {
                FileUtil::copyDir($oldDir . $file, $aimDir . $file, $overWrite);
            }
        }
        closedir($dirHandle);
        return true;
    }

    /**
     * 复制文件
     *
     * @param string  $fileUrl   文件路径
     * @param string  $aimUrl    新文件路径
     * @param boolean $overWrite 该参数控制是否覆盖原文件
     *
     * @return boolean
     */
    public static function copyFile($fileUrl, $aimUrl, $overWrite = false)
    {
        if (!file_exists($fileUrl)) {
            return false;
        }
        if (file_exists($aimUrl) && $overWrite == false) {
            return false;
        } elseif (file_exists($aimUrl) && $overWrite == true) {
            FileUtil::unlinkFile($aimUrl);
        }
        $aimDir = dirname($aimUrl);
        FileUtil::createDir($aimDir);
        copy($fileUrl, $aimUrl);
        return true;
    }

    /**
     * 判断目录是否为空
     *
     * @param string $dir 目录路径
     *
     * @return boolean
     */
    public static function isDirEmpty(string $dir)
    {
        if (!is_dir($dir)) {
            return true;
        }

        $res = array_diff(scandir($dir), ['..', '.']);
        return empty($res);
    }
}
