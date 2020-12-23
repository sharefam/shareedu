import ajaxPage from '../../common/ajax-page';
import ajaxLink from '../../common/ajax-link';
import tabBlock from '../../common/tab-block';


export default class ajaxPageTask extends ajaxPage {
  constructor(props) {
    const NewProps = Object.assign(ajaxPageTask.getDefaultProps(), props);
    super(NewProps);
  }

  static getDefaultProps() {
    return {
      cycle: {
        beforeload: function() {
          this.noop();
          return true;
        },

        loading: function() {
          this.noop();
          $(this.element).find(this['pageContent']).animate({
            height: 'toggle',
            opacity: 'toggle'
          }, 'slow');

          $(this.element).append(this.getloadingAnimationDom());
        },

        done: function() {
          this.noop();
        },

        fail: function(res) {
          this.noop();
          res && res.msg && console.error(res.msg) || console.log( 'request fail' + res.status + 'or something wrong with your ajax');
        },

        success: function(res) {
          this.noop();
          const $element = this['_$wrapperTarget'];
          const pageContent = this['pageContent'];
          const $pageContent = $element.find(pageContent);
          let content = res && res.content || res;
          $(this.element).find('.js-loading-animation').remove();

          $pageContent.empty().append(content);
          $pageContent.animate({
            height: 'toggle',
            opacity: 'toggle'
          }, 'slow');

          $element.addClass('is-active');

          $('[data-toggle="tooltip"]').tooltip({
            container: 'body'
          });

          new tabBlock({ element: $element });

          const editorCon = $pageContent
            .find('.js-ckeditor').prop('id');

          let editor = CKEDITOR.replace( editorCon , {
            allowedContent: true,
            toolbar: 'Thread',
            filebrowserImageUploadUrl: $('#'+editorCon).data('imageUploadUrl')
          }); 

          $pageContent.find('.js-discuss-btn').on('click', function(){
            $(this).toggleClass('active');
            $(this).closest('.js-discuss-wrap').find('.js-editor').toggleClass('hidden');
          });

          const $form = $pageContent.find('.js-discuss-form');
          let validator = $form.validate({
            ajax: true,
            rules: {
              title: {
                required: true
              },
              content: {
                required: true
              }
            },

            messages: {
              title: {
                required: Translator.trans('study_center.my_task.title_required_messages')
              },

              content: {
                required: Translator.trans('study_center.my_task.content_required_messages')
              }
            },
          });

          $pageContent.find('.js-discuss-submit').on('click', function() {
            const $btn = $(this);
            if ($btn.hasClass('disabled')) {
              return;
            }
            editor.updateElement();

            if (validator.form()) {
              $btn.addClass('disabled');
              let url = '';
              $.post($form.prop('action'), $form.serialize(), function(result) {
                url = result.url;
              }).done(function() {

                $.post(url, function(html) {
                  $form.find('[name="title"]').val('');
                  CKEDITOR.instances[editor.name].setData('');
                  $pageContent.find('.js-discuss-btn').removeClass('active');
                  $pageContent.find('.js-editor').addClass('hidden');
                  $pageContent.find('.nav-pills .js-tab-link').siblings().removeClass('active');
                  $pageContent.find('.nav-pills .js-tab-link').eq(0).addClass('active');
                  $pageContent.find('.js-discuss-wrap').find('.js-tab-sec').html(html);
                  $btn.removeClass('disabled');
                });
              });

            }
          });

          let $ajaxTabBlockWrap = $pageContent.find('.js-discuss-wrap');

          for (let i = 0 ; i < $ajaxTabBlockWrap.length ; i++) {
            new ajaxLink({ element: $ajaxTabBlockWrap[i]});
          }

        },

        destroy: function(res) {
          $(this.element).find(this['triggerClass']).on('click', function() {
            $(this).parents('.js-page-wrapper').find('.js-page-content').animate({
              height: 'toggle',
              opacity: 'toggle'
            }, 'slow')
              .end().toggleClass('is-active');
          });
        }
      }
    };
  }

  getloadingAnimationDom() {
    return  `<div class="loading-line loading-line-bot js-loading-animation">
              <div></div> 
            </div>`;
  }
}

