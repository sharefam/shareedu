import ActivityEmitter from '../activity-emitter';

const emitter = new ActivityEmitter();
let url = $('.js-cloud-url').data('url');
(function (url) {
  window.QiQiuYun || (window.QiQiuYun = {});
  let xhr = new XMLHttpRequest();
  xhr.open('GET', url + '?' + ~~(Date.now() / 1000 / 60), false); // 可设置缓存时间。当前缓存时间为1分钟。
  xhr.send(null);
  let firstScriptTag = document.getElementsByTagName('script')[0];
  let script = document.createElement('script');
  script.text = xhr.responseText;
  firstScriptTag.parentNode.insertBefore(script, firstScriptTag);
})(url);

let $element = $('#activity-ppt-content');
let pptPlayer = $element.data('type') || 'slide';
const images = $element.data('imageInfo');
const totalPagesNumber = Number(images.length);
const isIOS = !!navigator.userAgent.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios终端

const iosFullScreen = () => {
  if (isIOS) {
    $('#task-content-iframe', parent.document).toggleClass('ios-ppt-full-screen');
  }
};

const initPptPlayer = () => {
  // 清空内容后切换
  $element.empty();
  if ('onlyImg' === $element.data('imgType') || 'slide' === pptPlayer) {
    initPPTNormalPlayer();
  } else if ('img' === pptPlayer) {
    initPPTImgPlayer();
  }
};

// 触发任务finish状态
const endFinishTip = (pageNumber) => {
  if ($element.data('finishType') === 'end') {
    if (totalPagesNumber === 1) {
      emitter.emit('finish', {page: 1});
    } else {
      const page = Number(pageNumber);
      if (totalPagesNumber === page) {
        emitter.emit('finish', {page});
      }
    }
  }
};

// 兼容老ppt，默认就是时候img-player，不用切换
const initPPTNormalPlayer = () => {
  const pptPlayer = new QiQiuYun.Player({
    id: 'activity-ppt-content',
    // 环境配置
    playServer: app.cloudPlayServer,
    sdkBaseUri: app.cloudSdkBaseUri,
    disableDataUpload: app.cloudDisableLogReport,
    disableSentry: app.cloudDisableLogReport,
    resNo: $element.data('resNo'),
    token: $element.data('token'),
    user: {
      id: $element.data('userId'),
      name: $element.data('userName')
    }
  });

  pptPlayer.on('slide.ready', (data) => {
    endFinishTip();
  });

  pptPlayer.on('slide.pagechanged', (data) => {
    endFinishTip(data.page);
  });

  pptPlayer.on('slide.requestFullscreen', () => {
    iosFullScreen();
  });

  // 监听老图片
  pptPlayer.on('img.ready', () => {
    endFinishTip();
  });

  pptPlayer.on('img.requestFullscreen', () => {
    iosFullScreen();
  });

  pptPlayer.on('img.poschanged', (data) => {
    endFinishTip(data.pageNum);
  });
};

const initPPTImgPlayer = () => {
  const imgPlayer = new QiQiuYun.Player({
    id: 'activity-ppt-content',
    source: {
      type: 'ppt',
      args: {
        player: 'ppt',
        images,
        type: 'img',
      }
    },
  });

  imgPlayer.on('img.ready', () => {
    endFinishTip();
  });

  imgPlayer.on('img.requestFullscreen', () => {
    iosFullScreen();
  });

  imgPlayer.on('img.poschanged', (data) => {
    endFinishTip(data.pageNum);
  });
};

initPptPlayer();
