define(function (require, exports, module) {
  var Notify = require('common/bootstrap-notify');
  require("jquery.bootstrap-datetimepicker");
  var Validator = require('bootstrap.validator');
  require('common/validator-rules').inject(Validator);
  var now = new Date();
  var Cookie = require('cookie');
  // var Swiper = require('swiper');

  exports.run = function () {
    showCloudAd();
    
    var $form = $('#operation-form');
    var ajaxData = function () {
      $form.on('click', '.js-data-btn', function (e) {
        $.post($('#operation-form').attr('action'), $form.serialize(), function (result) {
          $('.user-active-chart').html(result);
        }).fail(function (e) {

        });
      });
    };

    var changeTime = function () {
      $('.js-data-btn').removeClass('active');
      $(this).addClass('active');

      $("[name=startTime]").prop("value", $(this).attr("monthStart"));
      $("[name=endTime]").prop("value", $(this).attr("monthEnd"));
    }

    $('.js-today-data-popover').popover({
      html: true,
      trigger: 'hover',
      placement: 'bottom',
      content: $("#info-property-tips-html").html()
    });

    $('.js-data-popover').popover({
      html: true,
      trigger: 'hover',
      placement: 'bottom',
      content: $(".popover-content").html()
    });

    (function initDateTimpPicker() {
      $("[name=endTime]").datetimepicker({
        autoclose: true,
        format: 'yyyy-mm-dd',
        minView: 'month',
        endDate: now,
        startDate: $('#loginStartDate').attr("value")
      });
      $("[name=startTime]").datetimepicker({
        autoclose: true,
        format: 'yyyy-mm-dd',
        minView: 'month',
        endDate: now,
        startDate: $('#loginStartDate').attr("value")
      });
    })();

    $("#btn-month").on("click", function () {
      changeTime.call(this);
      ajaxData();
    });

    $("#btn-lastMonth").on("click", function () {
      changeTime.call(this);
      ajaxData();
    })

    $("#btn-lastThreeMonths").on("click", function () {
      changeTime.call(this);
      ajaxData();
    })

    $("#btn-chart-search").on("click", function () {
      $startTime = $("[name=startTime]").val();
      $endTime = $("[name=endTime]").val();
      if (new Date($endTime).getTime() - new Date($startTime).getTime() > 3600 * 24 * 365 * 1000) {
        Notify.danger('admin.default.chart_search_message');
      } else {
        ajaxData();
      }

    })
  };

  var showCloudAd = function () {
    var $cloudAd = $('#cloud-ad');
    $.get($cloudAd.data('url'), function (res) {
      if (!!res.error) {
        return;
      }

      var img = new Image();
      if (Cookie.get('cloud-ad') == res.image) {
        return;
      }
      img.src = res.image;
      if (img.complete) {
        showAdImage($cloudAd, img, res);
      } else {
        img.onload = function () {
          showAdImage($cloudAd, img, res);
          img.onload = null;
        };
      }
      ;
    });

    $cloudAd.on('hide.bs.modal', function () {
      Cookie.set('cloud-ad', $cloudAd.find('img').attr('src'), {expires: 360 * 10});
    })
  }

  var showAdImage = function ($cloudAd, img, res) {
    var $img = $(img);
    var $box = $cloudAd.find('.modal-dialog');
    var boxWidth = $box.width() ? $box.width() : $(window).width() - 20;
    var WindowHeight = $(window).height();

    var width = img.width;
    var height = img.height;
    var marginTop = 0;
    if ((width / height) >= (4 / 3)) {
      height = width > boxWidth ? height / (width / boxWidth) : height * (boxWidth / width);
      marginTop = (WindowHeight - height) / 2;
    } else {
      height = WindowHeight > 600 ? 600 : WindowHeight * 0.9;
      $img.height(height);
      $img.width('auto');
      marginTop = (WindowHeight - height) / 2;
    }

    $cloudAd.find('a').attr('href', res.urlOfImage).append($img).css({'margin-top': marginTop});
    $cloudAd.modal('show');
  }
  
  // if ($(".swiper-container").length > 0 && $('.swiper-container .swiper-wrapper').children().length > 1) {
  //   var noticeSwiper = new Swiper('.swiper-container', {
  //     speed: 300,
  //     loop: true,
  //     mode: 'vertical',
  //     autoplay: 5000,
  //     calculateHeight: true
  //   });
  // }
});
