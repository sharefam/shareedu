webpackJsonp(["app/js/question-manage/index"],{0:function(e,t){e.exports=jQuery},"1be2a74362f00ba903a0":function(e,t,n){"use strict";function a(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}Object.defineProperty(t,"__esModule",{value:!0});var o=function(){function e(e,t){for(var n=0;n<t.length;n++){var a=t[n];a.enumerable=a.enumerable||!1,a.configurable=!0,"value"in a&&(a.writable=!0),Object.defineProperty(e,a.key,a)}}return function(t,n,a){return n&&e(t.prototype,n),a&&e(t,a),t}}(),i=function(){function e(t,n){a(this,e),this.select1=t,this.select2=n,this._initEvent()}return o(e,[{key:"_initEvent",value:function(){var e=this;this.select1.on("change",function(t){return e._selectChange(t)})}},{key:"_selectChange",value:function(e){var t=this.select1.data("url"),n=this.select1.val(),a=this;if(a.select2.text(""),0==n)return void this.select2.hide();$.post(t,{courseId:n},function(e){if(""!=e){var t='<option value="0">'+Translator.trans("site.choose_hint")+"</option>";$.each(e,function(e,n){t+='<option value="'+n.id+'">'+n.title+"</option>"}),a.select2.append(t),a.select2.show()}else a.select2.hide()})}}]),e}();t.default=i},"4e3c732c4b4223e2d989":function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0});t.shortLongText=function(e){e.on("click",".short-text",function(){$(this).slideUp("fast").parents(".short-long-text").find(".long-text").slideDown("fast")}),e.on("click",".long-text",function(){$(this).slideUp("fast").parents(".short-long-text").find(".short-text").slideDown("fast")})}},d9c5a837e96540dfeeef:function(e,t,n){"use strict";function a(e){return e&&e.__esModule?e:{default:e}}var o=n("de585ca0d3c2d0205c51"),i=a(o),r=n("f637e828bcb096623369"),s=a(r),c=n("4e3c732c4b4223e2d989"),l=n("1be2a74362f00ba903a0"),u=a(l);new i.default($("#quiz-table-container")),new s.default($("#quiz-table-container"),"courseSetQuestionIds"),(0,c.shortLongText)($("#quiz-table-container")),new u.default($('[name="courseId"]'),$('[name="lessonId"]'))},f637e828bcb096623369:function(e,t,n){"use strict";function a(e){return e&&e.__esModule?e:{default:e}}function o(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}Object.defineProperty(t,"__esModule",{value:!0});var i=function(){function e(e,t){for(var n=0;n<t.length;n++){var a=t[n];a.enumerable=a.enumerable||!1,a.configurable=!0,"value"in a&&(a.writable=!0),Object.defineProperty(e,a.key,a)}}return function(t,n,a){return n&&e(t.prototype,n),a&&e(t,a),t}}(),r=n("b334fd7e4c5a19234db2"),s=a(r),c=n("d7275d9e047b10241fd6"),l=a(c),u=function(){function e(t,n,a){o(this,e),this.batchSelectInCookie=new l.default(t,n),this.$element=t,this.onSuccess=a,this.initEvent()}return i(e,[{key:"initEvent",value:function(){var e=this;this.$element.on("click",'[data-role="item-delete"]',function(t){return e._itemDelete(t)}),this.$element.on("click",'[data-role="batch-delete"]',function(t){return e._batchDelete(t)})}},{key:"_itemDelete",value:function(e){var t=$(e.currentTarget),n=t.data("name"),a=(t.data("message"),this);cd.confirm({title:Translator.trans("user.data.delete_testpaper_title"),content:Translator.trans("site.data.delete_name_hint",{name:n}),okText:Translator.trans("site.confirm"),cancelText:Translator.trans("site.close")}).on("ok",function(){$.post(t.data("url"),function(){$.isFunction(a.onSuccess)?a.onSuccess.call(a.$element):(t.closest("[data-role=item]").remove(),(0,s.default)("success",Translator.trans("site.delete_success_hint")),window.location.reload())})})}},{key:"_batchDelete",value:function(e){var t=this,n=$(e.currentTarget),a=n.data("name"),o=this.batchSelectInCookie.itemIds;if(0==o.length)return void(0,s.default)("danger",Translator.trans("site.data.uncheck_name_hint",{name:a}));cd.confirm({title:Translator.trans("user.data.delete_testpaper_title"),content:Translator.trans("site.data.delete_check_name_hint",{name:a}),okText:Translator.trans("site.confirm"),cancelText:Translator.trans("site.close")}).on("ok",function(){t.batchSelectInCookie._clearCookie(),$.post(n.data("url"),{ids:o},function(){window.location.reload()})})}}]),e}();t.default=u}},["d9c5a837e96540dfeeef"]);