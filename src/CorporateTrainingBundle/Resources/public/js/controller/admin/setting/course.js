define(function(require, exports, module) {
  
  var Validator = require('bootstrap.validator');
  require('common/validator-rules').inject(Validator);
  require('jquery.sortable');
  var Notify = require('common/bootstrap-notify');
  var WebUploader = require('edusoho.webuploader');
  
  exports.run = function() {
    
    var initChapterStatus = function(use_chapter_name){
      if(use_chapter_name == 1){
        $("#show_course_chapter_name").removeClass('hide');
      }else{
        $("#show_course_chapter_name").addClass('hide');
      }
    };
    
    $( "input[name='course_setting[custom_chapter_enabled]']").on('click',function(){
      initChapterStatus($( "input[name='course_setting[custom_chapter_enabled]']:checked").val());
    });
  
    var defaultCoursePicUploader = new WebUploader({
      element: '#default-course-picture-btn'
    });
  
    defaultCoursePicUploader.on('uploadSuccess', function(file, response ) {
      var url = $("#default-course-picture-btn").data("gotoUrl");
      Notify.success(Translator.trans('admin.setting.default_course_picture.upload_success'), 1);
      document.location.href = url;
    });
  
    var $systemCoursePictureClass = $('#system-course-picture-class');
  
    if ($("[name='course_avatar[coursePicture]']:checked").val() == 0) {
      $('#course-picture-class').hide();
    }
    if ($("[name='course_avatar[coursePicture]']:checked").val() == 1) {
      $systemCoursePictureClass.hide();
    }
  
    $("[name='course_avatar[coursePicture]']").on("click",function(){
      if($("[name='course_avatar[coursePicture]']:checked").val() == 0){
        $systemCoursePictureClass.show();
        $('#course-picture-class').hide();
      }
      if($("[name='course_avatar[coursePicture]']:checked").val() == 1){
        $systemCoursePictureClass.hide();
        $('#course-picture-class').show();
        defaultCoursePicUploader.enable();
      }
      $("input[name='course_setting[defaultCoursePicture]']").val($("[name='course_avatar[coursePicture]']:checked").val());

    });
    var $defaultCoursePicture = $("[name='course_avatar[defaultCoursePicture]']");
    $("[name='course_avatar[coursePicture]']").change(function(){
      $defaultCoursePicture.val($("[name='course_avatar[coursePicture]']:checked").val());
    });
    
  };
  
});
