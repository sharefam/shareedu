webpackJsonp(["app/js/settings/security/index"],{0:function(e,t){e.exports=jQuery},"694afbe9a63ec6cdf212":function(e,t,n){"use strict";var a=n("b334fd7e4c5a19234db2"),s=function(e){return e&&e.__esModule?e:{default:e}}(a);$("#send-verify-email").click(function(){var e=$(this);$.post(e.data("url")).done(function(t){$("#modal").html(t).modal("show"),e.button("reset")}).fail(function(t){e.button("reset"),(0,s.default)("danger",Translator.trans(t.responseJSON.message))})})}},["694afbe9a63ec6cdf212"]);