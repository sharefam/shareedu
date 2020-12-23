!function(e){function t(a,n){if(r[a])return r[a].exports;var i={i:a,l:!1,exports:{}};return 0!=n&&(r[a]=i),e[a].call(i.exports,i,i.exports,t),i.l=!0,i.exports}var r={};t.m=e,t.c=r,t.d=function(e,r,a){t.o(e,r)||Object.defineProperty(e,r,{configurable:!1,enumerable:!0,get:a})},t.n=function(e){var r=e&&e.__esModule?function(){return e.default}:function(){return e};return t.d(r,"a",r),r},t.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},t.p="/static-dist/",t(t.s="6c0327642d8a82b31184")}({"00b3162ee3b4c529813c":function(e,t,r){"use strict";function a(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}Object.defineProperty(t,"__esModule",{value:!0});var n=function(){function e(e,t){for(var r=0;r<t.length;r++){var a=t[r];a.enumerable=a.enumerable||!1,a.configurable=!0,"value"in a&&(a.writable=!0),Object.defineProperty(e,a.key,a)}}return function(t,r,a){return r&&e(t.prototype,r),a&&e(t,a),t}}(),i=function(){function e(t){a(this,e),this.$element=t,this.$courseSetType=this.$element.find(".js-courseSetType"),this.$currentCourseSetType=this.$element.find(".js-courseSetType.active"),this.init(),this.initExpiryMode(),this.checkBoxChange()}return n(e,[{key:"init",value:function(){var e=this;this.validator=this.$element.validate({currentDom:"#courseset-create-btn",rules:{title:{byte_maxlength:200,required:{depends:function(){return $(this).val($.trim($(this).val())),!0}},course_title:!0},orgCode:{required:!0}},messages:{title:{required:Translator.trans("course_set.title_required_error_hint"),trim:Translator.trans("course_set.title_required_error_hint")}}}),this.initDatePicker("#expiryStartDate"),this.initDatePicker("#expiryEndDate"),this.initDatePicker("#deadline"),this.$courseSetType.click(function(t){e.$courseSetType.removeClass("active"),e.$currentCourseSetType=$(t.currentTarget).addClass("active"),$('input[name="type"]').val(e.$currentCourseSetType.data("type"));var r=$("#course_title");r.rules("remove"),"live"!=e.$currentCourseSetType.data("type")?(r.rules("add",{required:!0,trim:!0,course_title:!0}),$(".js-learn-mod").show()):(r.rules("add",{required:!0,trim:!0,open_live_course_title:!0}),$(".js-learn-mod").hide(),$('[value="freeMode"]').click())}),$("#courseset-create-btn").click(function(t){e.validator.form()&&e.$element.submit()})}},{key:"initDatePicker",value:function(e){var t=this,r=$(e);r.datetimepicker({format:"yyyy-mm-dd",language:"zh",minView:2,autoclose:!0,endDate:new Date(Date.now()+31536e7)}).on("hide",function(){t.validator.form()}),r.datetimepicker("setStartDate",new Date)}},{key:"initExpiryMode",value:function(){var e=$('[name="deadline"]'),t=$('[name="expiryDays"]'),r=$('[name="expiryStartDate"]'),a=$('[name="expiryEndDate"]'),n=$('[name="expiryMode"]:checked').val();switch(this.elementRemoveRules(e),this.elementRemoveRules(t),this.elementRemoveRules(r),this.elementRemoveRules(a),n){case"days":if("end_date"===$('[name="deadlineType"]:checked').val())return this.elementAddRules(e,this.getDeadlineEndDateRules()),void this.validator.form();this.elementAddRules(t,this.getExpiryDaysRules()),this.validator.form();break;case"date":this.elementAddRules(r,this.getExpiryStartDateRules()),this.elementAddRules(a,this.getExpiryEndDateRules()),this.validator.form()}}},{key:"checkBoxChange",value:function(){var e=this;$('input[name="deadlineType"]').on("change",function(t){"end_date"==$('input[name="deadlineType"]:checked').val()?($("#deadlineType-date").removeClass("hidden"),$("#deadlineType-days").addClass("hidden")):($("#deadlineType-date").addClass("hidden"),$("#deadlineType-days").removeClass("hidden")),e.initExpiryMode()}),$('input[name="expiryMode"]').on("change",function(t){"date"==$('input[name="expiryMode"]:checked').val()?($("#expiry-days").removeClass("hidden").addClass("hidden"),$("#expiry-date").removeClass("hidden")):"days"==$('input[name="expiryMode"]:checked').val()?($("#expiry-date").removeClass("hidden").addClass("hidden"),$("#expiry-days").removeClass("hidden"),$('input[name="deadlineType"][value="days"]').prop("checked",!0)):($("#expiry-date").removeClass("hidden").addClass("hidden"),$("#expiry-days").removeClass("hidden").addClass("hidden")),e.initExpiryMode()}),$('input[name="learnMode"]').on("change",function(e){"freeMode"==$('input[name="learnMode"]:checked').val()?($("#learnLockModeHelp").removeClass("hidden").addClass("hidden"),$("#learnFreeModeHelp").removeClass("hidden")):($("#learnFreeModeHelp").removeClass("hidden").addClass("hidden"),$("#learnLockModeHelp").removeClass("hidden"))})}},{key:"getExpiryEndDateRules",value:function(){return{required:!0,date:!0,after_date:"#expiryStartDate",messages:{required:Translator.trans("course.manage.expiry_end_date_error_hint")}}}},{key:"getExpiryStartDateRules",value:function(){return{required:!0,date:!0,after_now_date:!0,before_date:"#expiryEndDate",messages:{required:Translator.trans("course.manage.expiry_start_date_error_hint")}}}},{key:"getExpiryDaysRules",value:function(){return{required:!0,positive_integer:!0,max_year:!0,messages:{required:Translator.trans("course.manage.expiry_days_error_hint")}}}},{key:"getDeadlineEndDateRules",value:function(){return{required:!0,date:!0,after_now_date:!0,messages:{required:Translator.trans("course.manage.deadline_end_date_error_hint")}}}},{key:"elementAddRules",value:function(e,t){e.rules("add",t)}},{key:"elementRemoveRules",value:function(e){e.rules("remove")}}]),e}();t.default=i},"6c0327642d8a82b31184":function(e,t,r){"use strict";var a=r("00b3162ee3b4c529813c");new(function(e){return e&&e.__esModule?e:{default:e}}(a).default)($("#courseset-create-form")),$("#user-orgCode").length>0&&new window.$.CheckTreeviewInput({$elem:$("#user-orgCode"),selectType:"single"})}});