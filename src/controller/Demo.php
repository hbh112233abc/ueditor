<?php

namespace bingher\ueditor\controller;

class Demo
{
    /**
     * ue_setting demo
     *
     * @return \think\Response\Html;
     */
    public function setting()
    {
        return view('../vendor/bingher/ueditor/src/view/setting.html');
    }

    /**
     * ue_view demo
     *
     * @return \think\Response\Html;
     */
    public function view()
    {
        return view('../vendor/bingher/ueditor/src/view/view.html');
    }
}
