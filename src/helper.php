<?php

define('UE_VERSION', '1.2.0');
$jsStart = false;

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
    global $jsStart;
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
    $result = '';
    if (!$jsStart) {
        $result .= ue_js();
    }
    $result     .= <<<tpl
<script id="{$name}" name="{$name}" type="text/plain"></script>
<script>
    //ueditor代码
    var ue = UE.getEditor("{$name}",{$configJson});
</script>
tpl;
    return $result;
}

/**
 * 引入js
 *
 * @param string $version 版本号
 * @return string
 */
function ue_js(string $version = ''): string
{
    global $jsStart;
    $version = empty($version) ? UE_VERSION : $version;
    $jsStart = true;
    return '<!-- 引入ueditor的js代码 -->
<script src="/static/bingher/ueditor/ueditor.config.js?v=' . $version . '"></script>
<script src="/static/bingher/ueditor/ueditor.all.min.js?v=' . $version . '"></script>';
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

/**
 * 验证session中是否有uid
 * @return bool 验证结果
 */
function check_uid()
{
    if (env('app_debug')) {
        return true;
    }
    $config = new \bingher\ueditor\config\UeConfig();
    $uid    = $config->get('session_uid_key', 'uid');
    if (session($uid)) {
        return true;
    }
    return false;
}
