// document.addEventListener('DOMContentLoaded', function () {
//     initAudio();
// }, false);

window.onload = function(){
    initAudio();
}

function initAudio(){
    // 设置音频文件名显示宽度
    var element = document.querySelector('.audio-right');
    if (element == undefined) return;
    var maxWidth = window.getComputedStyle(element, null).width;
    document.querySelector('.audio-right p').style.maxWidth = maxWidth;

    // 初始化音频控制事件
    var audioList = document.querySelectorAll('.audio-wrapper');
    audioList.forEach(function(e,i){
        initAudioEvent(e);
    })
}


function initAudioEvent(e) {
    var audio = e.getElementsByTagName('audio')[0];
    var audioPlayer = e.querySelector('.audio-left img');
    // var src = audio.querySelector('source').getAttribute('src');
    // if(!urlCheck(src)){
    //     e.querySelector('.audio-title').innerHTML = '<b style="color:red;">audio load error!</b>';
    // }
    // // 添加音频自动播放功能
    // // PS：不完善，在chrome下会报错，原因看这里https://developers.google.com/web/updates/2017/09/autoplay-policy-changes
    // audio.addEventListener('canplaythrough', function (event) {
    //     var playPromise = audio.play();
    //     if (playPromise !== undefined) {
    //         playPromise.then(() => {
    //                 audioPlayer.src = './image/pause.png';
    //             })
    //             .catch(error => {
    //                 // Auto-play was prevented
    //                 // Show paused UI.
    //                 console.log(error.message)
    //             });
    //     }
    // });

    //设置音频总时间
    audio.onloadedmetadata = function(){
        e.querySelector('.audio-right .audio-length-total').innerText = transTime(audio.duration);
    }


    audio.addEventListener('error',function(){
        e.querySelector('.audio-title').innerHTML = '<b style="color:red;">audio load error!</b>';
    });

    // 监听音频播放时间并更新进度条
    audio.addEventListener('timeupdate', function () {
        updateProgress(e,audio);
    }, false);

    // 监听播放完成事件
    audio.addEventListener('ended', function () {
        audioEnded(e);
    }, false);

    // 点击播放/暂停图片时，控制音乐的播放与暂停
    audioPlayer.addEventListener('click', function () {
        // 改变播放/暂停图片
        if (audio.paused) {
            // 开始播放当前点击的音频
            audio.play();
            audioPlayer.src = audioPlayer.src.replace('play.png','pause.png');
        } else {
            audio.pause();
            audioPlayer.src = audioPlayer.src.replace('pause.png','play.png');
        }
    }, false);

    // 点击进度条跳到指定点播放
    // PS：此处不要用click，否则下面的拖动进度点事件有可能在此处触发，此时e.offsetX的值非常小，会导致进度条弹回开始处（简直不能忍！！）
    var progressBarBg = e.querySelector('.progress-bar-bg');
    progressBarBg.addEventListener('mousedown', function (event) {
        // 只有音乐开始播放后才可以调节，已经播放过但暂停了的也可以
        if (!audio.paused || audio.currentTime != 0) {
            var pgsWidth = parseFloat(window.getComputedStyle(progressBarBg, null).width.replace('px', ''));
            var rate = event.offsetX / pgsWidth;
            audio.currentTime = audio.duration * rate;
            updateProgress(e,audio);
        }
    }, false);

    // 拖动进度点调节进度
    dragProgressDotEvent(e,audio);
}

/**
 * 鼠标拖动进度点时可以调节进度
 * @param {DataView.audio-wrapper} e
 * @param {*} audio
 */
