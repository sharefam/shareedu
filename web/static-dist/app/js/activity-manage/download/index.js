webpackJsonp(["app/js/activity-manage/download/index"],{0:function(e,t){e.exports=jQuery},acb7a9eb3f93d9000adf:function(e,t,a){"use strict";var i=a("fbf0b6283b62b602eb6b");new(function(e){return e&&e.__esModule?e:{default:e}}(i).default)},fbf0b6283b62b602eb6b:function(e,t,a){"use strict";function i(e){return e&&e.__esModule?e:{default:e}}function n(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}Object.defineProperty(t,"__esModule",{value:!0});var l=function(){function e(e,t){for(var a=0;a<t.length;a++){var i=t[a];i.enumerable=i.enumerable||!1,i.configurable=!0,"value"in i&&(i.writable=!0),Object.defineProperty(e,i.key,i)}}return function(t,a,i){return a&&e(t.prototype,a),i&&e(t,i),t}}(),r=a("eca7a2561fa47d3f75f6"),s=i(r),o=a("b334fd7e4c5a19234db2"),d=(i(o),a("9181c6995ae8c5c94b7a")),m=a("f324dbdea53170d5000f"),c=function(){function e(){n(this,e),this.$form=$("#step2-form"),this.firstName=$("#title").val(),this.media=Object.create(null),this.materials=Object.create(null),this.initStep2Form(),this.bindEvent(),this.initFileChooser()}return l(e,[{key:"initStep2Form",value:function(){this.$form.validate({rules:{title:{required:!0,maxlength:200,trim:!0,course_title:!0},link:"url",materials:"required"},messages:{link:Translator.trans("activity.download_manage.link_error_hint"),materials:Translator.trans("activity.download_manage.materials_error_hint")}})}},{key:"bindEvent",value:function(){var e=this;this.$form.on("click",".js-btn-delete",function(t){return e.deleteItem(t)}),this.$form.on("click",".js-video-import",function(){return e.importLink()}),this.$form.on("click",".js-add-file-list",function(){return e.addFile()}),this.$form.on("blur","#title",function(t){return e.changeTitle(t)})}},{key:"deleteItem",value:function(e){var t=$(e.currentTarget).closest("li"),a=t.data("id"),i=$("#materials");this.materials=(0,d.isEmpty)(i.val())?Object.create(null):JSON.parse(i.val()),this.materials&&this.materials[a]&&(delete this.materials[a],i.val(JSON.stringify(this.materials))),t.siblings("li").length||i.val(""),t.remove()}},{key:"initFileChooser",value:function(){var e=this,t=function(t){$("#media").val(JSON.stringify(t)),(0,m.chooserUiOpen)(),$("#title").val(e.firstName),$(".js-current-file").text(t.name)};(new s.default).on("select",t)}},{key:"changeTitle",value:function(e){var t=$(e.currentTarget);this.firstName=t.val()}},{key:"importLink",value:function(){var e=$("#link"),t=$("#verifyLink");this.$form.data("validator").valid()&&e.val()?t.val(e.val()):(e.val(""),t.val("")),$(".js-current-file").text(t.val())}},{key:"addLink",value:function(){var e=$("#verifyLink").val(),t={source:"link",id:e,name:e,link:e,summary:$("#file-summary").val(),size:0};$("#media").val(JSON.stringify(t))}},{key:"addFile",value:function(){var e=$("#media"),t=$("#materials"),a=$(".js-success-redmine"),i=$(".js-danger-redmine");return $("#verifyLink").val()&&this.addLink(),this.media=(0,d.isEmpty)(e.val())?Object.create(null):JSON.parse(e.val()),this.materials=(0,d.isEmpty)(t.val())?Object.create(null):JSON.parse(t.val()),(0,d.isEmpty)(this.media)?void this.showTip(a,i,"activity.download_manage.materials_error_hint"):!(0,d.isEmpty)(this.materials)&&this.checkExisted()?void this.showTip(a,i,"activity.download_manage.materials_exist_error_hint"):(this.media.summary=$("#file-summary").val(),this.materials[this.media.id]=this.media,t.val(JSON.stringify(this.materials)),this.firstName||(this.firstName=this.media.name,$("#title").val(this.firstName)),this.showFile(),this.showTip(i,a,"activity.download_manage.materials_add_success_hint"),void($(".jq-validate-error:visible").length&&this.$form.data("validator").form()))}},{key:"checkExisted",value:function(){for(var e in this.materials){var t=this.materials[e],a=t.name===this.media.name,i=t.link&&t.link===this.media.id;if(a||i)return!0}return!1}},{key:"showFile",value:function(){var e="";e=this.media.link?'\n        <li class="download-item" data-id="'+this.media.link+'">\n          <a class="gray-primary" href="'+this.media.link+'" target="_blank">'+(this.media.summary?this.media.summary:this.media.name)+'<span class="glyphicon glyphicon-new-window text-muted text-sm mlm" title="'+Translator.trans("activity.download_manage.materials_delete_btn")+'"></span></a>\n          <a class="gray-primary phm btn-delete js-btn-delete" href="javascript:;" data-url="" data-toggle="tooltip" data-placement="top" title="'+Translator.trans("activity.download_manage.materials_delete_btn")+'"><i class="es-icon es-icon-delete"></i></a>\n        </li>\n      ':'\n        <li class="download-item" data-id="'+this.media.id+'">\n          <a class="gray-primary" href="/materiallib/'+this.media.id+'/download">'+this.media.name+'</a>\n          <a class="gray-primary phm btn-delete js-btn-delete" href="javascript:;" data-url="" data-toggle="tooltip" data-placement="top" title="'+Translator.trans("activity.download_manage.materials_delete_btn")+'"><i class="es-icon es-icon-delete"></i></a>\n        </li>\n      ',$("#material-list").append(e),$('[data-toggle="tooltip"]').tooltip()}},{key:"showTip",value:function(e,t,a){e.hide(),$(".js-current-file").text(""),$("#link").val(""),$("#verifyLink").val(""),$("#file-summary").val(""),$("#media").val(""),t.text(Translator.trans(a)).show(),setTimeout(function(){t.slideUp()},3e3)}}]),e}();t.default=c}},["acb7a9eb3f93d9000adf"]);