webpackJsonp(["app/js/batch-upload/index"],{0:function(e,t){e.exports=jQuery},"582d7dd2d5834261808b":function(e,t,n){"use strict";var a=n("5899c7c7c1283bfb76ec");new(function(e){return e&&e.__esModule?e:{default:e}}(a).default)({element:"#uploader-container"}),$("#uploader-container").parents(".modal").on("hidden.bs.modal",function(){window.location.reload()})},"5899c7c7c1283bfb76ec":function(e,t,n){"use strict";function a(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}Object.defineProperty(t,"__esModule",{value:!0});var o=function(){function e(e,t){for(var n=0;n<t.length;n++){var a=t[n];a.enumerable=a.enumerable||!1,a.configurable=!0,"value"in a&&(a.writable=!0),Object.defineProperty(e,a.key,a)}}return function(t,n,a){return n&&e(t.prototype,n),a&&e(t,a),t}}(),r=n("b334fd7e4c5a19234db2"),i=function(e){return e&&e.__esModule?e:{default:e}}(r),l=n("4e68e437f5b716377a9d"),s=function(){function e(t){a(this,e),this.element=$(t.element),this.uploader=null,this.files=[],this.$sortable=$("#sortable-list"),this.init()}return o(e,[{key:"init",value:function(){this.initUploader(),this.initEvent()}},{key:"initUploader",value:function(){var e=this,t=this.element;this.uploader=new UploaderSDK({id:t.attr("id"),sdkBaseUri:app.cloudSdkBaseUri,disableDataUpload:app.cloudDisableLogReport,disableSentry:app.cloudDisableLogReport,initUrl:t.data("initUrl"),finishUrl:t.data("finishUrl"),accept:t.data("accept"),process:this.getUploadProcess(),ui:"batch",locale:document.documentElement.lang}),this.uploader.on("file.finish",function(t){e.files.push(t)}),this.uploader.on("error",function(e){var t={F_DUPLICATE:Translator.trans("uploader.file.exist")};e.message||(e.message=t[e.error]),(0,i.default)("danger",e.message)})}},{key:"initEvent",value:function(){var e=this;$(".js-upload-params").on("change",function(t){e.uploader.setProcess(e.getUploadProcess())}),$(".js-batch-create-lesson-btn").on("click",function(t){if(!e.files.length)return void(0,i.default)("danger",Translator.trans("uploader.select_one_file"));var n=$(t.currentTarget);n.button("loading"),e.files.map(function(t,a){var o=!1;a+1==e.files.length&&(o=!0),e.createLesson(n,t,o)})}),$('[data-toggle="popover"]').popover({html:!0})}},{key:"getUploadProcess",value:function(){var e=$(".js-upload-params").get().reduce(function(e,t){return e[$(t).attr("name")]=$(t).find("option:selected").val(),e},{}),t={video:e,document:{type:"html"}};return $("[name=support_mobile]").length>0&&(t.common={supportMobile:$("[name=support_mobile]").val()}),t}},{key:"createLesson",value:function(e,t,n){var a=this;$.ajax({type:"post",url:e.data("url"),async:!1,data:{fileId:t.id},success:function(e){e&&e.error?(0,i.default)("danger",e.error):a.$sortable.append(e.html)},error:function(e){(0,i.default)("danger",Translator.trans("uploader.status.error"))},complete:function(e){n&&((0,l.sortablelist)(a.$sortable),$("#modal").modal("hide"))}})}}]),e}();t.default=s}},["582d7dd2d5834261808b"]);