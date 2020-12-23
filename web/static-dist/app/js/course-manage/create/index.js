webpackJsonp(["app/js/course-manage/create/index"],{"34bc6a13647d2e759c8b":function(e,t,n){"use strict";function a(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}var i=function(){function e(e,t){for(var n=0;n<t.length;n++){var a=t[n];a.enumerable=a.enumerable||!1,a.configurable=!0,"value"in a&&(a.writable=!0),Object.defineProperty(e,a.key,a)}}return function(t,n,a){return n&&e(t.prototype,n),a&&e(t,a),t}}(),r=n("423d5c93d4f10f876e3b"),s=function(e){return e&&e.__esModule?e:{default:e}}(r);new(function(){function e(){a(this,e),this.validator=null,this.init()}return i(e,[{key:"init",value:function(){$('[data-toggle="popover"]').popover({html:!0}),this.initValidator(),this.initExpiryMode(),this.checkBoxChange()}},{key:"initValidator",value:function(){var e=this,t=$("#course-create-form");this.validator=t.validate({groups:{date:"expiryStartDate expiryEndDate"},rules:{title:{required:!0,trim:!0}},messages:{title:Translator.trans("course.manage.title_required_error_hint")}}),$("#course-submit").click(function(n){e.validator.form()&&($(n.currentTarget).button("loading"),t.submit())}),this.initDatePicker("#expiryStartDate"),this.initDatePicker("#expiryEndDate"),this.initDatePicker("#deadline")}},{key:"isInitIntro",value:function(){1==$("#courses-list-table").find("tbody tr").length&&(new s.default).isSetCourseListCookies()}},{key:"checkBoxChange",value:function(){var e=this;$('input[name="deadlineType"]').on("change",function(t){"end_date"==$('input[name="deadlineType"]:checked').val()?($("#deadlineType-date").removeClass("hidden"),$("#deadlineType-days").addClass("hidden")):($("#deadlineType-date").addClass("hidden"),$("#deadlineType-days").removeClass("hidden")),e.initExpiryMode()}),$('input[name="expiryMode"]').on("change",function(t){"date"==$('input[name="expiryMode"]:checked').val()?($("#expiry-days").removeClass("hidden").addClass("hidden"),$("#expiry-date").removeClass("hidden")):"days"==$('input[name="expiryMode"]:checked').val()?($("#expiry-date").removeClass("hidden").addClass("hidden"),$("#expiry-days").removeClass("hidden"),$('input[name="deadlineType"][value="days"]').prop("checked",!0)):($("#expiry-date").removeClass("hidden").addClass("hidden"),$("#expiry-days").removeClass("hidden").addClass("hidden")),e.initExpiryMode()}),$('input[name="learnMode"]').on("change",function(e){"freeMode"==$('input[name="learnMode"]:checked').val()?($("#learnLockModeHelp").removeClass("hidden").addClass("hidden"),$("#learnFreeModeHelp").removeClass("hidden")):($("#learnFreeModeHelp").removeClass("hidden").addClass("hidden"),$("#learnLockModeHelp").removeClass("hidden"))})}},{key:"initDatePicker",value:function(e){var t=this,n=$(e);n.datetimepicker({format:"yyyy-mm-dd",language:document.documentElement.lang,minView:2,autoclose:!0,endDate:new Date(Date.now()+31536e7)}).on("hide",function(){t.validator.form()}),n.datetimepicker("setStartDate",new Date)}},{key:"initExpiryMode",value:function(){var e=$('[name="deadline"]'),t=$('[name="expiryDays"]'),n=$('[name="expiryStartDate"]'),a=$('[name="expiryEndDate"]'),i=$('[name="expiryMode"]:checked').val(),r=$('[name="deadlineType"]:checked');switch(this.elementRemoveRules(e),this.elementRemoveRules(t),this.elementRemoveRules(n),this.elementRemoveRules(a),i){case"days":if("end_date"===r.val())return this.elementAddRules(e,this.getDeadlineEndDateRules()),void this.validator.form();this.elementAddRules(t,this.getExpiryDaysRules()),this.validator.form();break;case"date":this.elementAddRules(n,this.getExpiryStartDateRules()),this.elementAddRules(a,this.getExpiryEndDateRules()),this.validator.form()}}},{key:"getExpiryEndDateRules",value:function(){return{required:!0,date:!0,after_date:"#expiryStartDate",messages:{required:Translator.trans("course.manage.expiry_end_date_error_hint")}}}},{key:"getExpiryStartDateRules",value:function(){return{required:!0,date:!0,after_now_date:!0,before_date:"#expiryEndDate",messages:{required:Translator.trans("course.manage.expiry_start_date_error_hint")}}}},{key:"getExpiryDaysRules",value:function(){return{required:!0,positive_integer:!0,max_year:!0,messages:{required:Translator.trans("course.manage.expiry_days_error_hint")}}}},{key:"getDeadlineEndDateRules",value:function(){return{required:!0,date:!0,after_now_date:!0,messages:{required:Translator.trans("course.manage.deadline_end_date_error_hint")}}}},{key:"elementAddRules",value:function(e,t){e.rules("add",t)}},{key:"elementRemoveRules",value:function(e){e.rules("remove")}}]),e}())},"423d5c93d4f10f876e3b":function(e,t,n){"use strict";function a(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}Object.defineProperty(t,"__esModule",{value:!0});var i=function(){function e(e,t){for(var n=0;n<t.length;n++){var a=t[n];a.enumerable=a.enumerable||!1,a.configurable=!0,"value"in a&&(a.writable=!0),Object.defineProperty(e,a.key,a)}}return function(t,n,a){return n&&e(t.prototype,n),a&&e(t,a),t}}();n("d5e8fa5f17ac5fe79c78");var r=n("fe53252afd7b6c35cb73"),s=function(e){return e&&e.__esModule?e:{default:e}}(r),o=function(){function e(){var t=this;a(this,e),this.intro=null,this.customClass="es-intro-help multistep",$("body").on("click",".js-skip",function(e){t.intro.exit()})}return i(e,[{key:"introType",value:function(){return this.isTaskCreatePage()?void this.initTaskCreatePageIntro():this.isCourseListPage()?void this.initCourseListPageIntro():void this.initNotTaskCreatePageIntro()}},{key:"isCourseListPage",value:function(){return!!$("#courses-list-table").length}},{key:"isTaskCreatePage",value:function(){return!!$("#step-3").length}},{key:"isInitTaskDetailIntro",value:function(){return $(".js-task-manage-item").attr("into-step-id","step-5"),!!$(".js-settings-list").length}},{key:"introStart",value:function(e){var t=this,n='<i class="es-icon es-icon-close01"></i>';this.intro=introJs(),e.length<2?(n=Translator.trans("intro.confirm_hint"),this.customClass="es-intro-help"):this.customClass="es-intro-help multistep",this.intro.setOptions({steps:e,skipLabel:n,nextLabel:Translator.trans("course_set.manage.next_label"),prevLabel:Translator.trans("course_set.manage.prev_label"),doneLabel:n,showBullets:!1,tooltipPosition:"auto",showStepNumbers:!1,exitOnEsc:!1,exitOnOverlayClick:!1,tooltipClass:this.customClass}),this.intro.start().onexit(function(){}).onchange(function(){t.intro._currentStep==t.intro._introItems.length-1?$(".introjs-nextbutton").before('<a class="introjs-button  done-button js-skip">'+Translator.trans("intro.confirm_hint")+"<a/>"):$(".js-skip").remove()})}},{key:"initTaskCreatePageIntro",value:function(){$(".js-task-manage-item:first .js-item-content").trigger("click"),store.get("COURSE_BASE_INTRO")||store.get("COURSE_TASK_INTRO")?store.get("COURSE_TASK_INTRO")||(store.set("COURSE_TASK_INTRO",!0),this.introStart(this.initTaskSteps())):(store.set("COURSE_BASE_INTRO",!0),store.set("COURSE_TASK_INTRO",!0),this.introStart(this.initAllSteps()))}},{key:"initTaskDetailIntro",value:function(e){store.get("COURSE_TASK_DETAIL_INTRO")||(store.set("COURSE_TASK_DETAIL_INTRO",!0),this.introStart(this.initTaskDetailSteps(e)))}},{key:"initNotTaskCreatePageIntro",value:function(){store.get("COURSE_BASE_INTRO")||(store.set("COURSE_BASE_INTRO",!0),this.introStart(this.initNotTaskPageSteps()))}},{key:"isSetCourseListCookies",value:function(){store.get("COURSE_LIST_INTRO")||s.default.set("COURSE_LIST_INTRO_COOKIE",!0)}},{key:"initCourseListPageIntro",value:function(){var e=this;2===$("#courses-list-table").find("tbody tr").length&&!store.get("COURSE_LIST_INTRO")&&s.default.get("COURSE_LIST_INTRO_COOKIE")&&(s.default.remove("COURSE_LIST_INTRO_COOKIE"),new Promise(function(e,t){setTimeout(function(){if(!$(".js-sidenav-course-menu").length)return void e();$(".js-sidenav-course-menu").slideUp(function(){e()})},100)}).then(function(){setTimeout(function(){e.initCourseListIntro(".js-sidenav")},100)}))}},{key:"initCourseListIntro",value:function(e){store.get("COURSE_LIST_INTRO")||(store.set("COURSE_LIST_INTRO",!0),this.introStart(this.initCourseListSteps(e)))}},{key:"initAllSteps",value:function(){var e=[{intro:Translator.trans("course_set.manage.upgrade_hint")},{element:"#step-1",intro:Translator.trans("course_set.manage.upgrade_step1_hint")},{element:"#step-2",intro:Translator.trans("course_set.manage.upgrade_step2_hint")},{element:"#step-3",intro:Translator.trans("course_set.manage.upgrade_step3_hint")}];return this.isInitTaskDetailIntro()&&(e.push({element:'[into-step-id="step-5"]',intro:Translator.trans("course_set.manage.upgrade_step5_hint")}),store.get("COURSE_TASK_DETAIL_INTRO")||store.set("COURSE_TASK_DETAIL_INTRO",!0)),e}},{key:"initNotTaskPageSteps",value:function(){return[{intro:Translator.trans("course_set.manage.upgrade_hint")},{element:"#step-1",intro:Translator.trans("course_set.manage.upgrade_step1_hint")},{element:"#step-2",intro:Translator.trans("course_set.manage.upgrade_step2_hint")}]}},{key:"initTaskSteps",value:function(){var e=[{element:"#step-3",intro:Translator.trans("course_set.manage.upgrade_step3_hint")}];return this.isInitTaskDetailIntro()&&(e.push({element:"#step-5",intro:Translator.trans("course_set.manage.upgrade_step5_hint"),position:"bottom"}),store.get("COURSE_TASK_DETAIL_INTRO")||store.set("COURSE_TASK_DETAIL_INTRO",!0)),e}},{key:"initTaskDetailSteps",value:function(e){return[{element:e,intro:Translator.trans("course_set.manage.activity_link_hint"),position:"bottom"}]}},{key:"initCourseListSteps",value:function(e){return[{element:e,intro:Translator.trans("course_set.manage.hint")}]}},{key:"initResetStep",value:function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"";return[{element:".js-intro-btn-group",intro:Translator.trans("course_set.manage.all_tutorial",{introBtnClassName:e}),position:"top"}]}}]),e}();t.default=o}},["34bc6a13647d2e759c8b"]);