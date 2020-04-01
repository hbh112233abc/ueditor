<?php
namespace bingher\ueditor\controller;

use bingher\ueditor\config\Config;
use think\facade\Request;
use think\Image;
use think\facade\Filesystem;

class Ueditor
{
    protected $config = [];
    protected $uid;
    protected $error;
    protected $upfile;

    function __construct()
    {
        $this->config = Config();
        $uidKey = $this->config->get('session_uid_key','uid');
        $this->uid = session($uidKey) ? strval(session($uidKey)) : '';
        $this->upfile = $this->config->get('upload_field_name','upfile');

        $fsConfig  = Filesystem::getDiskConfig(Filesystem::getDefaultDriver());
        $this->rootPath = $fsConfig['root'];
        $this->savePath = self::pathJoin('ueditor',$this->uid);
        $this->urlPath  = $fsConfig['url'];
    }

    public function index()
    {
        $action = Request::param('action','');
        switch ($action) {
            case 'config':
                return $this->config->json();
                break;
            
            /* 上传图片 */
            case 'uploadimage':
                $config = array(
                    "pathFormat" => $this->config->get('imagePathFormat'),
                    "maxSize"    => $this->config->get('imageMaxSize'),
                    "allowFiles" => $this->config->get('imageAllowFiles'),
                );
                $result    = $this->upFile($config);
                break;

            /* 上传涂鸦 */
            case 'uploadscrawl':
                $config = array(
                    "pathFormat" => $this->config->get('scrawlPathFormat'),
                    "maxSize"    => $this->config->get('scrawlMaxSize'),
                    "allowFiles" => $this->config->get('scrawlAllowFiles'),
                    "oriName"    => "scrawl.png",
                );
                $base64    = "base64";
                $result    = $this->upBase64($config);
                break;

            /* 上传视频 */
            case 'uploadvideo':
                $config = array(
                    "pathFormat" => $this->config->get('videoPathFormat'),
                    "maxSize"    => $this->config->get('videoMaxSize'),
                    "allowFiles" => $this->config->get('videoAllowFiles'),
                );
                $result    = $this->upFile($config);
                break;

            /* 上传文件 */
            case 'uploadfile':
                // default:
                $config = array(
                    "pathFormat" => $this->config->get('filePathFormat'),
                    "maxSize"    => $this->config->get('fileMaxSize'),
                    "allowFiles" => $this->config->get('fileAllowFiles'),
                );
                $result    = $this->upFile($config);
                break;

            /* 列出图片 */
            case 'listimage':
                $allowFiles = $this->config->get('imageManagerAllowFiles');
                $listSize   = $this->config->get('imageManagerListSize');
                $path       = $this->config->get('imageManagerListPath');
                $get        = $_GET;
                $result     = $this->fileList($allowFiles, $listSize, $get);
                break;
            /* 列出文件 */
            case 'listfile':
                $allowFiles = $this->config->get('fileManagerAllowFiles');
                $listSize   = $this->config->get('fileManagerListSize');
                $path       = $this->config->get('fileManagerListPath');
                $get        = $_GET;
                $result     = $this->fileList($allowFiles, $listSize, $get);
                break;

            /* 抓取远程文件 */
            case 'catchimage':
                $config = array(
                    "pathFormat" => $this->config->get('catcherPathFormat'),
                    "maxSize"    => $this->config->get('catcherMaxSize'),
                    "allowFiles" => $this->config->get('catcherAllowFiles'),
                    "oriName"    => "remote.png",
                );

                /* 抓取远程图片 */
                $list     = [];
                $failList = []; //错误的列表
                $source = Request::param($this->config->get('upload_field_name','upfile'));
                if (empty($source)) {
                    return $this->error('参数错误');
                }

                foreach ($source as $imgUrl) {
                    $remoteResult = $this->saveRemote($config, $imgUrl);
                    if ($remoteResult === false) {
                        array_push($failList, array(
                            "state"  => $this->error,
                            "source" => htmlspecialchars($imgUrl),
                        ));
                    } else {
                        array_push($list, array(
                            "state"    => $remoteResult["state"],
                            "url"      => $remoteResult["url"],
                            "size"     => $remoteResult["size"],
                            "title"    => htmlspecialchars($remoteResult["title"]),
                            "original" => htmlspecialchars($remoteResult["original"]),
                            "source"   => htmlspecialchars($imgUrl),
                        ));
                    }                   
                }

                $result = json_encode([
                    'state'     => count($list) ? 'SUCCESS' : 'ERROR',
                    'list'      => $list,
                    'fail_list' => $failList,
                ]);                
                break;

            default:
                return $this->error('请求地址出错');
                break;
        }

        /**错误信息 */
        if($result === false){
            return $this->error($this->error);
        }

        $callback = Request::param('callback');
        if (empty($callback)) {
            return $this->success($result);
        }

        if (!preg_match('/^[\w_]+$/',$callback)) {
            return $this->error('callback参数不合法');
        }

        return htmlspecialchars($callback . '(' . $result . ')');
    }

    

