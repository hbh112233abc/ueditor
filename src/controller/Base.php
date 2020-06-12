<?php

namespace bingher\ueditor\controller;

use bingher\ueditor\config\UeConfig;

/**
 * 基础控制器
 */
class Base
{
    protected $config = [];

    public function __construct()
    {
        $this->config = new UeConfig();
        if ($this->config->get('auth_control')) {
            if (!call_user_func($this->config->get('auth_control'))) {
                throw new \think\exception\HttpResponseException($this->error('auth error'));
            }
        }
    }

    /**
     * 路径拼接
     * 1.传入的多个参数用DIRECTORY_SEPARATOR连接
     * 2.将\\替换为/
     *
     * @param array ...$args
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
     * @param string $msg
     * @param integer $code
     * @return \think\Response
     */
    protected function error($msg = 'ERROR', $code = 0)
    {
        return json(['state' => $msg, 'code' => $code]);
    }

    /**
     * 返回响应信息
     *
     * @param array $data
     * @param string $msg
     * @param integer $code
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
