<?php

namespace bingher\ueditor\controller;

use think\facade\Db;
use FormBuilder\Form;
use FormBuilder\Factory\Elm;
use think\Image;
use bingher\ueditor\config\UeConfig;
use bingher\ueditor\validate\UeditorValidate;

/**
 * ueditor配置项数据库设置
 */
class Setting
{
    /**
     * 展示表单
     *
     * @return void
     */
    public function index()
    {
        $config = Db::table('ueditor_config')->column('value,remark', 'name');
        $input = [
            Elm::number('max_image_size', '上传图片限制', $config['max_image_size']['value'])->min(10240)->max(512000000)->step(10240)->info($config['max_image_size']['remark']),
            Elm::number('max_file_size', '上传文件限制', $config['max_file_size']['value'])->min(10240)->max(512000000)->step(10240)->info($config['max_file_size']['remark']),
            Elm::number('max_vedio_size', '上传视频限制', $config['max_vedio_size']['value'])->min(10240)->max(5120000000)->step(10240)->info($config['max_vedio_size']['remark']),
            Elm::select('thumb_type', '缩略图类型', intval($config['thumb_type']['value']))->options([
                ['value' => 0, 'label' => '不生成缩略图'],
                ['value' => Image::THUMB_SCALING,   'label' => '缩略图等比例缩放'],
                ['value' => Image::THUMB_FILLED,    'label' => '缩略图缩放后填充'],
                ['value' => Image::THUMB_CENTER,    'label' => '缩略图居中裁剪'],
                ['value' => Image::THUMB_NORTHWEST, 'label' => '缩略图左上角裁剪'],
                ['value' => Image::THUMB_SOUTHEAST, 'label' => '缩略图右下角裁剪'],
                ['value' => Image::THUMB_FIXED,     'label' => '缩略图固定尺寸缩放'],
            ])->info($config['thumb_type']['remark']),
            Elm::number('thumb_image_quality', '缩略图质量', $config['thumb_image_quality']['value'])->min(50)->max(100)->info($config['thumb_image_quality']['remark']),
            Elm::number('thumb_max_width_height', '缩略图最长宽(高)', $config['thumb_max_width_height']['value'])->min(10)->info($config['thumb_max_width_height']['remark']),
            Elm::select('water', '选择水印')->options([
                ['value' => 0, 'label' => '无水印'],
                ['value' => 1, 'label' => '文字水印'],
                ['value' => 2, 'label' => '图片水印'],
            ])->info($config['water']['remark'])->value(intval($config['water']['value'])),
            Elm::input('water_text', '文字水印', $config['water_text']['value'])->info($config['water_text']['remark']),
            Elm::select('water_position', '水印位置')->options([
                ['value' => Image::WATER_NORTHWEST, 'label' => '标识左上角'],
                ['value' => Image::WATER_NORTH,     'label' => '标识上居中'],
                ['value' => Image::WATER_NORTHEAST, 'label' => '标识右上角'],
                ['value' => Image::WATER_WEST,      'label' => '标识左居中'],
                ['value' => Image::WATER_CENTER,    'label' => '标识居中'],
                ['value' => Image::WATER_EAST,      'label' => '标识右居中'],
                ['value' => Image::WATER_SOUTHWEST, 'label' => '标识左下角'],
                ['value' => Image::WATER_SOUTH,     'label' => '标识下居中'],
                ['value' => Image::WATER_SOUTHEAST, 'label' => '标识右下角'],
            ])->info($config['water_position']['remark'])->value(intval($config['water_position']['value'])),
            Elm::uploadImage('water_image', '图片水印', '/ueditor/setting/upload_imgage', $config['water_image']['value'])->uploadName('water_image')->info($config['water_image']['remark']),
            Elm::input('upload_field_name', '上传表单字段名称', $config['upload_field_name']['value'])->maxlength(16)->required()->info($config['upload_field_name']['remark']),
            Elm::input('session_uid_key', 'session用户id标识', $config['session_uid_key']['value'])->maxlength(16)->required()->info($config['session_uid_key']['remark']),
            Elm::input('super_admin_uid', 'session管理员id', $config['super_admin_uid']['value'])->maxlength(16)->required()->info($config['super_admin_uid']['remark']),
            Elm::input('filesystem_type', '文件系统存储类型', $config['filesystem_type']['value'])->info($config['filesystem_type']['remark'])->readonly(true),
            Elm::input('filesystem_root', '文件系统存储路径', $config['filesystem_root']['value'])->info($config['filesystem_root']['remark'])->required(),
            Elm::input('filesystem_url', '文件系统访问路径', $config['filesystem_url']['value'])->info($config['filesystem_root']['remark'])->required(),
        ];
        //创建表单
        $form = Elm::createForm('/ueditor/setting/save');
        $form->setMethod('POST');
        $form->setTitle('Ueditor配置');

        // $form->setValue('thumb_type', $config['thumb_type']['value']);
        // $form->setValue('water', $config['water']['value']);
        // $form->setValue('water_position', $config['water_position']['value']);

        //添加组件
        $form->setRule($input);

        //设置样式
        $formConfig = Elm::config();
        $formStyle = Elm::style();
        $formStyle->labelWidth('auto');
        $formConfig->formStyle($formStyle);
        $form->setConfig($formConfig);


        //生成表单页面
        return $form->view();
    }

    /**
     * 保存数据
     *
     * @return \think\Response\Json;
     */
    public function save()
    {
        $data = request()->post();
        try {
            validate(UeditorValidate::class)->check($data);
        } catch (\think\exception\ValidateException $e) {
            return json(['code' => 400, 'msg' => $e->getMessage()]);
        }
        foreach ($data as $k => $v) {
            if ($k == 'filesystem') {
                $v = json_encode($v);
            }
            Db::table('ueditor_config')->where('name', $k)->update(['value' => $v]);
        }
        cache('ueditor_config', null);
        return json(['code' => 200, 'msg' => '更新成功']);
    }

    /**
     * 图片上传
     *
     * @return \think\Response\Json
     */
    public function uploadImage()
    {
        $files = request()->file();
        if (empty($files)) {
            return 'upload files count is 0';
        }

        $fs = (new UeConfig())->initFilesystem();
        $result = [];
        foreach ($files as $k => $file) {
            $saveName = $fs->putFile('config', $file);
            $filePath = $fs->path($saveName);
            $fileUrl = substr($filePath, strlen(public_path('public')) - 1);
            $result['data'] = [
                'name' => $_FILES[$k]['name'],
                'filePath' => path_fmt($fileUrl),
            ];
        }
        $result['code'] = 200;

        return json($result);
    }
}
