import notify  from 'common/notify';

var $form = $('#user-roles-form'),
  isTeacher = $form.find('input[value=ROLE_TEACHER]').prop('checked'),
  currentUser = $form.data('currentuser'),
  editUser = $form.data('edituser');

if (currentUser == editUser) {
  $form.find('input[value=ROLE_SUPER_ADMIN]').attr('disabled', 'disabled');
}

$form.find('input[value=ROLE_USER]').on('change', function(){
  if ($(this).prop('checked') === false) {
    $(this).prop('checked', true);
    var user_name = $('#change-user-roles-btn').data('user') ;
    notify('info', Translator.trans('admin.user.roles.empty',{user:user_name}));
  }
});

$form.on('submit', function() {

  var roles = [];

  var $modal = $('#modal');

  $form.find('input[name="roles[]"]:checked').each(function(){
    roles.push($(this).val());
  });

  if ($.inArray('ROLE_USER', roles) < 0) {
    var user_name = $('#change-user-roles-btn').data('user') ;
    notify('danger', Translator.trans('admin.user.roles.empty',{user:user_name}));

    return false;
  }

  if (isTeacher && $.inArray('ROLE_TEACHER', roles) < 0) {
    if (!confirm(Translator.trans('admin.user.roles.confirm_message'))) {
      return false;
    }
  }

  $form.find('input[value=ROLE_SUPER_ADMIN]').removeAttr('disabled');
  $('#change-user-roles-btn').button('submiting').addClass('disabled');
  $.post($form.attr('action'), $form.serialize(), function(html) {
    if(html.status === 'false'){
      notify('danger', Translator.trans(html.message));
      return ;
    }
    $modal.modal('hide');
    notify('success',Translator.trans('admin.user.roles.create_success'));
    var $tr = $(html);
    $('#' + $tr.attr('id')).replaceWith($tr);
  }).error(function(){
    notify('danger',Translator.trans('admin.user.roles.create_error'));
  });

  return false;
});