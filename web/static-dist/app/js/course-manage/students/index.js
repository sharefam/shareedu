webpackJsonp(["app/js/course-manage/students/index"],{0:function(t,n){t.exports=jQuery},b5088243a1e717933967:function(t,n,e){"use strict";function s(t,n){if(!(t instanceof n))throw new TypeError("Cannot call a class as a function")}var o=function(){function t(t,n){for(var e=0;e<n.length;e++){var s=n[e];s.enumerable=s.enumerable||!1,s.configurable=!0,"value"in s&&(s.writable=!0),Object.defineProperty(t,s.key,s)}}return function(n,e,s){return e&&t(n.prototype,e),s&&t(n,s),n}}(),i=e("b334fd7e4c5a19234db2"),a=function(t){return t&&t.__esModule?t:{default:t}}(i);new(function(){function t(){s(this,t),this.initTooltips(),this.initDeleteActions(),this.initFollowActions(),this.initExpiryDayActions()}return o(t,[{key:"initTooltips",value:function(){$("#refund-coin-tips").popover({html:!0,trigger:"hover",placement:"left",content:$("#refund-coin-tips-html").html()})}},{key:"initDeleteActions",value:function(){$("body").on("click",".js-remove-student",function(t){confirm(Translator.trans("course.manage.student_delete_hint"))&&$.post($(t.target).data("url"),function(t){t.success?((0,a.default)("success",Translator.trans("site.delete_success_hint")),location.reload()):(0,a.default)("danger",Translator.trans("site.delete_fail_hint")+":"+t.message)})})}},{key:"initFollowActions",value:function(){$("#course-student-list").on("click",".follow-student-btn, .unfollow-student-btn",function(){var t=$(this);$.post(t.data("url"),function(){t.hide(),t.hasClass("follow-student-btn")?(t.parent().find(".unfollow-student-btn").show(),(0,a.default)("success",Translator.trans("user.follow_success_hint"))):(t.parent().find(".follow-student-btn").show(),(0,a.default)("success",Translator.trans("user.unfollow_success_hint")))})})}},{key:"initExpiryDayActions",value:function(){$(".js-expiry-days").on("click",function(){(0,a.default)("danger","只有按天数设置的学习有效期，才可手动增加有效期。")})}}]),t}())}},["b5088243a1e717933967"]);