if (!navigator.userAgent.match(/(iPhone|iPod|Android|ios|iPad)/i)) {
  bindCardEvent('.js-card-content');
  $('body').on('mouseenter', '.js-user-card', function (event) {

    const _this = $(event.currentTarget);
    const userId = _this.data('userId');
    const loadingHtml = '<div class="card-body"><div class="card-loader"><span class="loader-inner"><span></span><span></span><span></span></span>' + Translator.trans('user.card_load_hint') + '</div>';

    const timer = setTimeout(function () {

      function callback(html) {
        _this.popover('destroy');

        setTimeout(function () {
          if ($('#user-card-' + userId).length == 0) {
            if ($('body').find('#user-card-store').length > 0) {
              $('#user-card-store').append(html);
            } else {
              $('body').append('<div id="user-card-store" class="hidden"></div>');
              $('#user-card-store').append(html);
            }
          }

          _this.popover({
            trigger: 'manual',
            placement: 'auto top',
            html: 'true',
            content: function () {
              return html;
            },
            template: '<div class="popover es-card"><div class="arrow"></div><div class="popover-content"></div></div>',
            container: 'body',
            animation: true
          });
          _this.popover('show');

          _this.data('popover', true);

          $('.popover').on('mouseleave', function () {
            _this.popover('hide');
          });
        }, 200);
      }

      if ($('#user-card-' + userId).length == 0 || !_this.data('popover')) {
        $.ajax({
          type: 'GET',
          url: _this.data('cardUrl'),
          dataType: 'html',
          beforeSend: function(){
            _this.popover({
              trigger: 'manual',
              placement: 'auto top',
              html: 'true',
              content: function () {
                return loadingHtml;
              },
              template: '<div class="popover es-card"><div class="arrow"></div><div class="popover-content"></div></div>',
              container: 'body',
              animation: true
            });
            // _this.popover("show");
          },
          success: callback
        });
      } else {
        const html = $('#user-card-' + userId).clone();
        callback(html);
        // _this.popover("show");
      }

      bindMsgBtn($('.es-card'), _this);


    }, 100);

    _this.data('timerId', timer);

  });

  $('body').on('mouseleave', '.js-user-card', function (event) {

    const _this = $(event.currentTarget);
    setTimeout(function () {

      if (!$('.popover:hover').length) {

        _this.popover('hide');

      }

    }, 100);

    clearTimeout(_this.data('timerId'));

  });
}

function bindCardEvent() {
  $('body').on('click', '.js-card-content .follow-btn', function () {
    const $btn = $(this);
    const loggedin = $btn.data('loggedin');
    if (loggedin == '1') {
      showUnfollowBtn($btn);
    }
    $.post($btn.data('url'));
  }).on('click', '.js-card-content .unfollow-btn', function () {
    const $btn = $(this);
    showFollowBtn($btn);
    $.post($btn.data('url'));
  });
}

function bindMsgBtn($card, self) {
  $card.on('click', '.direct-message-btn', function () {
    $(self).popover('hide');
  });
}

function showFollowBtn($btn) {
  $btn.hide();
  $btn.siblings('.follow-btn').show();
  let $actualCard = $('#user-card-' + $btn.closest('.js-card-content').data('userId'));
  $actualCard.find('.unfollow-btn').hide();
  $actualCard.find('.follow-btn').show();
}

function showUnfollowBtn($btn) {
  $btn.hide();
  $btn.siblings('.unfollow-btn').show();
  let $actualCard = $('#user-card-' + $btn.closest('.js-card-content').data('userId'));
  $actualCard.find('.follow-btn').hide();
  $actualCard.find('.unfollow-btn').show();
}
