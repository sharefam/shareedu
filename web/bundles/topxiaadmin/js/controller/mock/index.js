define(function(require, exports, module) {

  exports.run = function() {

    $('#tokenGeneratorBtn').click(
      function() {
        $('.distributorRegisterUrl').val('');
        $.post(
          $('#tokenGeneratorForm').attr('action'),
          $('#tokenGeneratorForm').serialize(),
          function(data) {
            $('.distributorRegisterUrl').val(document.origin + '/distributor_register?token=' + data.token);
          }
        );
      }
    );

    $('.mockSelector').change(
      function() {
        $('.tagContent').hide();
        displayedTabContent = $('.mockSelector').val();
        $('.tagContent.' + displayedTabContent).show();
      }
    );

    $('.sendedType').change(
      function() {
        if ($('.sendedType').val() != '') {
          $('.sendedData').val('');
          $.post(
            $('.sendedType').data('url'), { type: $('.sendedType').val() },
            function(data) {
              $('.sendedData').val(data);
            },
            'text'
          );
        }
      }
    );

    $('.sendBtn').click(
      function() {
        if ($('.sendedType') != '') {
          $('.sendResult').html('');
          $.post(
            $('.sendBtn').data('url'), { type: $('.sendedType').val() },
            function(data) {
              $('.sendResult').html(data.result);
            }
          );
        }
      }
    );

    $('.sendMarketingBtn').click(
      function() {
        $('.sendMarketingResult').html('');
        $.post(
          $('.sendMarketingBtn').data('url'), { 'url': $('.marketingUrl').val(), 'body': $('.sendedMarketingData').val() },
          function(data) {
            $('.sendMarketingResult').html(data.result);
          }
        );
      }
    );

    $('.marketingUrl').val('/callback/marketing?ac=orders.accept');

    $('.selectedType').change(
      function() {
        var sendMsg = $('.' + $('.selectedType').val()).html();
        $('.sendOtherMsg').val(sendMsg);
        $('.doc').val($('.' + $('.selectedType').val() + '_doc').html());
      }
    );

    $('.selectedType').change();

    $('.sendOtherBtn').click(
      function() {
        $('.result').html(Translator.trans('admin.mock.message'));
        $apiInfo = $.parseJSON($('.' + $('.selectedType').val() + '_apiInfo').html());
        $url = $('.sendOtherBtn').data('url-version-' + $apiInfo.apiVersion);

        $postData = $.parseJSON($('.sendOtherMsg').val());
        let copiedFields = ['apiMethod', 'apiUrl', 'apiAuthorized'];
        for (let index = 0; index < copiedFields.length; index++) {
          const element = copiedFields[index];
          $postData[element] = $apiInfo[element];
        }

        $.post(
          $url, { 'data': $postData },
          function(data) {
            $('.result').html(JSON.stringify(data));
          }
        );
      }
    );
  };

});
