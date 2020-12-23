import OrgCheckTreeviewInput from '../org-select/Org-CheckTreeview-input';
window.$.publishCheckTreeviewInput = OrgCheckTreeviewInput;

class SourcePublish {
  constructor() {
    this.event();
    this.initTree();
  }

  event() {
    $('.js-tab-link').click(function () {
      if($('.js-showable-open').data('permission') ===1){
        $(this).parents('.js-publish-range').find('.active').removeClass('active');
        $(this).addClass('active');

        if($(this).hasClass('js-showable-open')){

          $('.js-publish-setting').addClass('hidden');
          $('[name=showable]').val(0);
        }else{
          $('.js-publish-setting').removeClass('hidden');
          $('[name=showable]').val(1);
        }

      }
    });
  }

  initTree() {
    if ($('#orgsSelect').length) {
      new window.$.publishCheckTreeviewInput({
        $elem: $('#orgsSelect'),
        disableNodeCheck: false,
        saveColumn: 'id',
        transportParent: true,
      });
    }

    if ($('#postsSelect').length) {
      new window.$.publishCheckTreeviewInput({
        $elem: $('#postsSelect'),
        disableNodeCheck: false,
        saveColumn: 'id',
        transportChildren: true,
      });
    }

    if ($('#userGroupsSelect').length) {
      new window.$.publishCheckTreeviewInput({
        $elem: $('#userGroupsSelect'),
        disableNodeCheck: false,
        saveColumn: 'id',
        transportChildren: true,
      });
    }

    if ($('#postRanksSelect').length) {
      new window.$.publishCheckTreeviewInput({
        $elem: $('#postRanksSelect'),
        disableNodeCheck: false,
        saveColumn: 'id',
        transportChildren: true,
      });
    }
  }
}

new SourcePublish();


