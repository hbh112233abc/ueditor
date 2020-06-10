(function () {

    var insertaudio,
        uploadaudio;

    window.onload = function () {
        initTabs();
        initButtons();
        addUrlChangeListener($G("audioUrl"));
        addTitleChangeListener($(".uploadAudioTitle"));
    };


    /* 初始化tab标签 */
    function initTabs() {
        var tabs = $G('tabhead').children;
        var audio = editor.selection.getRange().getClosedNode();
        var id = tabs[0].getAttribute('data-content-id');
        for (var i = 0; i < tabs.length; i++) {
            domUtils.on(tabs[i], "click", function (e) {
                var j, bodyId, target = e.target || e.srcElement;
                id = target.getAttribute('data-content-id');
                for (j = 0; j < tabs.length; j++) {
                    bodyId = tabs[j].getAttribute('data-content-id');
                    if(tabs[j] == target){
                        domUtils.addClass(tabs[j], 'focus');
                        domUtils.addClass($G(bodyId), 'focus');
                    }else {
                        domUtils.removeClasses(tabs[j], 'focus');
                        domUtils.removeClasses($G(bodyId), 'focus');
                    }
                }
                switch (id) {
                    case 'remote':
                        insertaudio = insertaudio || new RemoteAudio();
                        break;
                    case 'upload':
                        uploadaudio = uploadaudio || new UploadAudio('queueList');
                        break;
                }
            });
        }
        for (var i = 0; i < tabs.length; i++) {
            if (domUtils.hasClass(tabs[i], 'focus')) {
                id = tabs[i].getAttribute('data-content-id');
                break;
            }
        }
        switch (id) {
            case 'remote':
                insertaudio = insertaudio || new RemoteAudio();
                break;
            case 'upload':
                uploadaudio = uploadaudio || new UploadAudio('queueList');
                break;
        }
    }

    /* 初始化onok事件 */
    function initButtons() {
        dialog.onok = function () {
            var remote = false, list = [], id, tabs = $G('tabhead').children;
            for (var i = 0; i < tabs.length; i++) {
                if (domUtils.hasClass(tabs[i], 'focus')) {
                    id = tabs[i].getAttribute('data-content-id');
                    break;
                }
            }
            switch (id) {
                case 'remote':
                    list = insertaudio.getInsertList();
                    break;
                case 'upload':
                    list = uploadaudio.getInsertList();
                    var count = uploadaudio.getQueueCount();
                    if (count) {
                        $('.info', '#queueList').html('<span style="color:red;">' + '还有2个未上传文件'.replace(/[\d]/, count) + '</span>');
                        return false;
                    }

                    // 配上标题
                    var title = $('#audio_title_2').val();
                    if (!title || $.trim(title) == '') {
                        alert('请填写标题');
                        $('#audio_title_2').focus();
                        return false;
                    }
                    if(list) {
                        for(var i = 0; i < list.length; i++) {
                            var f = list[i];
                            f['title'] = title;
                            list[i] = f;
                        }
                    }
                    break;
            }
            if(list) {
                editor.execCommand('insertaudio', list);
                remote && editor.fireEvent("catchRemoteAudio");
            }
        };
    }

    /**
     * 监听url改变事件
     * @param url
     */
    function addUrlChangeListener(url){
        if (browser.ie) {
            url.onpropertychange = function () {
                if($('#audio_title_1').val() == ''){
                    var basename = this.value.split('\/').pop();
                    $('#audio_title_1').val(basename);
                }
                createPreviewAudio( this.value,$('#audio_title_1').val());
            }
        } else {
            url.addEventListener( "change", function () {
                if($('#audio_title_1').val() == ''){
                    var basename = this.value.split('\/').pop();
                    $('#audio_title_1').val(basename);
                }
                createPreviewAudio( this.value,$('#audio_title_1').val());
            }, false );
        }

    }

    /**
     * 监听title改变事件
     * @param title
     */
    function addTitleChangeListener(title){
        title.on('change',function(e){
            console.log(e)
            console.log($(e.target).val());
            $('#preview').find('.audio-title').text($(e.target).val());
        });
    }

    /**
     * 根据url生成音频预览
     * @param url
     */
    function createPreviewAudio(url,title){
        if ( !url )return;
        var ext = getType(url);
        if(!(['.mp3','.wav','.ogg'].includes(ext))){
            alert('仅支持mp3,ogg,wav格式');
            return;
        }
        title = title || '';
        var key = new Date().getTime();
        document.querySelector("#preview").innerHTML = createAudioHtml(key,url,title);
        initAudioEvent(document.querySelector("#preview .audio-wrapper"));
    }

    //获取文件后缀
    function getType(file){
        var filename=file;
        var index1=filename.lastIndexOf(".");
        var index2=filename.length;
        var type=filename.substring(index1,index2);
        return type;
    }


    /**
     * 构造音频控件html
     *
     * @param {string} audioDivId - 音频控件父div的id
     * @param {string} audioSrc - 音频控件地址
     * @param {string} audioTitle - 音频标题
     */
    function createAudioHtml(audioDivId, audioSrc, audioTitle) {
        var playicon = '../../third-party/H5-audio/image/play.png';
        var src = '<div class="audio-wrapper" id="'+audioDivId+'">'
                +'    <audio>'
                +'        <source src="'+audioSrc+'">'
                +'    </audio>'
                +'    <div class="audio-left">'
                +'        <img class="audio-icon" src="'+playicon+'">'
                +'    </div>'
                +'    <div class="audio-right">'
                +'        <p class="audio-title" style="max-width: 536px;">'+audioTitle+'</p>'
                +'        <div class="progress-bar-bg">'
                +'            <span class="progress-dot"></span>'
                +'            <div class="progress-bar"></div>'
                +'        </div>'
                +'        <div class="audio-time">'
                +'            <span class="audio-length-current">00:00</span>'
                +'            <span class="audio-length-total"></span>'
                +'        </div>'
                +'    </div>'
                +'</div>';
        return src;
    }

    /**
     * 将单个音频插入编辑器中
     */
    function RemoteAudio() {
        this.init();
    }

    RemoteAudio.prototype = {
        init:function(){

        },
        getInsertList:function(){
            var url=$G('audioUrl').value;
            if(!url) return false;
            // 配上标题
            var title = $('#audio_title_1').val();
            if (!title || $.trim(title) == '') {
                alert('请填写标题');
                $('#audio_title_1').focus();
                return false;
            }
            var list = [
                {
                    src:url,
                    key:new Date().getTime(),
                    title:title,
                }
            ];
            return list;
        }
    }



    /* 上传音频 */
    function UploadAudio(target) {
        this.$wrap = target.constructor == String ? $('#' + target) : $(target);
        this.init();
    }
    UploadAudio.prototype = {
        init: function () {
            this.audioList = [];
            this.initContainer();
            this.initUploader();
        },
        initContainer: function () {
            this.$queue = this.$wrap.find('.filelist');
        },
        /* 初始化容器 */
        initUploader: function () {
            var _this = this,
            $ = jQuery,    // just in case. Make sure it's not an other libaray.
            $wrap = _this.$wrap,
            // 文件容器
            $queue = $wrap.find('.filelist'),
            // 状态栏，包括进度和控制按钮
            $statusBar = $wrap.find('.statusBar'),
            // 文件总体选择信息。
            $info = $statusBar.find('.info'),
            // 上传按钮
            $upload = $wrap.find('.uploadBtn'),
            // 上传按钮
            $filePickerBtn = $wrap.find('.filePickerBtn'),
            // 上传按钮
            $filePickerBlock = $wrap.find('.filePickerBlock'),
            // 没选择文件之前的内容。
            $placeHolder = $wrap.find('.placeholder'),
            // 总体进度条
            $progress = $statusBar.find('.progress').hide(),
            // 添加的文件数量
            fileCount = 0,
            // 添加的文件总大小
            fileSize = 0,

            // 可能有pedding, ready, uploading, confirm, done.
            state = '',
            // 所有文件的进度信息，key为file id
            percentages = {},
            supportTransition = (function () {
                var s = document.createElement('p').style,
                    r = 'transition' in s ||
                        'WebkitTransition' in s ||
                        'MozTransition' in s ||
                        'msTransition' in s ||
                        'OTransition' in s;
                s = null;
                return r;
            })(),
            // WebUploader实例
            uploader,
            actionUrl = editor.getActionUrl(editor.getOpt('audioActionName')),
            acceptExtensions = (editor.getOpt('audioAllowFiles') || []).join('').replace(/\./g, ',').replace(/^[,]/, ''),
            audioMaxSize = editor.getOpt('audioMaxSize');

            if (!WebUploader.Uploader.support()) {
                $('#filePickerReady').after($('<div>').html(lang.errorNotSupport)).hide();
                return;
            } else if (!editor.getOpt('audioActionName')) {
                $('#filePickerReady').after($('<div>').html(lang.errorLoadConfig)).hide();
                return;
            }

            uploader = _this.uploader = WebUploader.create({
                pick: {
                    id: '#filePickerReady',
                    label: lang.uploadSelectFile,
                    multiple: false // 限制为单选
                },
                accept: {
                    title: 'Audios',
                    extensions: acceptExtensions,
                    mimeTypes: 'audio/mp3,audio/amr,audio/wma,audio/wav'
                },
                swf: '../../third-party/webuploader/Uploader.swf',
                server: actionUrl,
                fileVal: editor.getOpt('audioFieldName'),
                duplicate: false,
                fileNumLimit: 1,    // 限制为单个文件
                fileSingleSizeLimit: audioMaxSize    // 默认 30 M
            });
            uploader.addButton({
                id: '#filePickerBlock'
            });
            uploader.addButton({
               id: '#filePickerBtn',
               label: lang.uploadAddFile
            });

            setState('pedding');

            // 当有文件添加进来时执行，负责view的创建
            function addFile(file) {
                var $li = $('<li id="' + file.id + '">' +
                        '<p class="title">' + file.name + '</p>' +
                        '<p class="progress"><span></span></p>' +
                        '</li>'),

                    $btns = $('<div class="file-panel">' +
                        '<span class="cancel">' + lang.uploadDelete + '</span>' +
                        '<span class="rotateRight">' + lang.uploadTurnRight + '</span>' +
                        '<span class="rotateLeft">' + lang.uploadTurnLeft + '</span></div>').appendTo($li),
                    $prgress = $li.find('p.progress span'),
                    $wrap = $li.find('p.imgWrap'),
                    $info = $('<p class="error"></p>').hide().appendTo($li),

                    showError = function (code) {
                        switch (code) {
                            case 'exceed_size':
                                text = lang.errorExceedSize;
                                break;
                            case 'interrupt':
                                text = lang.errorInterrupt;
                                break;
                            case 'http':
                                text = lang.errorHttp;
                                break;
                            case 'not_allow_type':
                                text = lang.errorFileType;
                                break;
                            default:
                                text = lang.errorUploadRetry;
                                break;
                        }
                        $info.text(text).show();
                    };

                if (file.getStatus() === 'invalid') {
                    showError(file.statusText);
                } else {
                    percentages[ file.id ] = [ file.size, 0 ];
                    file.rotation = 0;

                    /* 检查文件格式 */
                    if (!file.ext || acceptExtensions.indexOf(file.ext.toLowerCase()) == -1) {
                        showError('not_allow_type');
                        uploader.removeFile(file);
                    }
                }

                file.on('statuschange', function (cur, prev) {
                    if (prev === 'progress') {
                        $prgress.hide().width(0);
                    } else if (prev === 'queued') {
                        $li.off('mouseenter mouseleave');
                        $btns.remove();
                    }
                    // 成功
                    if (cur === 'error' || cur === 'invalid') {
                        showError(file.statusText);
                        percentages[ file.id ][ 1 ] = 1;
                    } else if (cur === 'interrupt') {
                        showError('interrupt');
                    } else if (cur === 'queued') {
                        percentages[ file.id ][ 1 ] = 0;
                    } else if (cur === 'progress') {
                        $info.hide();
                        $prgress.css('display', 'block');
                    } else if (cur === 'complete') {
                    }

                    $li.removeClass('state-' + prev).addClass('state-' + cur);
                });

                $li.on('mouseenter', function () {
                    $btns.stop().animate({height: 30});
                });
                $li.on('mouseleave', function () {
                    $btns.stop().animate({height: 0});
                });

                $btns.on('click', 'span', function () {
                    var index = $(this).index(),
                        deg;

                    switch (index) {
                        case 0:
                            uploader.removeFile(file);
                            return;
                        case 1:
                            file.rotation += 90;
                            break;
                        case 2:
                            file.rotation -= 90;
                            break;
                    }

                    if (supportTransition) {
                        deg = 'rotate(' + file.rotation + 'deg)';
                        $wrap.css({
                            '-webkit-transform': deg,
                            '-mos-transform': deg,
                            '-o-transform': deg,
                            'transform': deg
                        });
                    } else {
                        $wrap.css('filter', 'progid:DXImageTransform.Microsoft.BasicImage(rotation=' + (~~((file.rotation / 90) % 4 + 4) % 4) + ')');
                    }

                });

                $li.insertBefore($filePickerBlock);
                // 隐藏继续添加控件，设置为单个文件上传
                $filePickerBlock.hide();
            }

            // 取消上传
            function cancelFile(file) {
                 var $li = $('#' + file.id);
                 var spans = $progress.children();
                 spans.eq(0).text('0%');
                 spans.eq(1).css('width', '0%');
                 $progress.css('display', 'none');
                 $('.statusBar').children('.info').css('display', 'inline-block');
                 $('.error').remove();
                 var upBtn = $('.uploadBtn');
                 upBtn.removeClass('state-paused disabled');
                 upBtn.addClass('state-ready');
                 upBtn.html(lang.uploadStart);
            }

            // 负责view的销毁
            function removeFile(file) {
                var $li = $('#' + file.id);
                delete percentages[ file.id ];
                updateTotalProgress();
                $li.off().find('.file-panel').off().end().remove();
                // 显示继续添加控件
                $filePickerBlock.show();
            }

            function updateTotalProgress() {
                var loaded = 0,
                    total = 0,
                    spans = $progress.children(),
                    percent;

                $.each(percentages, function (k, v) {
                    total += v[ 0 ];
                    loaded += v[ 0 ] * v[ 1 ];
                });

                percent = total ? loaded / total : 0;

                spans.eq(0).text(Math.round(percent * 100) + '%');
                spans.eq(1).css('width', Math.round(percent * 100) + '%');
                updateStatus();
            }

            function setState(val, files) {

                if (val != state) {

                    var stats = uploader.getStats();

                    $upload.removeClass('state-' + state);
                    $upload.addClass('state-' + val);

                    switch (val) {

                        /* 未选择文件 */
                        case 'pedding':
                            $queue.addClass('element-invisible');
                            $statusBar.addClass('element-invisible');
                            $placeHolder.removeClass('element-invisible');
                            $progress.hide(); $info.hide();
                            uploader.refresh();
                            break;

                        /* 可以开始上传 */
                        case 'ready':
                            $placeHolder.addClass('element-invisible');
                            $queue.removeClass('element-invisible');
                            $statusBar.removeClass('element-invisible');
                            $progress.hide(); $info.show();
                            $upload.text(lang.uploadStart);
                            uploader.refresh();
                            break;

                        /* 上传中 */
                        case 'uploading':
                            $progress.show(); $info.hide();
                            $upload.text(lang.uploadCancel);
                            break;

                        /* 暂停上传 */
                        case 'paused':
                            $progress.show(); $info.hide();
                            $upload.text(lang.uploadContinue);
                            break;

                        /* 取消上传 */
                        case 'cancel':
                            $placeHolder.addClass('element-invisible');
                            $queue.removeClass('element-invisible');
                            $statusBar.removeClass('element-invisible');
                            $progress.hide(); $info.show();
                            $upload.text(lang.uploadStart);
                            uploader.refresh();
                            break;

                        case 'confirm':
                            $progress.show(); $info.hide();
                            $upload.text(lang.uploadStart);

                            stats = uploader.getStats();
                            if (stats.successNum && !stats.uploadFailNum) {
                                setState('finish');
                                return;
                            }
                            break;

                        case 'finish':
                            $progress.hide(); $info.show();
                            if (stats.uploadFailNum) {
                                $upload.text(lang.uploadRetry);
                            } else {
                                $upload.text(lang.uploadStart);
                            }
                            break;
                    }

                    state = val;
                    updateStatus();

                }

                if (!_this.getQueueCount()) {
                    $upload.addClass('disabled')
                } else {
                    $upload.removeClass('disabled')
                }

            }

            function updateStatus() {
                var text = '', stats;

                if (state === 'ready') {
                    text = lang.updateStatusReady.replace('_', fileCount).replace('_KB', WebUploader.formatSize(fileSize));
                } else if (state === 'confirm') {
                    stats = uploader.getStats();
                    if (stats.uploadFailNum) {
                        text = lang.updateStatusConfirm.replace('_', stats.successNum).replace('_', stats.successNum);
                    }
                } else {
                    stats = uploader.getStats();
                    text = lang.updateStatusFinish.replace('_', fileCount).
                        replace('_KB', WebUploader.formatSize(fileSize)).
                        replace('_', stats.successNum);

                    if (stats.uploadFailNum) {
                        text += lang.updateStatusError.replace('_', stats.uploadFailNum);
                    }
                }

                $info.html(text);
            }

            uploader.on('fileQueued', function (file) {
                fileCount++;
                fileSize += file.size;

                if (fileCount === 1) {
                    $placeHolder.addClass('element-invisible');
                    $statusBar.show();
                }

                addFile(file);
            });

            uploader.on('fileDequeued', function (file) {
                fileCount--;
                fileSize -= file.size;

                removeFile(file);
                updateTotalProgress();
            });

            uploader.on('filesQueued', function (file) {
                if (!uploader.isInProgress() && (state == 'pedding' || state == 'finish' || state == 'confirm' || state == 'ready')) {
                    setState('ready');
                }
                updateTotalProgress();
            });

            uploader.on('all', function (type, files) {
                switch (type) {
                    case 'uploadFinished':
                        setState('confirm', files);
                        break;
                    case 'startUpload':
                        /* 添加额外的GET参数 */
                        var params = utils.serializeParam(editor.queryCommandValue('serverparam')) || '';
                        setState('uploading', files);
                        break;
                    case 'stopUpload':
                        setState('paused', files);
                        break;
                }
            });

            uploader.on('uploadBeforeSend', function (file, data, header) {
                //这里可以通过data对象添加POST参数
                header['X_Requested_With'] = 'XMLHttpRequest';

                // 上传token
                // var token = getUploadToken4UE();
                // if (token == null) {
                //     alert('获取上传token异常，请稍后再试~');
                //     return false;
                // }
                // data['token'] = token;
                // 文件key
                // data['key'] = keyPrefix + '/' + uuid();
            });

            uploader.on('uploadProgress', function (file, percentage) {

                var $li = $('#' + file.id),
                    $percent = $li.find('.progress span');

                $percent.css('width', percentage * 100 + '%');
                percentages[ file.id ][ 1 ] = percentage;
                updateTotalProgress();
            });

            uploader.on('uploadSuccess', function (file, ret) {

                var $file = $('#' + file.id);
                try {
                    var responseText = (ret._raw || ret),
                        json = utils.str2json(responseText);
                    if (json.state == 'SUCCESS') {
                        //_this.audioList.push(json);
                        _this.audioList[$file.index()] = json;   //按选择好的文件列表顺序存储
                        $file.append('<span class="success"></span>');
                        if($('#audio_title_2').val() == ''){
                            $('#audio_title_2').val(json.title);
                        }
                    } else {
                        $file.find('.error').text(json.state).show();
                    }
                } catch (e) {
                    $file.find('.error').text(lang.errorServerUpload).show();
                }
            });

            uploader.on('uploadError', function (file, code) {
            });
            uploader.on('error', function (code, file) {
                if (code == 'Q_TYPE_DENIED' || code == 'F_EXCEED_SIZE') {
                    addFile(file);
                }
            });
            uploader.on('uploadComplete', function (file, ret) {
            });

            $upload.on('click', function () {
                if ($(this).hasClass('disabled')) {
                    return false;
                }

                if (state === 'ready') {
                    uploader.upload();
                } else if (state === 'paused') {
                    uploader.upload();
                } else if (state === 'cancel') {
                    uploader.upload();
                } else if (state === 'uploading') {
                    // 调整为取消上传
                    var file = uploader.getFiles()[0];
                    uploader.stop(file);
                    cancelFile(file);
                    //setState('cancel');
                }
            });

            $upload.addClass('state-' + state);
            updateTotalProgress();
        },
        getQueueCount: function () {
            var file, i, status, readyFile = 0, files = this.uploader.getFiles();
            for (i = 0; file = files[i++]; ) {
                status = file.getStatus();
                if (status == 'queued' || status == 'uploading' || status == 'progress') readyFile++;
            }
            return readyFile;
        },
        destroy: function () {
            this.$wrap.remove();
        },
        getInsertList: function () {
            var i, data, list = [],
                prefix = editor.getOpt('audioUrlPrefix');
            for (i = 0; i < this.audioList.length; i++) {
                data = this.audioList[i];
                if(data == undefined){
                    continue;
                }
                //修改END
                list.push({
                    src: prefix + data.url,
                    key: + new Date().getTime()   // 以时间戳作为音频控件父div的id
                });
            }
            return list;
        }
    };

})();
