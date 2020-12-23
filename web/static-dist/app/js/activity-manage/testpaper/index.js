webpackJsonp(["app/js/activity-manage/testpaper/index"],{"32a7b7e8267fd29cb9ed":function(e,t,i){"use strict";var n=i("8923d040717a9546fc7c");new(function(e){return e&&e.__esModule?e:{default:e}}(n).default)($("#iframe-content"))},"8923d040717a9546fc7c":function(e,t,i){"use strict";function n(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}Object.defineProperty(t,"__esModule",{value:!0});var a=function(){function e(e,t){for(var i=0;i<t.length;i++){var n=t[i];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(e,n.key,n)}}return function(t,i,n){return i&&e(t.prototype,i),n&&e(t,n),t}}(),r=i("3c398f87808202f19beb"),o=function(){function e(t){n(this,e),this.$element=t,this.$step2_form=this.$element.find("#step2-form"),this.$step3_form=this.$element.find("#step3-form"),this.$parentiframe=$(window.parent.document).find("#task-create-content-iframe"),this.scoreSlider=null,this._init()}return a(e,[{key:"_init",value:function(){(0,r.dateFormat)(),this.setValidateRule(),this.initEvent(),this.initStepForm2()}},{key:"initEvent",value:function(){var e=this;this.$element.find("#testpaper-media").on("change",function(t){return e.changeTestpaper(t)}),this.$element.find("input[name=doTimes]").on("change",function(t){return e.showRedoInterval(t)}),this.$element.find('input[name="testMode"]').on("change",function(t){return e.startTimeCheck(t)}),this.$element.find('input[name="length"]').on("blur",function(t){return e.changeEndTime(t)}),this.$element.find("#condition-select").on("change",function(t){return e.changeCondition(t)}),this.initSelectTestpaper(this.$element.find("#testpaper-media").find("option:selected"),$('[name="finishScore"]').val())}},{key:"setValidateRule",value:function(){$.validator.addMethod("arithmeticFloat",function(e,t){return this.optional(t)||/^[0-9]+(\.[0-9]?)?$/.test(e)},$.validator.format(Translator.trans("activity.testpaper_manage.arithmetic_float_error_hint"))),$.validator.addMethod("positiveInteger",function(e,t){return this.optional(t)||/^[1-9]\d*$/.test(e)},$.validator.format(Translator.trans("activity.testpaper_manage.positive_integer_error_hint")))}},{key:"initStepForm2",value:function(){var e=this.$step2_form.validate({onkeyup:!1,rules:{title:{required:!0,trim:!0,maxlength:200,course_title:!0},mediaId:{required:!0,digits:!0},length:{required:!0,digits:!0},startTime:{required:function(){return 1==$('[name="doTimes"]:checked').val()&&"realTime"==$('[name="testMode"]:checked').val()},DateAndTime:function(){return 1==$('[name="doTimes"]:checked').val()&&"realTime"==$('[name="testMode"]:checked').val()}},redoInterval:{required:function(){return 0==$('[name="doTimes"]:checked').val()},arithmeticFloat:!0,max:1e9}},messages:{mediaId:{required:Translator.trans("activity.testpaper_manage.media_error_hint")},redoInterval:{max:Translator.trans("activity.testpaper_manage.max_error_hint")}}});this.$step2_form.data("validator",e)}},{key:"initSelectTestpaper",value:function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"",i=e.val();if(""!=i){this.getItemsTable(e.closest("select").data("getTestpaperItems"),i);var n=e.data("score");""==t&&(t=Math.ceil(.6*n)),$("#score-single-input").val(t),$(".js-score-total").text(n),$('input[name="title"]').val()||$('input[name="title"]').val(e.text()),this.initScoreSlider(parseInt(t),parseInt(n))}else $("#questionItemShowDiv").hide()}},{key:"changeTestpaper",value:function(e){var t=$(e.currentTarget),i=t.find("option:selected");this.initSelectTestpaper(i)}},{key:"showRedoInterval",value:function(e){1==$(e.currentTarget).val()?($("#lesson-redo-interval-field").closest(".form-group").hide(),$(".starttime-check-div").show(),this.dateTimePicker()):($("#lesson-redo-interval-field").closest(".form-group").show(),$(".starttime-check-div").hide())}},{key:"startTimeCheck",value:function(e){"realTime"==$(e.currentTarget).val()?($(".starttime-input").removeClass("hidden"),this.dateTimePicker()):$(".starttime-input").addClass("hidden")}},{key:"changeEndTime",value:function(e){$('input[name="startTime"]:visible').val()}},{key:"changeCondition",value:function(e){"score"!=$(e.currentTarget).find("option:selected").val()?$(".js-score-form-group").addClass("hidden"):$(".js-score-form-group").removeClass("hidden")}},{key:"initScoreSlider",value:function(e,t){var i=document.getElementById("score-slider"),n={start:e,connect:[!0,!1],tooltips:[!0],step:1,range:{min:0,max:t}};this.scoreSlider?this.scoreSlider.updateOptions(n):(this.scoreSlider=noUiSlider.create(i,n),i.noUiSlider.on("update",function(e,i){$(".noUi-tooltip").text((e[i]/t*100).toFixed(0)+"%"),$(".js-score-tooltip").css("left",(e[i]/t*100).toFixed(0)+"%"),$(".js-passScore").text(parseInt(e[i])),$('input[name="finishScore"]').val(parseInt(e[i]))}));var a=Translator.trans("activity.testpaper_manage.pass_score_hint",{passScore:'<span class="js-passScore">'+e+"</span>"}),r='<div class="score-tooltip js-score-tooltip"><div class="tooltip top" role="tooltip" style="">\n      <div class="tooltip-arrow"></div>\n      <div class="tooltip-inner ">\n        '+a+"\n      </div>\n      </div></div>";$(".noUi-handle").append(r),$(".noUi-tooltip").text((e/t*100).toFixed(0)+"%"),$(".js-score-tooltip").css("left",(e/t*100).toFixed(0)+"%")}},{key:"getItemsTable",value:function(e,t){$.post(e,{testpaperId:t},function(e){$("#questionItemShowTable").html(e),$("#questionItemShowDiv").show()})}},{key:"dateTimePicker",value:function(){var e=this,t=new Date,i=$('input[name="startTime"]');!i.is(":visible")||""!=i.val()&&"0"!=i.val()||i.val(t.Format("yyyy-MM-dd hh:mm")),i.datetimepicker({autoclose:!0,format:"yyyy-mm-dd hh:ii",language:document.documentElement.lang,minView:"hour",endDate:new Date(Date.now()+31536e7)}).on("show",function(t){e.$parentiframe.height($("body").height()+240)}).on("hide",function(t){e.$step2_form.data("validator").form(),e.$parentiframe.height($("body").height())}).on("changeDate",function(e){e.date.valueOf()}),i.datetimepicker("setStartDate",t)}}]),e}();t.default=o}},["32a7b7e8267fd29cb9ed"]);