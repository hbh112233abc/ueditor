<?php

/**
 * 页面中引入ueditor
 * 例: {:ue_view()}
 * @param string $name 控件id及表单字段名称name,默认ueditor,如果同一个页面多个ueditor请分别设置不同id
 * @param string $content 编辑内容
 * @param array $config 配置项, 参考assets/ueditor/ueditor.config.js
 * @return string
 */
function ue_view(string $name = 'ueditor', string $content = '', array $config = [])
{
    $default = [
        "serverUrl"          => '/ueditor/index',
        "UEDITOR_HOME_URL"   => '/static/bingher/ueditor/',
        "initialFrameHeight" => 600,
        "autoHeightEnabled"  => true,
        "maximumWords"       => 200000,
        "initialContent"     => $content,
    ];
    $config     = array_merge($default, $config);
    $configJson = json_encode($config);
    $result     = <<<tpl
<script id="{$name}" name="{$name}" type="text/plain"></script>
<!-- 引入ueditor的js代码 -->
<script src="/static/bingher/ueditor/ueditor.config.js"></script>
<script src="/static/bingher/ueditor/ueditor.all.js"></script>
<script>
    //ueditor代码
    var ue = UE.getEditor("{$name}",{$configJson});
</script>
tpl;
    return $result;
}

/**
 * ueditor 设置
 *
 * @return string
 */
function ue_setting(): string
{
    return (new \bingher\ueditor\controller\Setting)->index();
}

/**
 * 路径拼接
 * 1.传入的多个参数用DIRECTORY_SEPARATOR连接
 * 2.将\\替换为/
 *
 * @param array ...$args
 * @return string
 */
function path_join(...$args): string
{
    $path = implode(DIRECTORY_SEPARATOR, $args);
    $path = path_fmt($path);
    return $path;
}

/**
 * 路径格式
 *
 * @param string $path
 * @return string
 */
function path_fmt($path)
{
    $path = str_replace('//', '/', $path);
    $path = str_replace('\\', '/', $path);
    return $path;
}