function dragProgressDotEvent(e,audio) {
    var dot = e.querySelector('.progress-dot');

    var position = {
        oriOffestLeft: 0, // 移动开始时进度条的点距离进度条的偏移值
        oriX: 0, // 移动开始时的x坐标
        maxLeft: 0, // 向左最大可拖动距离
        maxRight: 0 // 向右最大可拖动距离
    };
    var flag = false; // 标记是否拖动开始

    // 鼠标按下时
    dot.addEventListener('mousedown', down, false);
    dot.addEventListener('touchstart', down, false);

    // 开始拖动
    document.addEventListener('mousemove', move, false);
    document.addEventListener('touchmove', move, false);

    // 拖动结束
    document.addEventListener('mouseup', end, false);
    document.addEventListener('touchend', end, false);

    function down(event) {
        if (!audio.paused || audio.currentTime != 0) { // 只有音乐开始播放后才可以调节，已经播放过但暂停了的也可以
            flag = true;

            position.oriOffestLeft = dot.offsetLeft;
            position.oriX = event.touches ? event.touches[0].clientX : event.clientX; // 要同时适配mousedown和touchstart事件
            position.maxLeft = position.oriOffestLeft; // 向左最大可拖动距离
            position.maxRight = e.querySelector('.progress-bar-bg').offsetWidth - position.oriOffestLeft; // 向右最大可拖动距离

            // 禁止默认事件（避免鼠标拖拽进度点的时候选中文字）
            if (event && event.preventDefault) {
                event.preventDefault();
            } else {
                event.returnValue = false;
            }

            // 禁止事件冒泡
            if (event && event.stopPropagation) {
                event.stopPropagation();
            } else {
                window.event.cancelBubble = true;
            }
        }
    }

    function move(event) {
        if (flag) {
            var clientX = event.touches ? event.touches[0].clientX : event.clientX; // 要同时适配mousemove和touchmove事件
            var length = clientX - position.oriX;
            if (length > position.maxRight) {
                length = position.maxRight;
            } else if (length < -position.maxLeft) {
                length = -position.maxLeft;
            }
            var progressBarBg = e.querySelector('.progress-bar-bg');
            var pgsWidth = parseFloat(window.getComputedStyle(progressBarBg, null).width.replace('px', ''));
            var rate = (position.oriOffestLeft + length) / pgsWidth;
            audio.currentTime = audio.duration * rate;
            updateProgress(e,audio);
        }
    }

    function end() {
        flag = false;
    }
}

/**
 * 更新进度条与当前播放时间
 * @param {object} audio - audio对象
 */
function updateProgress(e,audio) {
    var value = audio.currentTime / audio.duration;
    e.querySelector('.progress-bar').style.width = value * 100 + '%';
    e.querySelector('.progress-dot').style.left = value * 100 + '%';
    e.querySelector('.audio-length-current').innerText = transTime(audio.currentTime);
}

/**
 * 播放完成时把进度调回开始的位置
 */
function audioEnded(e) {
    e.querySelector('.progress-bar').style.width = 0;
    e.querySelector('.progress-dot').style.left = 0;
    e.querySelector('.audio-length-current').innerText = transTime(0);
    e.querySelector('.audio-left img').src = e.querySelector('.audio-left img').src.replace('pause.png','play.png');
}

/**
 * 音频播放时间换算
 * @param {number} value - 音频当前播放时间，单位秒
 */
function transTime(value) {
    var time = "";
    var h = parseInt(value / 3600);
    value %= 3600;
    var m = parseInt(value / 60);
    var s = parseInt(value % 60);
    if (h > 0) {
        time = formatTime(h + ":" + m + ":" + s);
    } else {
        time = formatTime(m + ":" + s);
    }

    return time;
}

/**
 * 格式化时间显示，补零对齐
 * eg：2:4  -->  02:04
 * @param {string} value - 形如 h:m:s 的字符串
 */
function formatTime(value) {
    var time = "";
    var s = value.split(':');
    var i = 0;
    for (; i < s.length - 1; i++) {
        time += s[i].length == 1 ? ("0" + s[i]) : s[i];
        time += ":";
    }
    time += s[i].length == 1 ? ("0" + s[i]) : s[i];

    return time;
}
