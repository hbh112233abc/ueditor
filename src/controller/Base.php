<?php
namespace bingher\ueditor\controller;

use bingher\ueditor\config\UeConfig;
use think\exception\HttpResponseException;

/**
 * 基础控制器
 */
class Base
{
    protected $config = [];

    /**
     * 构造函数
     *
     * @return self
     */
    public function __construct()
    {
        $this->config = new UeConfig();
        if ($this->config->get('auth_control')) {
            if (!call_user_func($this->config->get('auth_control'))) {
                throw new HttpResponseException($this->error('auth error'));
            }
        }
    }

    /**
     * 路径拼接
     * 1.传入的多个参数用DIRECTORY_SEPARATOR连接
     * 2.将\\替换为/
     *
     * @param array ...$args 路径名
     *
     * @return string
     */
    public static function pathJoin(...$args): string
    {
        $path = implode(DIRECTORY_SEPARATOR, $args);
        $path = str_replace('//', '/', $path);
        $path = str_replace('\\', '/', $path);
        return $path;
    }

    /**
     * 返回错误信息
     *
     * @param string  $msg  错误信息,默认:ERROR
     * @param integer $code 错误码,默认:0
     *
     * @return \think\Response
     */
    protected function error($msg = 'ERROR', $code = 0)
    {
        return json(['state' => $msg, 'code' => $code]);
    }

    /**
     * 返回响应信息
     *
     * @param array   $data 返回数据,默认:[]
     * @param string  $msg  返回消息,默认:SUCCESS
     * @param integer $code 返回消息码,默认:1
     *
     * @return \think\Response
     */
    protected function success(array $data = [], $msg = 'SUCCESS', $code = 1)
    {
        if (empty($data['state'])) {
            $data['state'] = $msg;
        }
        if (empty($data['code'])) {
            $data['code'] = $code;
        }
        return json($data);
    }
}
