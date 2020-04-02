<?php
namespace bingher\ueditor\config;
use think\facade\Config;

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

    /**
     * 获取配置
     *
     * @param string $key 
     * @param string $default
     * @return string|int|array
     */
    public function get(string $key,$default='')
    {
        if(isset($this->config[$key])){
            return $this->config[$key];
        }
        return $default;
    }
}