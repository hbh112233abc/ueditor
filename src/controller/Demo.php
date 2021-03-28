<?php

namespace bingher\ueditor\controller;

/**
 * DEMO 控制器
 */
class Demo
{
    /**
     * 配置页面 ue_setting demo
     *
     * @return \think\Response\Html;
     */
    public function setting()
    {
        return view('../vendor/bingher/ueditor/src/view/setting.html');
    }

    /**
     * 使用示例 ue_view demo
     *
     * @return \think\Response\Html;
     */
    public function view()
    {
        return view('../vendor/bingher/ueditor/src/view/view.html');
    }
}
