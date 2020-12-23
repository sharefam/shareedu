webpackJsonp(["app/js/auth/register/index"],{0:function(e,t){e.exports=jQuery},"6386db75dd507093b6de":function(e,t,a){"use strict";var i=a("9ffde76f31e1d8ca79f0");new(function(e){return e&&e.__esModule?e:{default:e}}(i).default)},"9ffde76f31e1d8ca79f0":function(e,t,a){"use strict";function i(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}Object.defineProperty(t,"__esModule",{value:!0});var n=function(){function e(e,t){for(var a=0;a<t.length;a++){var i=t[a];i.enumerable=i.enumerable||!1,i.configurable=!0,"value"in i&&(i.writable=!0),Object.defineProperty(e,i.key,i)}}return function(t,a,i){return a&&e(t.prototype,a),i&&e(t,i),t}}(),s=a("0282bb17fb83bfbfed9b"),r=function(e){return e&&e.__esModule?e:{default:e}}(s),l=function(){function e(){i(this,e),this.initDate(),this.initValidator(),this.inEventMobile(),this.initCaptchaCode(),this.initRegisterTypeRule(),this.initInviteCodeRule(),this.initUserTermsRule(),this.initMobileMsgVeriCodeSendBtn()}return n(e,[{key:"initValidator",value:function(){$("#register-form").validate({rules:{nickname:{required:!0,byte_minlength:4,byte_maxlength:18,nickname:!0,chinese_alphanumeric:!0,es_remote:{type:"get"}},password:{minlength:5,maxlength:20}}}),$.validator.addMethod("email_or_mobile_check",function(e,t,a){var i=/^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/,n=/^1\d{10}$/,s=!1,r=i.test(e),l=n.test(e);return l?($(".email_mobile_msg").removeClass("hidden"),$(".js-captcha").addClass("hidden")):($(".email_mobile_msg").addClass("hidden"),$(".js-captcha").removeClass("hidden")),(r||l)&&(s=!0),$.validator.messages.email_or_mobile_check=Translator.trans("请输入正确的手机／邮箱"),this.optional(t)||s},Translator.trans("validate.email_or_mobile_check.message"))}},{key:"inEventMobile",value:function(){var e=this;$("#register_emailOrMobile").blur(function(){var t=$("#register_emailOrMobile").val();e.emSmsCodeValidate(t)}),$("#register_mobile").blur(function(){var t=$("#register_mobile").val();e.emSmsCodeValidate(t)})}},{key:"initDate",value:function(){$(".date").datetimepicker({autoclose:!0,format:"yyyy-mm-dd",minView:"month",language:document.documentElement.lang})}},{key:"initCaptchaCode",value:function(){var e=$("#getcode_num");e.length>0&&(e.click(function(){$(this).attr("src",e.data("url")+"?"+Math.random())}),this.initCaptchaCodeRule())}},{key:"initRegisterTypeRule",value:function(){var e=$('input[name="email"]');e.length>0&&e.rules("add",{required:!0,email:!0,es_remote:{type:"get"},messages:{required:Translator.trans("validate.valid_email_input.message")}});var t=$('input[name="emailOrMobile"]');t.length>0&&t.rules("add",{required:!0,email_or_mobile_check:!0,es_remote:{type:"get",callback:function(e){e?$(".js-sms-send-btn").removeClass("disabled"):$(".js-sms-send-btn").addClass("disabled")}},messages:{required:Translator.trans("validate.phone_and_email_input.message")}});var a=$('input[name="verifiedMobile"]');a.length>0&&($(".email_mobile_msg").removeClass("hidden"),a.rules("add",{required:!0,phone:!0,es_remote:{type:"get",callback:function(e){e?$(".js-sms-send-btn").removeClass("disabled"):$(".js-sms-send-btn").addClass("disabled")}},messages:{required:Translator.trans("validate.phone.message")}}))}},{key:"initInviteCodeRule",value:function(){var e=$(".invitecode");e.length>0&&e.rules("add",{required:!1,reg_inviteCode:!0,es_remote:{type:"get"}})}},{key:"initUserTermsRule",value:function(){$("#user_terms").length&&$("#user_terms").rules("add",{required:!0,messages:{required:Translator.trans("validate.user_terms.message")}})}},{key:"initCaptchaCodeRule",value:function(){$('[name="captcha_code"]').rules("add",{required:!0,alphanumeric:!0,es_remote:{type:"get",callback:function(e){e||$("#getcode_num").attr("src",$("#getcode_num").data("url")+"?"+Math.random())}}})}},{key:"initSmsCodeRule",value:function(){$('[name="sms_code"]').rules("add",{required:!0,unsigned_integer:!0,rangelength:[6,6],es_remote:{type:"get"},messages:{rangelength:Translator.trans("validate.sms_code.message")}})}},{key:"initMobileMsgVeriCodeSendBtn",value:function(){$(".js-sms-send-btn").click(function(){var e="emailOrMobile";$("[name='verifiedMobile']").length>0&&(e="verifiedMobile"),new r.default({element:".js-sms-send",url:$(".js-sms-send").data("smsUrl"),smsType:"sms_registration",dataTo:e,captcha:!1,preSmsSend:function(){return!0},additionalAction:function(e){return"captchaRequired"==e&&($(".js-sms-send").click(),!0)}})})}},{key:"emSmsCodeValidate",value:function(e){/^1\d{10}$/.test(e)?(this.initSmsCodeRule(),$('[name="captcha_code"]').rules("remove")):(this.initCaptchaCodeRule(),$('[name="sms_code"]').rules("remove"))}}]),e}();t.default=l}},["6386db75dd507093b6de"]);