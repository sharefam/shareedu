webpackJsonp(["app/js/classroom-manage/set-info/index"],{"5d31da2441e6b75d3a07":function(e,a,r){"use strict";Object.defineProperty(a,"__esModule",{value:!0});a.default=function(){""!=$("#create-classroom").val()&&(1==$("#showable-open").data("showable")?($("#showable-open").attr("checked","checked"),1==$("#buyable-open").data("buyable")?$("#buyable-open").attr("checked","checked"):$("#buyable-close").attr("checked","checked")):($("#showable-close").attr("checked","checked"),1==$("#buyable-open").data("buyable")?$("#buyable-open").attr("checked","checked"):$("#buyable-close").attr("checked","checked"),$("#buyable").attr("hidden","hidden"))),$("#showable-close").click(function(){$("#buyable").attr("hidden","hidden")}),$("#showable-open").click(function(){$("#buyable").removeAttr("hidden")}),$("#classroom_tags").select2({ajax:{url:app.arguments.tagMatchUrl+"#",dataType:"json",quietMillis:100,data:function(e,a){return{q:e,page_limit:10}},results:function(e){var a=[];return $.each(e,function(e,r){a.push({id:r.name,name:r.name})}),{results:a}}},initSelection:function(e,a){var r=[];$(e.val().split(",")).each(function(){r.push({id:this,name:this})}),a(r)},formatSelection:function(e){return e.name},formatResult:function(e){return e.name},width:"off",multiple:!0,maximumSelectionSize:20,placeholder:Translator.trans("classroom.manage.tag_required_hint"),createSearchChoice:function(){return null}})}()},"6dcdc9cf4a6380393d66":function(e,a,r){"use strict";function t(e){switch($("[name='expiryValue']").val()||$("[name='expiryValue']").val($("[name='expiryValue']").data(e)),s($("[name='expiryValue']")),e){case"days":$('[name="expiryValue"]').datetimepicker("remove"),$(".expiry-value-js .controls > span").removeClass("hidden"),o($('[name="expiryValue"]'),n()),i.form();break;case"date":if(void 0!==$("#classroom_expiryValue").attr("readonly"))return!1;$(".expiry-value-js .controls > span").addClass("hidden"),$("#classroom_expiryValue").datetimepicker({language:document.documentElement.lang,autoclose:!0,format:"yyyy-mm-dd",minView:"month",endDate:new Date(Date.now()+31536e7)}),$("#classroom_expiryValue").datetimepicker("setStartDate",new Date),o($('[name="expiryValue"]'),l()),i.form()}}function n(){return{required:!0,digits:!0,min:1,max:1e4,messages:{required:Translator.trans("classroom.manage.expiry_mode_days_error_hint")}}}function l(){return{required:!0,date:!0,after_now_date:!0,messages:{required:Translator.trans("classroom.manage.expiry_mode_date_error_hint")}}}function o(e,a){e.rules("add",a)}function s(e){e.rules("remove")}r("5d31da2441e6b75d3a07"),function(){CKEDITOR.replace("about",{allowedContent:!0,toolbar:"Detail",fileSingleSizeLimit:app.fileSingleSizeLimit,filebrowserImageUploadUrl:$("#about").data("imageUploadUrl"),filebrowserFlashUploadUrl:$("#about").data("flashUploadUrl")}),$('[name="categoryId"]').select2({treeview:!0,dropdownAutoWidth:!0,treeviewInitState:"collapsed",placeholderOption:"first"})}();var i=function(){return $("#classroom-set-form").validate({rules:{title:{required:!0},publishOrg:{publish_org_check:!0},accessOrg:{access_org_check:!0},days:{min:1,max:9999,digits:!0}},messages:{accessOrg:{access_org_check:Translator.trans("source.source_publish.select_org")}}})}();t($("[name=expiryMode]:checked").val()),$("[name='expiryMode']").change(function(){if("published"===app.arguments.classroomStatus)return!1;var e=$("[name='expiryValue']").val();if(e&&(e.match("-")?$("[name='expiryValue']").data("date",$("[name='expiryValue']").val()):$("[name='expiryValue']").data("days",$("[name='expiryValue']").val()),$("[name='expiryValue']").val("")),"forever"==$(this).val())$(".expiry-value-js").addClass("hidden");else{$(".expiry-value-js").removeClass("hidden");var a=$(".expiry-value-js > .controls > .help-block");a.text(a.data($(this).val()))}t($(this).val())}),jQuery.validator.addMethod("publish_org_check",function(){var e=$("[name = showable]").val(),a=$("[name = publishOrg]").val();return 0==e||a.length>0&&1==e},Translator.trans("source.source_publish.select_org")),jQuery.validator.addMethod("access_org_check",function(){var e=$("[name = conditionalAccess]").val(),a=$("[name = accessOrg]").val();return 0==e||a.length>0&&1==e},Translator.trans("source.source_publish.select_org")),new window.$.CheckTreeviewInput({$elem:$("#user-orgCode"),selectType:"single"})}},["6dcdc9cf4a6380393d66"]);