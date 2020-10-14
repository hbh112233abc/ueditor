<?php

namespace bingher\ueditor\validate;

use think\Validate;

class UeditorValidate extends Validate
{
    protected $rule = [
        'max_image_size|上传图片限制'          => 'require|number|between:10240,512000000',
        'max_vedio_size|上传视频限制'          => 'require|number|between:10240,5120000000',
        'max_file_size|上传文件限制'           => 'require|number|between:10240,512000000',
        'thumb_type|缩略图类型'               => 'require|between:0,6',
        'thumb_image_quality|缩略图质量'      => 'requireCallback:with_thumb|between:50,100',
        'thumb_max_width_height|缩略图最大宽高' => 'requireCallback:with_thumb|number',
        'water|水印类型'                     => 'require|between:0,2',
        'water_position|水印位置'            => 'requireCallback:with_water|between:1,9',
        'water_text|水印文字'                => 'requireCallback:with_water_text',
        'water_image|水印图片'               => 'requireCallback:with_water_image',
        'session_uid_key'                => 'require',
        'super_admin_uid'                => 'require',
        'filesystem_type|文件系统存储类型'       => 'require|check_fs_type:local',
        'filesystem_root|文件系统存储路径'       => 'require|check_fs_root',
        'filesystem_url|文件系统访问路径'        => 'require',
    ];

    /**
     * 需缩略图
     *
     * @param string $value
     * @param array $data
     * @return void
     */
    protected function with_thumb($value, $data)
    {
        if ($data['thumb_type'] > 0) {
            return true;
        }
        return false;
    }

    /**
     * 带水印
     *
     * @param string $value
     * @param array $data
     * @return void
     */
    protected function with_water($value, $data)
    {
        if ($data['water'] > 0) {
            return true;
        }
        return false;
    }

    /**
     * 带文字水印
     *
     * @param string $value
     * @param array $data
     * @return void
     */
    protected function with_water_text($value, $data)
    {
        if ($data['water'] == 1) {
            return true;
        }
        return false;
    }

    /**
     * 带图片水印
     *
     * @param string $value
     * @param array $data
     * @return void
     */
    protected function with_water_image($value, $data)
    {
        if ($data['water'] == 2) {
            return true;
        }
        return false;
    }

    protected function check_fs_type($value, $rule = 'local', $data)
    {
        if ($value != $rule) {
            return '当前类型仅支持local';
        }
        return true;
    }

    protected function check_fs_root($value, $rule = '', $data)
    {
        if (!is_dir($value)) {
            return '存储路径不存在或无法访问';
        }
        return true;
    }

    /**
     * 验证文件系统filesystem配置
     *
     * @param array $value
     * @param string $rule
     * @param array $data
     * @return string|true
     */
    protected function check_filesystem($value, $rule = '', $data)
    {
        if (!is_array($value)) {
            return '需要提交数组格式';
        }
        if ($value['"type"'] != 'local') {
            return '当前类型仅支持local';
        }
        if (empty($value['"root"'])) {
            return '请输入存储位置';
        }
        if (!is_dir($value['"root"'])) {
            return '存储位置路径错误';
        }
        if (empty($value['"url"'])) {
            return '请输入存储访问路径';
        }
        return true;
    }
}
