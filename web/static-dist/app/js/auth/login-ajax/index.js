webpackJsonp(["app/js/auth/login-ajax/index"],{"230971c9cdccda257425":function(a,r,n){"use strict";var e=$("#login-ajax-form"),o=$(".js-submit-login-ajax"),i=e.validate({rules:{_username:{required:!0},_password:{required:!0}}});o.click(function(a){i.form()&&$.post(e.attr("action"),e.serialize(),function(a){o.button("loading"),window.location.reload()},"json").error(function(a,r,n){var o=jQuery.parseJSON(a.responseText);e.find(".alert-danger").html(Translator.trans(o.message)).show()})})}},["230971c9cdccda257425"]);