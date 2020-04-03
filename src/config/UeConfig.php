<?php
namespace bingher\ueditor\config;
use think\facade\Config;
use think\facade\Filesystem;
/**
 * 配置信息
 */
class UeConfig
{
    protected $config;
    protected $ueditor;
    function __construct($config = [])
    {
        $userConfig = Config::get('ueditor',[]);
        if(!empty($config)){
            $userConfig = array_merge($userConfig,$config);
        }
        $this->config = $userConfig;
        $this->config = array_merge($this->config,$userConfig);

        $this->ueditor = require __DIR__.'/ueditor.php';
        $this->ueditor['imageMaxSize'] = $this->ueditor['scrawlMaxSize'] = $this->ueditor['catcherMaxSize'] = $this->config['max_image_size'];
        $this->ueditor['imageFieldName'] = $this->ueditor['scrawlFieldName'] = $this->ueditor['catcherFieldName'] = $this->ueditor['videoFieldName'] = $this->ueditor['fileFieldName'] = $this->config['upload_field_name'];

        $this->config = array_merge($this->ueditor,$this->config);
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

    public function initFilesystem(string $diskName='ueditor')
    {
        $fsConfig = Config::get('filesystem');
        if(empty($fsConfig['disks'][$diskName])){
            $ueditorFsConfig = $this->get('filesystem',[
                // 磁盘类型
                'type'       => 'local',
                // 磁盘路径
                'root'       => app()->getRootPath() . 'public/upload',
                // 磁盘路径对应的外部URL路径
                'url'        => '/upload',
                // 可见性
                'visibility' => 'public',
            ]);
            if (empty($ueditorFsConfig)) {
                throw new \Exception('配置错误,config/ueditor.php未找到filesystem配置项');
            }
            $fsConfig['disks'][$diskName] = $ueditorFsConfig;
            Config::set($fsConfig,'filesystem');
        }
        return Filesystem::disk($diskName);
    }

    /**
     * 获取配置
     *
     * @param string $name
     * @param string $default
     * @return string|int|array
     */
    public function get(string $name,$default='')
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

        $name    = explode('.', $name);
        $config  = $this->config;

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
