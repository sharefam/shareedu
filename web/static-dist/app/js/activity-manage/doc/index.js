webpackJsonp(["app/js/activity-manage/doc/index"],{0:function(e,i){e.exports=jQuery},"96a7449142c5cc41e885":function(e,i,t){"use strict";function a(e,i){if(!(e instanceof i))throw new TypeError("Cannot call a class as a function")}Object.defineProperty(i,"__esModule",{value:!0});var n=function(){function e(e,i){for(var t=0;t<i.length;t++){var a=i[t];a.enumerable=a.enumerable||!1,a.configurable=!0,"value"in a&&(a.writable=!0),Object.defineProperty(e,a.key,a)}}return function(i,t,a){return t&&e(i.prototype,t),a&&e(i,a),i}}(),r=t("eca7a2561fa47d3f75f6"),o=function(e){return e&&e.__esModule?e:{default:e}}(r),u=t("f324dbdea53170d5000f"),s=function(){function e(){a(this,e),this.$mediaId=$('[name="mediaId"]'),this.init()}return n(e,[{key:"init",value:function(){(0,u.showChooserType)(this.$mediaId),this.initStep2Form(),this.initStep3Form(),this.initFileChooser()}},{key:"initStep2Form",value:function(){var e=$("#step2-form"),i=e.validate({rules:{title:{required:!0,maxlength:200,trim:!0,course_title:!0},mediaId:"required"},messages:{mediaId:{required:Translator.trans("activity.document_manage.media_error_hint")}}});e.data("validator",i)}},{key:"initStep3Form",value:function(){var e=$("#step3-form"),i=e.validate({onkeyup:!1,rules:{title:{required:!0,maxlength:50},finishDetail:{required:!0,positive_integer:!0,max:300,min:1}},messages:{finishDetail:{required:Translator.trans("activity.audio_manage.finish_detail_required_error_hint"),digits:Translator.trans("activity.audio_manage.finish_detail_digits_error_hint")}}});e.data("validator",i)}},{key:"initFileChooser",value:function(){var e=this;(new o.default).on("select",function(i){(0,u.chooserUiClose)(),e.$mediaId.val(i.id),$("#step2-form").valid(),$('[name="media"]').val(JSON.stringify(i))})}}]),e}();i.default=s},a8a1e3eec1a5a9fdafc9:function(e,i,t){"use strict";var a=t("96a7449142c5cc41e885");new(function(e){return e&&e.__esModule?e:{default:e}}(a).default)}},["a8a1e3eec1a5a9fdafc9"]);