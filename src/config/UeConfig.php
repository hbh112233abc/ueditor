<?php

namespace bingher\ueditor\config;

use think\facade\Config;
use think\facade\Db;
use think\facade\Filesystem;

/**
 * 配置信息
 */
class UeConfig
{
    protected $config;
    protected $ueditor;

    /**
     * 构造函数
     *
     * @param array $config 配置信息,默认:[]
     *
     * @return void
     */
    public function __construct($config = [])
    {
        try {
            $userConfig = Db::name('ueditor_config')->cache('ueditor_config', 600)
                ->column('value', 'name');
        } catch (\Throwable $th) {
            $userConfig = Config::get('ueditor', []);
        }

        if (!empty($config)) {
            $userConfig = array_merge($userConfig, $config);
        }
        $this->config = $userConfig;

        $this->ueditor = include_once __DIR__ . '/ueditor.php';
        //默认最大图片限制
        $defaultMaxImageSize             = $this->config['max_image_size'];
        $this->ueditor['imageMaxSize']   = $defaultMaxImageSize;
        $this->ueditor['scrawlMaxSize']  = $defaultMaxImageSize;
        $this->ueditor['catcherMaxSize'] = $defaultMaxImageSize;
        //文件上传字段名
        $defaultUploadFieldName            = $this->config['upload_field_name'];
        $this->ueditor['imageFieldName']   = $defaultUploadFieldName;
        $this->ueditor['scrawlFieldName']  = $defaultUploadFieldName;
        $this->ueditor['catcherFieldName'] = $defaultUploadFieldName;
        $this->ueditor['videoFieldName']   = $defaultUploadFieldName;
        $this->ueditor['fileFieldName']    = $defaultUploadFieldName;

        $this->config = array_merge($this->ueditor, $this->config);
    }

    /**
     * 输出json配置信息(前端需要)
     *
     * @return string json数据
     */
    public function json()
    {
        return json($this->ueditor);
    }

    /**
     * 初始化文件上传配置
     *
     * @param string $diskName disk名,默认:ueditor
     *
     * @return \think\filesystem\Driver
     */
    public function initFilesystem(string $diskName = 'ueditor')
    {
        $fsConfig = Config::get('filesystem');
        if (empty($fsConfig['disks'][$diskName])) {
            $ueditorFsConfig = [
                // 磁盘类型
                'type'       => $this->get('filesystem_type', 'local'),
                // 磁盘路径
                'root'       => $this->get('filesystem_root', app()->getRootPath() . 'public/upload'),
                // 磁盘路径对应的外部URL路径
                'url'        => $this->get('filesystem_url', '/upload'),
                // 可见性
                'visibility' => 'public',
            ];
            $fsConfig['disks'][$diskName] = $ueditorFsConfig;
            Config::set($fsConfig, 'filesystem');
        }
        return Filesystem::disk($diskName);
    }

    /**
     * 获取配置
     *
     * @param string $name    配置项名
     * @param string $default 默认值
     *
     * @return string|int|array
     */
    public function get(string $name, $default = '')
    {
        // 无参数时获取所有
        if (empty($name)) {
            return $this->config;
        }

        if (false === strpos($name, '.')) {
            if (isset($this->config[$name])) {
                return $this->config[$name];
            } else {
                return $default;
            }
        }

        $name   = explode('.', $name);
        $config = $this->config;

        // 按.拆分成多维数组进行判断
        foreach ($name as $val) {
            if (isset($config[$val])) {
                $config = $config[$val];
            } else {
                return $default;
            }
        }

        return $config;
    }
}