    /**
     * 上传文件的主处理方法
     * @return mixed
     */
    private function upFile($config)
    {        
        $file = request()->file($this->config->get('upload_field_name','upfile'));
        $check = $this->check($config,$file);
        if ($check !== true) {
            return $check;
        }

        $saveName = Filesystem::putFile($this->savePath, $file);
        if (!$saveName) {
            $this->error = '文件上传失败';
            return false;
        }

        $filePath    = self::pathJoin($this->rootPath, $saveName);
        $ext      = $file->getExtension();
        if($this->isImage($ext)){
            try {
                $this->imageHandle($filePath,$ext);                
            } catch (\Exception $e) {
                $this->error = $e->getMessage();
                return false;
            }
        }        

        $data = [
            'url'      => self::pathJoin($this->urlPath, $saveName),
            'title'    => $file->getFileName(),
            'original' => $file->getFileName(),
            'type'     => '.' . $ext,
            'size'     => $file->getSize(),
        ];
        
        return $data;
    }

    /**
     * 图片再处理(压缩,水印)
     *
     * @param string $filePath
     * @param string $ext
     * @return void
     */
    protected function imageHandle($filePath,$ext)
    {
        $thumbType = $this->config->get('thumb_type',0);
        $quality  = $this->config->get('image_upload_quality', 80); //获取图片清晰度设置，默认是80
        $maxLimit = $this->config->get('image_upload_max_limit', 680); //获取图片宽高的最大限制值，0为不限制

        $image = Image::open($filePath);
        if ($maxLimit > 0 && $thumbType > 0) {
            $image->thumb($maxLimit, $maxLimit, $thumbType); //设置缩略图模式，按宽最大680或高最大680压缩
        }
        if ($this->water == 1) {
            $font = $this->config->get('water_font_path',__DIR__.'/../assets/zhttfs/1.ttf');
            $image->text($this->config->get('water_text'), $font, 10, '#FFCC66', $this->config->get('water_position'), [-8, -8])->save($filePath, $ext, $quality);
        } else if ($this->water == 2) {
            $image->water($this->config->get('water_image'),$this->config->get('water_position'),80)->save($filePath, $ext, $quality);
        } else {
            $image->save($filePath, $ext, $quality);
        }
        return $filePath;
    }

    /**
     * 根据文件后缀判断是不是图片
     *
     * @param string $ext
     * @return boolean
     */
    protected function isImage($ext)
    {
        $imageExts = self::formatExts($this->config->get('imageAllowFiles',[]));
        return in_array($ext,$imageExts);
    }

    /**
     * 处理base64编码的图片上传
     * @return mixed
     */
    private function upBase64($config)
    {
        $base64Data = Request::post($this->upfile);
        if (empty($base64Data)) {
            $this->error = '参数数据为空';
            return false;
        }

        $img        = base64_decode($base64Data);

        $savePath         = $this->savePath;
        $dirname          = self::pathJoin($this->rootPath, $savePath);
        $file['filesize'] = strlen($img);
        $file['oriName']  = $config['oriName'];
        $file['ext']      = strtolower(strrchr($config['oriName'], '.'));
        $file['name']     = uniqid() . $file['ext'];
        $file['fullName'] = self::pathJoin($dirname, $file['name']);
        $fullName         = $file['fullName'];

        //检查文件大小是否超出限制
        if ($file['filesize'] >= ($config["maxSize"])) {
            $this->error = '文件大小超出网站限制';
            return false;
        }

        //创建目录失败
        if (!file_exists($dirname) && !mkdir($dirname, 0777, true)) {
            $this->error = '目录创建失败';
            return false;            
        } else if (!is_writeable($dirname)) {
            $this->error = '目录没有写权限';
            return false;            
        }

        //移动文件
        if (!(file_put_contents($fullName, $img) && file_exists($fullName))) {
            //移动失败            
            $this->error = '写入文件内容错误';
            return false;  
        }
        
        //移动成功
        $data = [
            'url'      => self::pathJoin($this->urlPath, $savePath, $file['name']),
            'title'    => $file['name'],
            'original' => $file['oriName'],
            'type'     => $file['ext'],
            'size'     => $file['filesize'],
        ];
        
        return $data;
    }

