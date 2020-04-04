<?php

return [
    // 上传大小限制，单位B  102400=100KB, 512000=500KB,1048576=1M
    'max_image_size' => 1048576,
    // 上传大小限制，单位B，默认100MB
    'max_vedio_size' => 102400000,
    // 上传大小限制，单位B，默认50MB
    'max_file_size' => 51200000,
    // 0 不做缩略图,缩略图模式参考\think\Image::THUMB_*常量
    'thumb_type' => 1,
    //缩略图图片清晰度设置，默认是80
    'thumb_image_quality' => 80,
    //获取图片宽高的最大限制值，0为不限制
    'thumb_max_width_height' => 680,
    //是否加水印(0:无水印,1:水印文字,2:水印图片
    'water' => 0,
    //水印文
    'water_text' => '',
    //水印位置,默认右下角 参考\think\Image::WATER_*常量
    'water_position' => 9,
    //水印图片路
    'water_image' => '',
    //上传表单字段
    'upload_field_name' => 'upfile',
    //用户session账号i
    'session_uid_key' => 'uid',
    //超级管理员uid
    'super_admin_uid' => 'admin',
    //磁盘类型
    'filesystem_type'       => 'local',
    //磁盘路径
    'filesystem_root'       => app()->getRootPath() . 'public/upload',
    //磁盘路径对应的外部URL路径
    'filesystem_url'        => '/upload',
];
