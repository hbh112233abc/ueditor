<?php

function test_helper(){
    require __DIR__.'/helper.php';
    
    $res = ue_view();
    var_dump($res);
}

function test_fileutil(){
    require __DIR__.'/util/FileUtil.php';
    $res = \bingher\ueditor\util\FileUtil::isDirEmpty('./util');
    var_dump($res);
    $res = \bingher\ueditor\util\FileUtil::isDirEmpty('./util11');
    var_dump($res);
    $res = \bingher\ueditor\util\FileUtil::isDirEmpty('./blank');
    var_dump($res);
}

test_helper();