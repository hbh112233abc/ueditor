<?php

use think\migration\db\Column;
use think\migration\Migrator;

class BingherUeditorInit extends Migrator
{
    protected $tableName = 'ueditor_config';
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     * createTable
     * renameTable
     * addColumn
     * renameColumn
     * addIndex
     * addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */

    public function up()
    {
        // create the table
        $table = $this->table($this->tableName, array('engine' => 'InnoDB'));
        $table->addColumn('name', 'string', array('limit' => 32, 'default' => '', 'comment' => '配置项名称'))
            ->addColumn('value', 'text', array('comment' => '配置项内容'))
            ->addColumn('group', 'string', array('limit' => 16, 'default' => 'config', 'comment' => '配置分组'))
            ->addColumn('remark', 'string', array('limit' => 225, 'default' => '', 'comment' => '配置项备注'))
            ->addIndex('name')
            ->create();

        /**基础配置项 */
        $rows = [
            ['name' => 'max_image_size', 'value' => 1048576, 'group' => 'base', 'remark' => '图片上传大小限制，单位B  102400=100KB, 512000=500KB,1048576=1M '],
            ['name' => 'max_vedio_size', 'value' => 102400000, 'group' => 'base', 'remark' => '视频上传大小限制，单位B，默认100MB '],
            ['name' => 'max_file_size', 'value' => 51200000, 'group' => 'base', 'remark' => '文件上传大小限制，单位B，默认50MB '],
            ['name' => 'thumb_type', 'value' => 1, 'group' => 'base', 'remark' => '缩略图模式：0:不做缩略图;1:标识缩略图等例缩放类型;2:标识缩略图缩放后填充类型 参考\\think\\Image::THUMB_*常量'],
            ['name' => 'thumb_image_quality', 'value' => 80, 'group' => 'base', 'remark' => '缩略图图片清晰度设置，默认是80 '],
            ['name' => 'thumb_max_width_height', 'value' => 680, 'group' => 'base', 'remark' => '缩略图宽高的最大限制值，0为不限制 '],
            ['name' => 'water', 'value' => 0, 'group' => 'base', 'remark' => '是否加水印(0:无水印,1:水印文字,2:水印图片)'],
            ['name' => 'water_text', 'value' => '', 'group' => 'base', 'remark' => '水印文字'],
            ['name' => 'water_position', 'value' => 9, 'group' => 'base', 'remark' => '水印位置,默认右下角 参考\think\Image::WATER_*常量 '],
            ['name' => 'water_image', 'value' => '', 'group' => 'base', 'remark' => '水印图片路径'],
            ['name' => 'upload_field_name', 'value' => 'upfile', 'group' => 'base', 'remark' => '上传表单字段'],
            ['name' => 'session_uid_key', 'value' => 'uid', 'group' => 'base', 'remark' => 'session用户uid'],
            ['name' => 'super_admin_uid', 'value' => 'admin', 'group' => 'base', 'remark' => '超级管理员uid '],
            ['name' => 'filesystem_type', 'value' => 'local', 'group' => 'base', 'remark' => '上传文件系统类型'],
            ['name' => 'filesystem_root', 'value' => './upload', 'group' => 'base', 'remark' => '上传文件系统存储路径'],
            ['name' => 'filesystem_url', 'value' => '/upload', 'group' => 'base', 'remark' => '上传文件系统访问路径'],
            ['name' => 'auth_control', 'value' => 'check_uid', 'group' => 'base', 'remark' => '权限控制函数'],
        ];

        $this->table($this->tableName)->insert($rows)->save();
    }

    public function down()
    {
        $table = $this->table($this->tableName);
        $table->drop();
    }
}
