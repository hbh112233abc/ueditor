# bingher/ueditor
> thinkphp6 ueditor 插件

## 安装
```
composer require bingher/ueditor
```

## 配置
配置文件 config/ueditor.php
```
'max_image_size' => 1048576, /* 上传大小限制，单位B  102400=100KB, 512000=500KB,1048576=1M */
'max_vedio_size' => 102400000, /* 上传大小限制，单位B，默认100MB */
'max_file_size' => 51200000, /* 上传大小限制，单位B，默认50MB */   
'thumb_type' => 1,/* 0 不做缩略图,缩略图模式：1、标识缩略图等比例缩放类型，2、标识缩略图缩放后填充类型 参考\think\Image::THUMB_*常量*/
'thumb_image_quality' => 80, /*缩略图图片清晰度设置，默认是80 */
'thumb_max_width_height' => 680, /**获取图片宽高的最大限制值，0为不限制 */
'water' => 0, /*是否加水印(0:无水印,1:水印文字,2:水印图片)*/
'water_text' => '', /*水印文字*/
'water_position' => 9, /*水印位置,默认右下角 参考\think\Image::WATER_*常量 */
'water_image' => '', /*水印图片路径*/
'upload_field_name' => 'upfile', /*上传表单字段名*/
'session_uid_key' => 'uid', /*用户session账号id*/
'super_admin_uid' => 'admin', /**超级管理员uid */
```

## 使用
在模板文件中需要引入的地方加入以下代码
```
{:ue_view()}
```
ue_view方法如下:
```
function ue_view(string $id = 'ueditor',string $content = '',array $config = []){...}
```
|参数|说明|
|-|-|
|$id|生成的插件id,默认ueditor,同一个页面需要多个ueditor|
|$content|初始化内容,在需要编辑的时候传入编辑文本内容|
|$config|配置项,参考ueditor.config.js|