    /**
     * 拉取远程图片
     * @return mixed
     */
    private function saveRemote($config, $fieldName)
    {
        $imgUrl = htmlspecialchars($fieldName);
        $imgUrl = str_replace("&amp;", "&", $imgUrl);

        //http开头验证
        if (strpos($imgUrl, "http") !== 0) {            
            $this->error = '链接不是http|https链接';
            return false;
        }
        //获取请求头并检测死链
        $heads = get_headers($imgUrl, true);
        if (!(stristr($heads[0], "200") && stristr($heads[0], "OK"))) {            
            $this->error = '链接不可用';
            return false;
        }
        //格式验证(扩展名验证和Content-Type验证)
        $fileType = strtolower(strrchr(strrchr($imgUrl, '/'), '.'));
        //img链接后缀可能为空,Content-Type须为image
        if ((!empty($fileType) && !in_array($fileType, $config['allowFiles'])) || stristr($heads['Content-Type'], "image") === -1) {            
            $this->error = '链接contentType不正确';
            return false;
        }

        //解析出域名作为http_referer
        $urlArr      = explode('/', $imgUrl);
        $protocol    = str_replace(':', '', $urlArr[0]);
        $httpReferer = $protocol . ':' . '//' . $urlArr[2];

        //打开输出缓冲区并获取远程图片
        ob_start();
        $context = stream_context_create([
            'http' => array(
                //'header' => "Referer:$httpReferer",  //突破防盗链,不可用
                'user_agent'      => 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.142 Safari/537.36', //突破防盗链
                'follow_location' => false, // don't follow redirects
            ),
        ]);
        $res     = false;
        $message = '';
        try {
            $res = readfile($imgUrl, false, $context);
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        $img = ob_get_contents();
        ob_end_clean();

        if ($res === false) {            
            $this->error = $message;
            return false;
        }

        preg_match("/[\/]([^\/]*)[\.]?[^\.\/]*$/", $imgUrl, $fileName);

        $savePath         = self::pathJoin($this->savePath, date('Ymd'));
        $dirname          = self::pathJoin($this->rootPath, $savePath);
        $file['oriName']  = $fileName ? $fileName[1] : "";
        $file['filesize'] = strlen($img);
        $file['ext']      = strtolower(strrchr($config['oriName'], '.'));
        $file['name']     = uniqid() . $file['ext'];
        $file['fullName'] = $dirname . $file['name'];
        $fullName         = $file['fullName'];

        //检查文件大小是否超出限制
        if ($file['filesize'] >= ($config["maxSize"])) {
            $this->error = '文件大小超出网站限制';
            return false;
        }

        //创建目录失败
        if (!file_exists($dirname) && !mkdir($dirname, 0777, true)) {            
            $this->error = '目录创建失败';
            return false;
        } else if (!is_writeable($dirname)) {            
            $this->error = '目录没有写权限';
            return false;
        }

        //移动文件
        if (!(file_put_contents($fullName, $img) && file_exists($fullName))) {
            //移动失败
            $this->error = '写入文件内容错误';
            return false;
        }
        
        //移动成功
        $data = array(
            'state'    => 'SUCCESS',
            'url'      => self::pathJoin($this->urlPath,$savePath, $file['name']),
            'title'    => $file['name'],
            'original' => $file['oriName'],
            'type'     => $file['ext'],
            'size'     => $file['filesize'],
        );
        
        return $data;
    }


    /**
     * 文件列表
     *
     * @param array $allowFiles 指定的文件后缀数组
     * @param int $listSize 列表分页数量
     * @param array $get ['size'=>xxx,'start'=>xxx]
     * @return array
     */
    private function fileList($allowFiles, $listSize, $get)
    {
        $dirname = self::pathJoin($this->rootPath,'ueditor');
        if ($this->uid != $this->config->get('super_admin_uid','admin')) {
            $dirname = self::pathJoin($dirname,$this->uid);
        }

        $allowFiles = substr(str_replace(".", "|", join("", $allowFiles)), 1);

        /* 获取参数 */
        $size  = isset($get['size']) ? htmlspecialchars($get['size']) : $listSize;
        $start = isset($get['start']) ? htmlspecialchars($get['start']) : 0;
        $end   = $start + $size;

        /* 获取文件列表 */
        $files = $this->getFiles($dirname, $allowFiles);
        if (!count($files)) {
            return [
                "state" => "no match file",
                "list"  => [],
                "start" => $start,
                "total" => count($files),
            ];
        }

        /* 获取指定范围的列表 */
        $len = count($files);
        for ($i = min($end, $len) - 1, $list = array(); $i < $len && $i >= 0 && $i >= $start; $i--) {
            $list[] = $files[$i];
        }
        
        /* 返回数据 */
        $result = [
            "state" => "SUCCESS",
            "list"  => $list,
            "start" => $start,
            "total" => count($files),
        ];

        return $result;
    }

    /**
     * 遍历获取目录下的指定类型的文件
     * @param string $path 文件路径
     * @param string $allowFiles 指定的文件后缀,以|分隔的文本
     * @param array $files 文件数组
     * @return array
     */
    private function getFiles($path, $allowFiles, &$files = array())
    {
        if (!is_dir($path)) {
            return [];
        }
        if (substr($path, strlen($path) - 1) != '/') {
            $path .= '/';
        }

        $handle = opendir($path);
        while (false !== ($file = readdir($handle))) {
            if ($file != '.' && $file != '..') {
                $path2 = $path . $file;
                if (is_dir($path2)) {
                    $this->getFiles($path2, $allowFiles, $files);
                } else {
                    if (preg_match("/\.(" . $allowFiles . ")$/i", $file)) {
                        $files[] = array(
                            'url'   => preg_replace('/(.*)upload/i', '/upload', $path2),
                            'mtime' => filemtime($path2),
                        );
                    }
                }
            }
        }

        return $files;
    }

    /**
     * [formatUrl 格式化url，用于将getFiles返回的文件路径进行格式化，起因是中文文件名的不支持浏览]
     * @param  array $files [文件数组]
     * @return array   [格式化后的文件数组]
     */
    static public function formatUrl($files)
    {
        if (!is_array($files)) {
            return $files;
        }

        foreach ($files as $key => $value) {
            $data = [];
            $data = explode('/', $value);
            foreach ($data as $k => $v) {
                if ($v != '.' && $v != '..') {
                    $data[$k] = urlencode($v);
                    $data[$k] = str_replace("+", "%20", $data[$k]);
                }
            }
            $files[$key] = implode('/', $data);
        }

        return $files;
    }

    /**
     * 格式化文件后缀
     * 去除后缀名前面的.,例".exe"=>"exe"
     *
     * @param array $exts 文件后缀数组
     * @return array
     */
    static public function formatExts(array $exts):array
    {
        $data = [];
        foreach ($exts as $key => $value) {
            $data[] = ltrim($value, '.');
        }
        return $data;
    }

    /**
     * 路径拼接
     * 1.传入的多个参数用DIRECTORY_SEPARATOR连接
     * 2.将\\替换为/
     * 
     * @param array ...$args
     * @return string
     */
    static public function pathJoin(...$args):string
    {
        $path = implode(DIRECTORY_SEPARATOR,$args);
        $path = str_replace('\\','/',$path);
        return $path;
    }

    /**
     * 返回错误信息
     *
     * @param string $msg
     * @param integer $code
     * @return \think\Response
     */
    protected function error($msg = 'ERROR',$code=0)
    {
        return json(['state'=>$msg,'code'=>$code]);
    }

    /**
     * 返回响应信息
     *
     * @param array $data
     * @param string $msg
     * @param integer $code
     * @return \think\Response
     */
    protected function success(array $data = [],$msg = 'SUCCESS',$code=1)
    {
        if (empty($data['state'])) {
            $data['state'] = $msg;
        }
        if(empty($data['code'])){
            $data['code'] = $code;
        }
        return json($data);
    }

    /**
     * 上传文件验证
     * 1. 验证文件大小
     * 2. 验证文件后缀
     *
     * @param array $config
     * @param Object $file
     * @return bool
     */
    protected function check($config,$file)
    {
        try {
            $rule = [
                $this->config->get('upload_field_name') => [
                    'fileSize' => $config['maxSize'],
                    'fileExt' => self::formatExts($config['allowFiles']),
                ]
            ];
            validate($rule)->check($file);
            return True;
        } catch (\think\exception\ValidateException $e) {
            $this->error = $e->getMessage();
            return False;
        }
    }
}