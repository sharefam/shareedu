import Swiper from 'swiper';
import tabBlock from '../common/tab-block';
import ajaxLink from '../common/ajax-link';

echo.init();


var actSwiper = new Swiper('#home-act-swiper', {
  autoplay: 7000,
  autoplayDisableOnInteraction: false,
  loop: true,
  calculateHeight: true,
  roundLengths: true,
  slidesPerView: 4,
  onFirstInit: function (swiper) {
    $('.js-home-act-swiper .swiper-slide').removeClass('swiper-hidden');
  }
});

$('.js-home-act-swiper .js-swiper-l-btn').on('click', function() {
  actSwiper.swipePrev();
});
$('.js-home-act-swiper .js-swiper-r-btn').on('click', function() {
  actSwiper.swipeNext();
});

var actSwiper1 = new Swiper('#home-act-swiper-1', {
  autoplay: 7000,
  autoplayDisableOnInteraction: false,
  loop: true,
  calculateHeight: true,
  roundLengths: true,
  slidesPerView: 1,
  onFirstInit: function (swiper) {
    $('.js-home-act-swiper-1 .swiper-slide').removeClass('swiper-hidden');
  }
});

if ($('#m-home-act-swiper-1 .swiper-slide').length > 1) {
  const $swiper = $('.js-home-act-swiper-1');

  new Swiper('#m-home-act-swiper-1', {
    paginationClickable: true,
    autoplayDisableOnInteraction: false,
    calculateHeight: true,
    roundLengths: true,
    slidesPerView: 'auto',
  });
}

if ($('#m-home-plan-swiper-1 .swiper-slide').length > 1) {
  const $swiper = $('.js-home-plan-swiper-1');

  new Swiper('#m-home-plan-swiper-1', {
    paginationClickable: true,
    autoplayDisableOnInteraction: false,
    calculateHeight: true,
    roundLengths: true,
    slidesPerView: 'auto',
  });
}

if ($('.es-poster .swiper-slide').length > 1) {
  var topBannerSwiper = new Swiper('.es-poster.swiper-container', {
    pagination: '.swiper-pager',
    paginationClickable: true,
    autoplay: 5000,
    autoplayDisableOnInteraction: false,
    loop: true,
    calculateHeight: true,
    roundLengths: true,
    onFirstInit: function (swiper) {
      $('.es-poster .swiper-slide').removeClass('swiper-hidden');
    }
  });

  $('.es-poster .js-swiper-l-btn').on('click', function() {
    topBannerSwiper.swipePrev();
  });
  $('.es-poster .js-swiper-r-btn').on('click', function() {
    topBannerSwiper.swipeNext();
  });
}

new Swiper('#home-top-swiper', {
  autoplay: 9000,
  autoplayDisableOnInteraction: false,
  loop: true,
  calculateHeight: true,
  roundLengths: true,
});

new Swiper('#home-mes', {
  autoplay: 11000,
  autoplayDisableOnInteraction: false,
  mode: 'vertical',
  loop: true,
  calculateHeight: true,
  roundLengths: true,
});

const timeLink = new ajaxLink({
  element: '.js-search-wrap',
  sec: '.js-tab-sec.is-active',
  link: '.js-tab-ajax-link',
  cb: function(e) {
    const $elem = $(this.element);
    const $currentTarget = $(e.currentTarget);
    const index = $currentTarget.index();
    $(rankListTab.element).find(rankListTab.link + '.active').data('index', index);
  }
});
new tabBlock({ element: '.js-common-classroom', mode: 'hover', cb: () => {echo.render();}});

new tabBlock({ element: '.js-common-course', mode: 'hover', cb: () => {echo.render();}});

new tabBlock({ element: '.js-home-department', mode: 'hover', cb: () => {echo.render();}});

new tabBlock({ element: '.js-common-project-plan', mode: 'hover', cb: () => {echo.render();}});

const rankListTab = new tabBlock({ element: '.js-ranking-list', mode: 'hover', cb: function(e) {
  const $elem = $(this.element);
  const $currentTarget = $(e.currentTarget);
  const index = ($currentTarget).index();
  const $timeLink  = $(timeLink.element).find(timeLink.link);

  if (index == 0) {
    $timeLink.children().each(function(index, item) {
      $(this).data('url', $(this).data('user'));
    });
  } else {
    $timeLink.children().each(function(index, item) {
      $(this).data('url', $(this).data('post'));
    });
  }

  const timeIndex = $currentTarget.data('index');
  $timeLink.removeClass('active')
    .eq(timeIndex).addClass('active');
}});



$('.nav.nav-tabs').unbind();

$('.js-tab-sec').on('mouseover', '.es-icon-more', function () {
  $(this).popover('show');
}).on('mouseleave', '.es-icon-more', function () {
  $(this).popover('destroy');
});