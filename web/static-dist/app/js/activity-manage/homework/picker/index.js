webpackJsonp(["app/js/activity-manage/homework/picker/index"],{"0f294dfd80e43dae6fa3":function(e,t,n){"use strict";function i(e){return e&&e.__esModule?e:{default:e}}var o=n("b7747c79a9f58b90eaab"),a=i(o),r=n("1be2a74362f00ba903a0"),s=i(r),u=$("#question-picker-body",window.parent.document);new a.default(u,$("#step2-form")),new s.default($('[name="courseId"]',window.parent.document),$('[name="lessonId"]',window.parent.document))},"1be2a74362f00ba903a0":function(e,t,n){"use strict";function i(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}Object.defineProperty(t,"__esModule",{value:!0});var o=function(){function e(e,t){for(var n=0;n<t.length;n++){var i=t[n];i.enumerable=i.enumerable||!1,i.configurable=!0,"value"in i&&(i.writable=!0),Object.defineProperty(e,i.key,i)}}return function(t,n,i){return n&&e(t.prototype,n),i&&e(t,i),t}}(),a=function(){function e(t,n){i(this,e),this.select1=t,this.select2=n,this._initEvent()}return o(e,[{key:"_initEvent",value:function(){var e=this;this.select1.on("change",function(t){return e._selectChange(t)})}},{key:"_selectChange",value:function(e){var t=this.select1.data("url"),n=this.select1.val(),i=this;if(i.select2.text(""),0==n)return void this.select2.hide();$.post(t,{courseId:n},function(e){if(""!=e){var t='<option value="0">'+Translator.trans("site.choose_hint")+"</option>";$.each(e,function(e,n){t+='<option value="'+n.id+'">'+n.title+"</option>"}),i.select2.append(t),i.select2.show()}else i.select2.hide()})}}]),e}();t.default=a},"71e1df85d5928925f4b1":function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0});t.questionSubjectiveRemask=function(e){var t=!1,n="",i=$(".js-subjective-remask");if(e.find("tbody tr").each(function(){var e=$(this).data("type");"essay"==e&&(t=!0)}),t||0==e.find("tbody tr").length)return void i.html("");n="homework"==i.data("type")?Translator.trans("activity.homework_manage.objective_question_hint"):Translator.trans("activity.homework_manage.pass_objective_question_hint"),i.html(n).removeClass("hidden")}},b7747c79a9f58b90eaab:function(e,t,n){"use strict";function i(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}Object.defineProperty(t,"__esModule",{value:!0});var o=function(){function e(e,t){for(var n=0;n<t.length;n++){var i=t[n];i.enumerable=i.enumerable||!1,i.configurable=!0,"value"in i&&(i.writable=!0),Object.defineProperty(e,i.key,i)}}return function(t,n,i){return n&&e(t.prototype,n),i&&e(t,i),t}}(),a=n("71e1df85d5928925f4b1"),r=n("d7275d9e047b10241fd6"),s=function(e){return e&&e.__esModule?e:{default:e}}(r),u=function(){function e(t,n){i(this,e),this.batchSelectInCookie=new s.default(t,"courseSetQuestionIds"),this.$questionPickerBody=t,this.$questionPickerModal=this.$questionPickerBody.closest(".modal"),this.$questionAppendForm=n,this._initEvent()}return o(e,[{key:"_initEvent",value:function(){var e=this;this.$questionPickerBody.find('[data-role="search-btn"]').on("click",function(t){return e.searchQuestion(t)}),this.$questionPickerBody.find('[data-role="picked-item"]').on("click",function(t){return e.pickItem(t)}),this.$questionPickerBody.find('[data-role="preview-btn"]').on("click",function(t){return e.questionPreview(t)}),this.$questionPickerBody.find(".pagination a").on("click",function(t){return e.pagination(t)}),$('[data-role="batch-select-save"]',window.parent.document).on("click",function(t){return e.batchSelectSave(t)})}},{key:"pagination",value:function(e){var t=this,n=$(e.currentTarget);return $.get(n.attr("href"),function(e){t.$questionPickerModal.html(e),new s.default($("#question-picker-body",window.parent.document),"courseSetQuestionIds")}),!1}},{key:"searchQuestion",value:function(e){var t=this;e.preventDefault();var n=$(e.currentTarget),i=n.closest("form");$.get(i.attr("action"),i.serialize(),function(e){t.$questionPickerModal.html(e)})}},{key:"pickItem",value:function(e){var t=$(e.currentTarget),n=parseInt(t.data("replace")),i=t.data("questionId"),o=[];o.push(i),this.pickItemPost(t.data("url"),o,n)}},{key:"pickItemPost",value:function(e,t){var n=this,i=arguments.length>2&&void 0!==arguments[2]?arguments[2]:null;$.post(e,{questionIds:t},function(e){if(i)n.$questionAppendForm.find('tr[data-id="'+i+'"]').replaceWith(e),n.$questionAppendForm.find('tr[data-parent-id="'+i+'"]').remove();else{var t=n.$questionAppendForm.find("tbody:visible");t.length<=0&&(t=n.$questionAppendForm.find("tbody")),t.append(e).removeClass("hide")}n._refreshSeqs(),(0,a.questionSubjectiveRemask)(n.$questionAppendForm),n.batchSelectInCookie._clearCookie(),n.$questionPickerModal.modal("hide")})}},{key:"questionPreview",value:function(e){window.open($(e.currentTarget).data("url"),"_blank","directories=0,height=580,width=820,scrollbars=1,toolbar=0,status=0,menubar=0,location=0")}},{key:"batchSelectSave",value:function(e){var t=$(e.currentTarget),n=t.data("url"),i=this.batchSelectInCookie.itemIds;if($("#itemIds").length>0){if(i=$("#itemIds").val(),0==i.length)return void $(".js-choice-notice",window.parent.document).show();i=i.split(",")}this.pickItemPost(n,i,null)}},{key:"_refreshSeqs",value:function(){var e=1;this.$questionAppendForm.find("tbody tr").each(function(t,n){var i=$(n);i.hasClass("have-sub-questions")||(i.find("td.seq").html(e),e++)}),this.$questionAppendForm.find('[name="questionLength"]').val(e-1>0?e-1:null)}}]),e}();t.default=u}},["0f294dfd80e43dae6fa3"]);