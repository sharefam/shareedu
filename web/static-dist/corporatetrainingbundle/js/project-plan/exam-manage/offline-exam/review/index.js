!function(t){function e(i,s){if(n[i])return n[i].exports;var a={i:i,l:!1,exports:{}};return 0!=s&&(n[i]=a),t[i].call(a.exports,a,a.exports,e),a.l=!0,a.exports}var n={};e.m=t,e.c=n,e.d=function(t,n,i){e.o(t,n)||Object.defineProperty(t,n,{configurable:!1,enumerable:!0,get:i})},e.n=function(t){var n=t&&t.__esModule?function(){return t.default}:function(){return t};return e.d(n,"a",n),n},e.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},e.p="/static-dist/",e(e.s="a43267c66691545cdb39")}({0:function(t,e){t.exports=jQuery},a25cd36d0cf21bc7df34:function(t,e,n){(function(){(function(){(function(){(function(){(function(){(function(){(function(){(function(){(function(){(function(){(function(){(function(){(function(){(function(){(function(){(function(){(function(){var t=!1;(function(){!function(e){"function"==typeof t&&t.amd?t(["jquery"],e):e(jQuery)}(function(t){function e(e,i,s){var i={content:{message:"object"==typeof i?i.message:i,title:i.title?i.title:"",icon:i.icon?i.icon:"",url:i.url?i.url:"#",target:i.target?i.target:"-"}};s=t.extend(!0,{},i,s),this.settings=t.extend(!0,{},n,s),this._defaults=n,"-"==this.settings.content.target&&(this.settings.content.target=this.settings.url_target),this.animations={start:"webkitAnimationStart oanimationstart MSAnimationStart animationstart",end:"webkitAnimationEnd oanimationend MSAnimationEnd animationend"},"number"==typeof this.settings.offset&&(this.settings.offset={x:this.settings.offset,y:this.settings.offset}),this.init()}var n={element:"body",position:null,type:"info",allow_dismiss:!0,newest_on_top:!1,showProgressbar:!1,placement:{from:"top",align:"right"},offset:20,spacing:10,z_index:1031,delay:5e3,timer:1e3,url_target:"_blank",mouse_over:null,animate:{enter:"animated fadeInDown",exit:"animated fadeOutUp"},onShow:null,onShown:null,onClose:null,onClosed:null,icon_type:"class",template:'<div data-notify="container" class="col-xs-11 col-sm-4 alert alert-{0}" role="alert"><button type="button" aria-hidden="true" class="close" data-notify="dismiss">&times;</button><span data-notify="icon"></span> <span data-notify="title">{1}</span> <span data-notify="message">{2}</span><div class="progress" data-notify="progressbar"><div class="progress-bar progress-bar-{0}" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div></div><a href="{3}" target="{4}" data-notify="url"></a></div>'};String.format=function(){for(var t=arguments[0],e=1;e<arguments.length;e++)t=t.replace(RegExp("\\{"+(e-1)+"\\}","gm"),arguments[e]);return t},t.extend(e.prototype,{init:function(){var t=this;this.buildNotify(),this.settings.content.icon&&this.setIcon(),"#"!=this.settings.content.url&&this.styleURL(),this.styleDismiss(),this.placement(),this.bind(),this.notify={$ele:this.$ele,update:function(e,n){var i={};"string"==typeof e?i[e]=n:i=e;for(var e in i)switch(e){case"type":this.$ele.removeClass("alert-"+t.settings.type),this.$ele.find('[data-notify="progressbar"] > .progress-bar').removeClass("progress-bar-"+t.settings.type),t.settings.type=i[e],this.$ele.addClass("alert-"+i[e]).find('[data-notify="progressbar"] > .progress-bar').addClass("progress-bar-"+i[e]);break;case"icon":var s=this.$ele.find('[data-notify="icon"]');"class"==t.settings.icon_type.toLowerCase()?s.removeClass(t.settings.content.icon).addClass(i[e]):(s.is("img")||s.find("img"),s.attr("src",i[e]));break;case"progress":var a=t.settings.delay-t.settings.delay*(i[e]/100);this.$ele.data("notify-delay",a),this.$ele.find('[data-notify="progressbar"] > div').attr("aria-valuenow",i[e]).css("width",i[e]+"%");break;case"url":this.$ele.find('[data-notify="url"]').attr("href",i[e]);break;case"target":this.$ele.find('[data-notify="url"]').attr("target",i[e]);break;default:this.$ele.find('[data-notify="'+e+'"]').html(i[e])}var o=this.$ele.outerHeight()+parseInt(t.settings.spacing)+parseInt(t.settings.offset.y);t.reposition(o)},close:function(){t.close()}}},buildNotify:function(){var e=this.settings.content;this.$ele=t(String.format(this.settings.template,this.settings.type,e.title,e.message,e.url,e.target)),this.$ele.attr("data-notify-position",this.settings.placement.from+"-"+this.settings.placement.align),this.settings.allow_dismiss||this.$ele.find('[data-notify="dismiss"]').css("display","none"),(this.settings.delay<=0&&!this.settings.showProgressbar||!this.settings.showProgressbar)&&this.$ele.find('[data-notify="progressbar"]').remove()},setIcon:function(){"class"==this.settings.icon_type.toLowerCase()?this.$ele.find('[data-notify="icon"]').addClass(this.settings.content.icon):this.$ele.find('[data-notify="icon"]').is("img")?this.$ele.find('[data-notify="icon"]').attr("src",this.settings.content.icon):this.$ele.find('[data-notify="icon"]').append('<img src="'+this.settings.content.icon+'" alt="Notify Icon" />')},styleDismiss:function(){this.$ele.find('[data-notify="dismiss"]').css({position:"absolute",right:"10px",top:"5px",zIndex:this.settings.z_index+2})},styleURL:function(){this.$ele.find('[data-notify="url"]').css({backgroundImage:"url(data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7)",height:"100%",left:"0px",position:"absolute",top:"0px",width:"100%",zIndex:this.settings.z_index+1})},placement:function(){var e=this,n=this.settings.offset.y,i={display:"inline-block",margin:"0px auto",position:this.settings.position?this.settings.position:"body"===this.settings.element?"fixed":"absolute",transition:"all .5s ease-in-out",zIndex:this.settings.z_index},s=!1,a=this.settings;switch(t('[data-notify-position="'+this.settings.placement.from+"-"+this.settings.placement.align+'"]:not([data-closing="true"])').each(function(){return n=Math.max(n,parseInt(t(this).css(a.placement.from))+parseInt(t(this).outerHeight())+parseInt(a.spacing))}),1==this.settings.newest_on_top&&(n=this.settings.offset.y),i[this.settings.placement.from]=n+"px",this.settings.placement.align){case"left":case"right":i[this.settings.placement.align]=this.settings.offset.x+"px";break;case"center":i.left=0,i.right=0}this.$ele.css(i).addClass(this.settings.animate.enter),t.each(Array("webkit-","moz-","o-","ms-",""),function(t,n){e.$ele[0].style[n+"AnimationIterationCount"]=1}),t(this.settings.element).append(this.$ele),1==this.settings.newest_on_top&&(n=parseInt(n)+parseInt(this.settings.spacing)+this.$ele.outerHeight(),this.reposition(n)),t.isFunction(e.settings.onShow)&&e.settings.onShow.call(this.$ele),this.$ele.one(this.animations.start,function(t){s=!0}).one(this.animations.end,function(n){t.isFunction(e.settings.onShown)&&e.settings.onShown.call(this)}),setTimeout(function(){s||t.isFunction(e.settings.onShown)&&e.settings.onShown.call(this)},600)},bind:function(){var e=this;if(this.$ele.find('[data-notify="dismiss"]').on("click",function(){e.close()}),this.$ele.mouseover(function(e){t(this).data("data-hover","true")}).mouseout(function(e){t(this).data("data-hover","false")}),this.$ele.data("data-hover","false"),this.settings.delay>0){e.$ele.data("notify-delay",e.settings.delay);var n=setInterval(function(){var t=parseInt(e.$ele.data("notify-delay"))-e.settings.timer;if("false"===e.$ele.data("data-hover")&&"pause"==e.settings.mouse_over||"pause"!=e.settings.mouse_over){var i=(e.settings.delay-t)/e.settings.delay*100;e.$ele.data("notify-delay",t),e.$ele.find('[data-notify="progressbar"] > div').attr("aria-valuenow",i).css("width",i+"%")}t<=-e.settings.timer&&(clearInterval(n),e.close())},e.settings.timer)}},close:function(){var e=this,n=parseInt(this.$ele.css(this.settings.placement.from)),i=!1;this.$ele.data("closing","true").addClass(this.settings.animate.exit),e.reposition(n),t.isFunction(e.settings.onClose)&&e.settings.onClose.call(this.$ele),this.$ele.one(this.animations.start,function(t){i=!0}).one(this.animations.end,function(n){t(this).remove(),t.isFunction(e.settings.onClosed)&&e.settings.onClosed.call(this)}),setTimeout(function(){i||(e.$ele.remove(),e.settings.onClosed&&e.settings.onClosed(e.$ele))},600)},reposition:function(e){var n=this,i='[data-notify-position="'+this.settings.placement.from+"-"+this.settings.placement.align+'"]:not([data-closing="true"])',s=this.$ele.nextAll(i);1==this.settings.newest_on_top&&(s=this.$ele.prevAll(i)),s.each(function(){t(this).css(n.settings.placement.from,e),e=parseInt(e)+parseInt(n.settings.spacing)+t(this).outerHeight()})}}),t.notify=function(t,n){return new e(this,t,n).notify},t.notifyDefaults=function(e){return n=t.extend(!0,{},n,e)},t.notifyClose=function(e){void 0===e||"all"==e?t("[data-notify]").find('[data-notify="dismiss"]').trigger("click"):t('[data-notify-position="'+e+'"]').find('[data-notify="dismiss"]').trigger("click")}})}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)},a43267c66691545cdb39:function(t,e,n){"use strict";function i(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}Object.defineProperty(e,"__esModule",{value:!0});var s=function(){function t(t,e){for(var n=0;n<e.length;n++){var i=e[n];i.enumerable=i.enumerable||!1,i.configurable=!0,"value"in i&&(i.writable=!0),Object.defineProperty(t,i.key,i)}}return function(e,n,i){return n&&t(e.prototype,n),i&&t(e,i),e}}(),a=n("b334fd7e4c5a19234db2"),o=function(t){return t&&t.__esModule?t:{default:t}}(a),r=function(){function t(){i(this,t),this.$form=$("#offline-exam-review-form"),this.$modal=this.$form.parents(".modal"),this.init()}return s(t,[{key:"init",value:function(){this.initValidator()}},{key:"initValidator",value:function(){var t=this;this.validator=this.$form.validate({rules:{score:{required:!0,inputLessThan:!0,arithmeticFloat:!0,max:500}},messages:{score:{required:Translator.trans("project_plan.exam-manage.offline_exam")}}}),$(".js-save-btn").on("click",function(e){t.validator.form()&&($(".js-save-btn").button("loading"),$.post(t.$form.prop("action"),t.$form.serialize(),function(t){t?((0,o.default)("success",Translator.trans("project_plan.exam-manage.review_success")),window.location.reload()):($(".js-save-btn").button("reset"),(0,o.default)("danger",Translator.trans("project_plan.save_error")))}).error(function(){$(".js-save-btn").button("reset"),(0,o.default)("danger",Translator.trans("project_plan.save_error"))}))})}}]),t}();e.default=r,new r,$.validator.addMethod("arithmeticFloat",function(t,e){return this.optional(e)||/^[0-9]+(\.[0-9]?)?$/.test(t)},$.validator.format(Translator.trans("activity.testpaper_manage.arithmetic_float_error_hint"))),jQuery.validator.addMethod("inputLessThan",function(){var t=!0;return $("#score").val()-$("#testPaper-score").text()>0&&(t=!1),t},"得分应该小于等于试卷满分")},b334fd7e4c5a19234db2:function(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),n("a25cd36d0cf21bc7df34");var i=function(t,e){var n=arguments.length>2&&void 0!==arguments[2]?arguments[2]:{},i=arguments.length>3&&void 0!==arguments[3]?arguments[3]:{};$('[data-notify="container"]').remove();var s="";switch(t){case"info":s="cd-icon cd-icon-info-o color-info mrm";break;case"success":s="cd-icon cd-icon-success-o color-success mrm";break;case"danger":s="cd-icon cd-icon-danger-o color-danger mrm";break;case"warning":s="cd-icon cd-icon-warning-o color-warning mrm"}var a={message:e,icon:s},o={type:t,delay:3e3,placement:{from:"top",align:"center"},animate:{enter:"animated fadeInDownSmall",exit:"animated fadeOutUp"},offset:80,z_index:1051,timer:100,template:'<div data-notify="container" class="notify-content"><div class="notify notify-{0}"><span data-notify="icon"></span><span data-notify="title">{1}</span><span data-notify="message">{2}</span><div class="progress" data-notify="progressbar"><div class="progress-bar progress-bar-{0}" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div></div><a href="{3}" target="{4}" data-notify="url"></a></div></div>'};$.notify(Object.assign(a,i),Object.assign(o,n))};e.default=i}});