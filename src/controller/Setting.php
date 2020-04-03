<?php
namespace bingher\ueditor\controller;

use think\facade\Db;
use FormBuilder\Form;
use FormBuilder\Factory\Elm;
use think\Image;
use bingher\ueditor\config\UeConfig;

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
        $config = Db::table('ueditor_config')->column('value,remark','name');
        $config['filesystem']['value'] = json_decode($config['filesystem']['value']);
        $input = [
            Elm::number('max_image_size','max_image_size',$config['max_image_size']['value'])->min(10240)->max(512000000)->step(10240)->info($config['max_image_size']['remark']),
            Elm::number('max_file_size','max_file_size',$config['max_file_size']['value'])->min(10240)->max(512000000)->step(10240)->info($config['max_file_size']['remark']),
            Elm::number('max_vedio_size','max_vedio_size',$config['max_vedio_size']['value'])->min(10240)->max(512000000)->step(10240)->info($config['max_vedio_size']['remark']),
            Elm::select('thumb_type','thumb_type')->options([
                ['value'=>0,'label'=>'不生成缩略图'],
                ['value'=>Image::THUMB_SCALING,   'label'=>'缩略图等比例缩放类型'],
                ['value'=>Image::THUMB_FILLED,    'label'=>'缩略图缩放后填充类型'],
                ['value'=>Image::THUMB_CENTER,    'label'=>'缩略图居中裁剪类型'],
                ['value'=>Image::THUMB_NORTHWEST, 'label'=>'缩略图左上角裁剪类型'],
                ['value'=>Image::THUMB_SOUTHEAST, 'label'=>'缩略图右下角裁剪类型'],
                ['value'=>Image::THUMB_FIXED,     'label'=>'缩略图固定尺寸缩放类型'],
            ])->info($config['thumb_type']['remark'])->value($config['thumb_type']['value']),
            Elm::number('thumb_image_quality','thumb_image_quality',$config['thumb_image_quality']['value'])->min(50)->max(100)->info($config['thumb_image_quality']['remark']),
            Elm::number('thumb_max_width_height','thumb_max_width_height',$config['thumb_max_width_height']['value'])->min(0)->info($config['thumb_max_width_height']['remark']),
            Elm::select('water','water')->options([
                ['value'=>0,'label'=>'无水印'],
                ['value'=>1,'label'=>'文字水印'],
                ['value'=>2,'label'=>'图片水印'],
            ])->info($config['water']['remark'])->value($config['water']['value']),
            Elm::input('water_text','water_text',$config['water_text']['value'])->info($config['water_text']['remark']),
            Elm::select('water_position','water_position')->options([
                ['value'=>Image::WATER_NORTHWEST, 'label'=>'标识左上角水印'],
                ['value'=>Image::WATER_NORTH,     'label'=>'标识上居中水印'],
                ['value'=>Image::WATER_NORTHEAST, 'label'=>'标识右上角水印'],
                ['value'=>Image::WATER_WEST,      'label'=>'标识左居中水印'],
                ['value'=>Image::WATER_CENTER,    'label'=>'标识居中水印'],
                ['value'=>Image::WATER_EAST,      'label'=>'标识右居中水印'],
                ['value'=>Image::WATER_SOUTHWEST, 'label'=>'标识左下角水印'],
                ['value'=>Image::WATER_SOUTH,     'label'=>'标识下居中水印'],
                ['value'=>Image::WATER_SOUTHEAST, 'label'=>'标识右下角水印'],
            ])->info($config['water_position']['remark'])->value($config['water_position']['value']),
            Elm::uploadImage('water_image', 'water_image','/ueditor/setting/upload_imgage',$config['water_image']['value'])->uploadName('water_image')->info($config['water_image']['remark']),
            Elm::input('session_uid_key','session_uid_key',$config['session_uid_key']['value'])->maxlength(16)->required()->info($config['session_uid_key']['remark']),
            Elm::input('super_admin_uid','super_admin_uid',$config['super_admin_uid']['value'])->maxlength(16)->required()->info($config['super_admin_uid']['remark']),
            Elm::input('filesystem[type]','filesystem[type]',$config['filesystem']['value']['type'])->readonly()->info('文件系统,使用本地存储类型:local'),
            Elm::input('filesystem[root]','filesystem[root]',$config['filesystem']['value']['root'])->required()->info('文件系统,存储路径'),
            Elm::input('filesystem[url]','filesystem[url]',$config['filesystem']['value']['url'])->required()->info('文件系统,访问路径'),
        ];
        //创建表单
        $form = Form::elm('/ueditor/setting/save');
        $form->setMethod('POST');
        $form->setTitle('Ueditor配置');

        $form->setValue('thumb_type',$config['thumb_type']['value']);
        // $form->setValue('water',$config['water']['value']);
        // $form->setValue('water_position',$config['water_position']['value']);

        //添加组件
        $form->setRule($input);

        //生成表单页面
        return $form->view();
    }

    /**
     * 保存数据
     *
     * @return void
     */
    public function save()
    {

    }

    /**
     * 图片上传
     *
     * @return void
     */
    public function uploadImage()
    {
        $files = request()->file();
        if(empty($files)){
            return 'upload files count is 0';
        }

        $fs = (new UeConfig())->initFilesystem();
        $result = [];
        foreach ($files as $k => $file) {
            $saveName = $fs->putFile('config',$file);
            $filePath = $fs->path($saveName);
            $fileUrl = substr($filePath,strlen(public_path('public'))-1);
            $result['data'] = [
                'name' => $_FILES[$k]['name'],
                'filePath' => path_fmt($fileUrl),
            ];
        }
        $result['code'] = 200;

        return json($result);
    }
}
