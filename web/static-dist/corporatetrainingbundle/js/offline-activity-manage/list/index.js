!function(t){function e(n,s){if(i[n])return i[n].exports;var a={i:n,l:!1,exports:{}};return 0!=s&&(i[n]=a),t[n].call(a.exports,a,a.exports,e),a.l=!0,a.exports}var i={};e.m=t,e.c=i,e.d=function(t,i,n){e.o(t,i)||Object.defineProperty(t,i,{configurable:!1,enumerable:!0,get:n})},e.n=function(t){var i=t&&t.__esModule?function(){return t.default}:function(){return t};return e.d(i,"a",i),i},e.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},e.p="/static-dist/",e(e.s="d8a02bfb40c0b3741e84")}({0:function(t,e){t.exports=jQuery},a25cd36d0cf21bc7df34:function(t,e,i){(function(){(function(){(function(){(function(){(function(){(function(){(function(){(function(){(function(){(function(){(function(){(function(){(function(){(function(){(function(){(function(){(function(){var t=!1;(function(){!function(e){"function"==typeof t&&t.amd?t(["jquery"],e):e(jQuery)}(function(t){function e(e,n,s){var n={content:{message:"object"==typeof n?n.message:n,title:n.title?n.title:"",icon:n.icon?n.icon:"",url:n.url?n.url:"#",target:n.target?n.target:"-"}};s=t.extend(!0,{},n,s),this.settings=t.extend(!0,{},i,s),this._defaults=i,"-"==this.settings.content.target&&(this.settings.content.target=this.settings.url_target),this.animations={start:"webkitAnimationStart oanimationstart MSAnimationStart animationstart",end:"webkitAnimationEnd oanimationend MSAnimationEnd animationend"},"number"==typeof this.settings.offset&&(this.settings.offset={x:this.settings.offset,y:this.settings.offset}),this.init()}var i={element:"body",position:null,type:"info",allow_dismiss:!0,newest_on_top:!1,showProgressbar:!1,placement:{from:"top",align:"right"},offset:20,spacing:10,z_index:1031,delay:5e3,timer:1e3,url_target:"_blank",mouse_over:null,animate:{enter:"animated fadeInDown",exit:"animated fadeOutUp"},onShow:null,onShown:null,onClose:null,onClosed:null,icon_type:"class",template:'<div data-notify="container" class="col-xs-11 col-sm-4 alert alert-{0}" role="alert"><button type="button" aria-hidden="true" class="close" data-notify="dismiss">&times;</button><span data-notify="icon"></span> <span data-notify="title">{1}</span> <span data-notify="message">{2}</span><div class="progress" data-notify="progressbar"><div class="progress-bar progress-bar-{0}" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div></div><a href="{3}" target="{4}" data-notify="url"></a></div>'};String.format=function(){for(var t=arguments[0],e=1;e<arguments.length;e++)t=t.replace(RegExp("\\{"+(e-1)+"\\}","gm"),arguments[e]);return t},t.extend(e.prototype,{init:function(){var t=this;this.buildNotify(),this.settings.content.icon&&this.setIcon(),"#"!=this.settings.content.url&&this.styleURL(),this.styleDismiss(),this.placement(),this.bind(),this.notify={$ele:this.$ele,update:function(e,i){var n={};"string"==typeof e?n[e]=i:n=e;for(var e in n)switch(e){case"type":this.$ele.removeClass("alert-"+t.settings.type),this.$ele.find('[data-notify="progressbar"] > .progress-bar').removeClass("progress-bar-"+t.settings.type),t.settings.type=n[e],this.$ele.addClass("alert-"+n[e]).find('[data-notify="progressbar"] > .progress-bar').addClass("progress-bar-"+n[e]);break;case"icon":var s=this.$ele.find('[data-notify="icon"]');"class"==t.settings.icon_type.toLowerCase()?s.removeClass(t.settings.content.icon).addClass(n[e]):(s.is("img")||s.find("img"),s.attr("src",n[e]));break;case"progress":var a=t.settings.delay-t.settings.delay*(n[e]/100);this.$ele.data("notify-delay",a),this.$ele.find('[data-notify="progressbar"] > div').attr("aria-valuenow",n[e]).css("width",n[e]+"%");break;case"url":this.$ele.find('[data-notify="url"]').attr("href",n[e]);break;case"target":this.$ele.find('[data-notify="url"]').attr("target",n[e]);break;default:this.$ele.find('[data-notify="'+e+'"]').html(n[e])}var o=this.$ele.outerHeight()+parseInt(t.settings.spacing)+parseInt(t.settings.offset.y);t.reposition(o)},close:function(){t.close()}}},buildNotify:function(){var e=this.settings.content;this.$ele=t(String.format(this.settings.template,this.settings.type,e.title,e.message,e.url,e.target)),this.$ele.attr("data-notify-position",this.settings.placement.from+"-"+this.settings.placement.align),this.settings.allow_dismiss||this.$ele.find('[data-notify="dismiss"]').css("display","none"),(this.settings.delay<=0&&!this.settings.showProgressbar||!this.settings.showProgressbar)&&this.$ele.find('[data-notify="progressbar"]').remove()},setIcon:function(){"class"==this.settings.icon_type.toLowerCase()?this.$ele.find('[data-notify="icon"]').addClass(this.settings.content.icon):this.$ele.find('[data-notify="icon"]').is("img")?this.$ele.find('[data-notify="icon"]').attr("src",this.settings.content.icon):this.$ele.find('[data-notify="icon"]').append('<img src="'+this.settings.content.icon+'" alt="Notify Icon" />')},styleDismiss:function(){this.$ele.find('[data-notify="dismiss"]').css({position:"absolute",right:"10px",top:"5px",zIndex:this.settings.z_index+2})},styleURL:function(){this.$ele.find('[data-notify="url"]').css({backgroundImage:"url(data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7)",height:"100%",left:"0px",position:"absolute",top:"0px",width:"100%",zIndex:this.settings.z_index+1})},placement:function(){var e=this,i=this.settings.offset.y,n={display:"inline-block",margin:"0px auto",position:this.settings.position?this.settings.position:"body"===this.settings.element?"fixed":"absolute",transition:"all .5s ease-in-out",zIndex:this.settings.z_index},s=!1,a=this.settings;switch(t('[data-notify-position="'+this.settings.placement.from+"-"+this.settings.placement.align+'"]:not([data-closing="true"])').each(function(){return i=Math.max(i,parseInt(t(this).css(a.placement.from))+parseInt(t(this).outerHeight())+parseInt(a.spacing))}),1==this.settings.newest_on_top&&(i=this.settings.offset.y),n[this.settings.placement.from]=i+"px",this.settings.placement.align){case"left":case"right":n[this.settings.placement.align]=this.settings.offset.x+"px";break;case"center":n.left=0,n.right=0}this.$ele.css(n).addClass(this.settings.animate.enter),t.each(Array("webkit-","moz-","o-","ms-",""),function(t,i){e.$ele[0].style[i+"AnimationIterationCount"]=1}),t(this.settings.element).append(this.$ele),1==this.settings.newest_on_top&&(i=parseInt(i)+parseInt(this.settings.spacing)+this.$ele.outerHeight(),this.reposition(i)),t.isFunction(e.settings.onShow)&&e.settings.onShow.call(this.$ele),this.$ele.one(this.animations.start,function(t){s=!0}).one(this.animations.end,function(i){t.isFunction(e.settings.onShown)&&e.settings.onShown.call(this)}),setTimeout(function(){s||t.isFunction(e.settings.onShown)&&e.settings.onShown.call(this)},600)},bind:function(){var e=this;if(this.$ele.find('[data-notify="dismiss"]').on("click",function(){e.close()}),this.$ele.mouseover(function(e){t(this).data("data-hover","true")}).mouseout(function(e){t(this).data("data-hover","false")}),this.$ele.data("data-hover","false"),this.settings.delay>0){e.$ele.data("notify-delay",e.settings.delay);var i=setInterval(function(){var t=parseInt(e.$ele.data("notify-delay"))-e.settings.timer;if("false"===e.$ele.data("data-hover")&&"pause"==e.settings.mouse_over||"pause"!=e.settings.mouse_over){var n=(e.settings.delay-t)/e.settings.delay*100;e.$ele.data("notify-delay",t),e.$ele.find('[data-notify="progressbar"] > div').attr("aria-valuenow",n).css("width",n+"%")}t<=-e.settings.timer&&(clearInterval(i),e.close())},e.settings.timer)}},close:function(){var e=this,i=parseInt(this.$ele.css(this.settings.placement.from)),n=!1;this.$ele.data("closing","true").addClass(this.settings.animate.exit),e.reposition(i),t.isFunction(e.settings.onClose)&&e.settings.onClose.call(this.$ele),this.$ele.one(this.animations.start,function(t){n=!0}).one(this.animations.end,function(i){t(this).remove(),t.isFunction(e.settings.onClosed)&&e.settings.onClosed.call(this)}),setTimeout(function(){n||(e.$ele.remove(),e.settings.onClosed&&e.settings.onClosed(e.$ele))},600)},reposition:function(e){var i=this,n='[data-notify-position="'+this.settings.placement.from+"-"+this.settings.placement.align+'"]:not([data-closing="true"])',s=this.$ele.nextAll(n);1==this.settings.newest_on_top&&(s=this.$ele.prevAll(n)),s.each(function(){t(this).css(i.settings.placement.from,e),e=parseInt(e)+parseInt(i.settings.spacing)+t(this).outerHeight()})}}),t.notify=function(t,i){return new e(this,t,i).notify},t.notifyDefaults=function(e){return i=t.extend(!0,{},i,e)},t.notifyClose=function(e){void 0===e||"all"==e?t("[data-notify]").find('[data-notify="dismiss"]').trigger("click"):t('[data-notify-position="'+e+'"]').find('[data-notify="dismiss"]').trigger("click")}})}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)},b334fd7e4c5a19234db2:function(t,e,i){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),i("a25cd36d0cf21bc7df34");var n=function(t,e){var i=arguments.length>2&&void 0!==arguments[2]?arguments[2]:{},n=arguments.length>3&&void 0!==arguments[3]?arguments[3]:{};$('[data-notify="container"]').remove();var s="";switch(t){case"info":s="cd-icon cd-icon-info-o color-info mrm";break;case"success":s="cd-icon cd-icon-success-o color-success mrm";break;case"danger":s="cd-icon cd-icon-danger-o color-danger mrm";break;case"warning":s="cd-icon cd-icon-warning-o color-warning mrm"}var a={message:e,icon:s},o={type:t,delay:3e3,placement:{from:"top",align:"center"},animate:{enter:"animated fadeInDownSmall",exit:"animated fadeOutUp"},offset:80,z_index:1051,timer:100,template:'<div data-notify="container" class="notify-content"><div class="notify notify-{0}"><span data-notify="icon"></span><span data-notify="title">{1}</span><span data-notify="message">{2}</span><div class="progress" data-notify="progressbar"><div class="progress-bar progress-bar-{0}" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div></div><a href="{3}" target="{4}" data-notify="url"></a></div></div>'};$.notify(Object.assign(a,n),Object.assign(o,i))};e.default=n},d8a02bfb40c0b3741e84:function(t,e,i){"use strict";var n=i("b334fd7e4c5a19234db2"),s=function(t){return t&&t.__esModule?t:{default:t}}(n);$("#member-list-table").on("click",".member-remove",function(){if(confirm(Translator.trans("offline_activity.delete.make_sure"))){var t=$(this).data("id");$.post($(this).data("url"),function(e){e?($("#member-list-table").children("tbody").children("tr#member-"+t).remove(),(0,s.default)("success",Translator.trans("offline_activity.delete.success_hint"))):(0,s.default)("success",Translator.trans("offline_activity.delete.fail_hint"))}).error(function(){(0,s.default)("success",Translator.trans("offline_activity.delete.fail_hint"))})}})}});