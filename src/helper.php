<?php

/**
 * 页面中引入ueditor
 * 例: {:ue_view()}
 * @param string $id 控件id,默认container,如果同一个页面多个ueditor请分别设置不同id
 * @param string $content 编辑内容
 * @param array $config 配置项, 参考assets/ueditor/ueditor.config.js
 * @return string
 */
function ue_view(string $id='container',string $content='',array $config=[]){
    $default = [
        "serverUrl" => url('/ueditor/index')->build(),
        "UEDITOR_HOME_URL"=>'/../vendor/bingher/ueditor/assets/ueditor/',
        "initialFrameHeight"=>600,
        "autoHeightEnabled"=>true,
        "maximumWords"=>200000,
        "initialContent"=> $content,
    ];
    $config = array_merge($default,$config);
    $configJson = json_encode($config);
    $result = <<<EOF
    <script id="{$id}" type="text/plain"></script>
    <!-- 引入ueditor的js代码 -->
    <script src="/../vendor/bingher/ueditor/assets/ueditor/ueditor.config.js"></script>
    <script src="/../vendor/bingher/ueditor/assets/ueditor/ueditor.all.js"></script>
    <script>
        //ueditor代码
        var ue = UE.getEditor("{$id}",{$config});
    </script>
    EOF;
    return $result;
}