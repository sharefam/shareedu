!function(t){function e(s,n){if(i[s])return i[s].exports;var o={i:s,l:!1,exports:{}};return 0!=n&&(i[s]=o),t[s].call(o.exports,o,o.exports,e),o.l=!0,o.exports}var i={};e.m=t,e.c=i,e.d=function(t,i,s){e.o(t,i)||Object.defineProperty(t,i,{configurable:!1,enumerable:!0,get:s})},e.n=function(t){var i=t&&t.__esModule?function(){return t.default}:function(){return t};return e.d(i,"a",i),i},e.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},e.p="/static-dist/",e(e.s="c683cdc32728b5d26c11")}({0:function(t,e){t.exports=jQuery},"8f840897d9471c8c1fbd":function(t,e,i){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),i("b3c50df5d8bf6315aeba");var s=function(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:function(){},i={element:"#sortable-list",distance:20,itemSelector:"li.drag",ajax:!0},s=Object.assign({},i,t),n=$(s.element).sortable(Object.assign({},s,{onDrop:function(t,i,o){o(t,i);var a=n.sortable("serialize").get();e(a),s.ajax&&$.post(n.data("sortUrl"),{ids:a},function(t){s.success?s.success(t):document.location.reload()})},serialize:function(t,e,i){return i?e:t.attr("id")}}))};e.default=s},a25cd36d0cf21bc7df34:function(t,e,i){(function(){(function(){(function(){(function(){(function(){(function(){(function(){(function(){(function(){(function(){(function(){(function(){(function(){(function(){(function(){(function(){(function(){var t=!1;(function(){!function(e){"function"==typeof t&&t.amd?t(["jquery"],e):e(jQuery)}(function(t){function e(e,s,n){var s={content:{message:"object"==typeof s?s.message:s,title:s.title?s.title:"",icon:s.icon?s.icon:"",url:s.url?s.url:"#",target:s.target?s.target:"-"}};n=t.extend(!0,{},s,n),this.settings=t.extend(!0,{},i,n),this._defaults=i,"-"==this.settings.content.target&&(this.settings.content.target=this.settings.url_target),this.animations={start:"webkitAnimationStart oanimationstart MSAnimationStart animationstart",end:"webkitAnimationEnd oanimationend MSAnimationEnd animationend"},"number"==typeof this.settings.offset&&(this.settings.offset={x:this.settings.offset,y:this.settings.offset}),this.init()}var i={element:"body",position:null,type:"info",allow_dismiss:!0,newest_on_top:!1,showProgressbar:!1,placement:{from:"top",align:"right"},offset:20,spacing:10,z_index:1031,delay:5e3,timer:1e3,url_target:"_blank",mouse_over:null,animate:{enter:"animated fadeInDown",exit:"animated fadeOutUp"},onShow:null,onShown:null,onClose:null,onClosed:null,icon_type:"class",template:'<div data-notify="container" class="col-xs-11 col-sm-4 alert alert-{0}" role="alert"><button type="button" aria-hidden="true" class="close" data-notify="dismiss">&times;</button><span data-notify="icon"></span> <span data-notify="title">{1}</span> <span data-notify="message">{2}</span><div class="progress" data-notify="progressbar"><div class="progress-bar progress-bar-{0}" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div></div><a href="{3}" target="{4}" data-notify="url"></a></div>'};String.format=function(){for(var t=arguments[0],e=1;e<arguments.length;e++)t=t.replace(RegExp("\\{"+(e-1)+"\\}","gm"),arguments[e]);return t},t.extend(e.prototype,{init:function(){var t=this;this.buildNotify(),this.settings.content.icon&&this.setIcon(),"#"!=this.settings.content.url&&this.styleURL(),this.styleDismiss(),this.placement(),this.bind(),this.notify={$ele:this.$ele,update:function(e,i){var s={};"string"==typeof e?s[e]=i:s=e;for(var e in s)switch(e){case"type":this.$ele.removeClass("alert-"+t.settings.type),this.$ele.find('[data-notify="progressbar"] > .progress-bar').removeClass("progress-bar-"+t.settings.type),t.settings.type=s[e],this.$ele.addClass("alert-"+s[e]).find('[data-notify="progressbar"] > .progress-bar').addClass("progress-bar-"+s[e]);break;case"icon":var n=this.$ele.find('[data-notify="icon"]');"class"==t.settings.icon_type.toLowerCase()?n.removeClass(t.settings.content.icon).addClass(s[e]):(n.is("img")||n.find("img"),n.attr("src",s[e]));break;case"progress":var o=t.settings.delay-t.settings.delay*(s[e]/100);this.$ele.data("notify-delay",o),this.$ele.find('[data-notify="progressbar"] > div').attr("aria-valuenow",s[e]).css("width",s[e]+"%");break;case"url":this.$ele.find('[data-notify="url"]').attr("href",s[e]);break;case"target":this.$ele.find('[data-notify="url"]').attr("target",s[e]);break;default:this.$ele.find('[data-notify="'+e+'"]').html(s[e])}var a=this.$ele.outerHeight()+parseInt(t.settings.spacing)+parseInt(t.settings.offset.y);t.reposition(a)},close:function(){t.close()}}},buildNotify:function(){var e=this.settings.content;this.$ele=t(String.format(this.settings.template,this.settings.type,e.title,e.message,e.url,e.target)),this.$ele.attr("data-notify-position",this.settings.placement.from+"-"+this.settings.placement.align),this.settings.allow_dismiss||this.$ele.find('[data-notify="dismiss"]').css("display","none"),(this.settings.delay<=0&&!this.settings.showProgressbar||!this.settings.showProgressbar)&&this.$ele.find('[data-notify="progressbar"]').remove()},setIcon:function(){"class"==this.settings.icon_type.toLowerCase()?this.$ele.find('[data-notify="icon"]').addClass(this.settings.content.icon):this.$ele.find('[data-notify="icon"]').is("img")?this.$ele.find('[data-notify="icon"]').attr("src",this.settings.content.icon):this.$ele.find('[data-notify="icon"]').append('<img src="'+this.settings.content.icon+'" alt="Notify Icon" />')},styleDismiss:function(){this.$ele.find('[data-notify="dismiss"]').css({position:"absolute",right:"10px",top:"5px",zIndex:this.settings.z_index+2})},styleURL:function(){this.$ele.find('[data-notify="url"]').css({backgroundImage:"url(data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7)",height:"100%",left:"0px",position:"absolute",top:"0px",width:"100%",zIndex:this.settings.z_index+1})},placement:function(){var e=this,i=this.settings.offset.y,s={display:"inline-block",margin:"0px auto",position:this.settings.position?this.settings.position:"body"===this.settings.element?"fixed":"absolute",transition:"all .5s ease-in-out",zIndex:this.settings.z_index},n=!1,o=this.settings;switch(t('[data-notify-position="'+this.settings.placement.from+"-"+this.settings.placement.align+'"]:not([data-closing="true"])').each(function(){return i=Math.max(i,parseInt(t(this).css(o.placement.from))+parseInt(t(this).outerHeight())+parseInt(o.spacing))}),1==this.settings.newest_on_top&&(i=this.settings.offset.y),s[this.settings.placement.from]=i+"px",this.settings.placement.align){case"left":case"right":s[this.settings.placement.align]=this.settings.offset.x+"px";break;case"center":s.left=0,s.right=0}this.$ele.css(s).addClass(this.settings.animate.enter),t.each(Array("webkit-","moz-","o-","ms-",""),function(t,i){e.$ele[0].style[i+"AnimationIterationCount"]=1}),t(this.settings.element).append(this.$ele),1==this.settings.newest_on_top&&(i=parseInt(i)+parseInt(this.settings.spacing)+this.$ele.outerHeight(),this.reposition(i)),t.isFunction(e.settings.onShow)&&e.settings.onShow.call(this.$ele),this.$ele.one(this.animations.start,function(t){n=!0}).one(this.animations.end,function(i){t.isFunction(e.settings.onShown)&&e.settings.onShown.call(this)}),setTimeout(function(){n||t.isFunction(e.settings.onShown)&&e.settings.onShown.call(this)},600)},bind:function(){var e=this;if(this.$ele.find('[data-notify="dismiss"]').on("click",function(){e.close()}),this.$ele.mouseover(function(e){t(this).data("data-hover","true")}).mouseout(function(e){t(this).data("data-hover","false")}),this.$ele.data("data-hover","false"),this.settings.delay>0){e.$ele.data("notify-delay",e.settings.delay);var i=setInterval(function(){var t=parseInt(e.$ele.data("notify-delay"))-e.settings.timer;if("false"===e.$ele.data("data-hover")&&"pause"==e.settings.mouse_over||"pause"!=e.settings.mouse_over){var s=(e.settings.delay-t)/e.settings.delay*100;e.$ele.data("notify-delay",t),e.$ele.find('[data-notify="progressbar"] > div').attr("aria-valuenow",s).css("width",s+"%")}t<=-e.settings.timer&&(clearInterval(i),e.close())},e.settings.timer)}},close:function(){var e=this,i=parseInt(this.$ele.css(this.settings.placement.from)),s=!1;this.$ele.data("closing","true").addClass(this.settings.animate.exit),e.reposition(i),t.isFunction(e.settings.onClose)&&e.settings.onClose.call(this.$ele),this.$ele.one(this.animations.start,function(t){s=!0}).one(this.animations.end,function(i){t(this).remove(),t.isFunction(e.settings.onClosed)&&e.settings.onClosed.call(this)}),setTimeout(function(){s||(e.$ele.remove(),e.settings.onClosed&&e.settings.onClosed(e.$ele))},600)},reposition:function(e){var i=this,s='[data-notify-position="'+this.settings.placement.from+"-"+this.settings.placement.align+'"]:not([data-closing="true"])',n=this.$ele.nextAll(s);1==this.settings.newest_on_top&&(n=this.$ele.prevAll(s)),n.each(function(){t(this).css(i.settings.placement.from,e),e=parseInt(e)+parseInt(i.settings.spacing)+t(this).outerHeight()})}}),t.notify=function(t,i){return new e(this,t,i).notify},t.notifyDefaults=function(e){return i=t.extend(!0,{},i,e)},t.notifyClose=function(e){void 0===e||"all"==e?t("[data-notify]").find('[data-notify="dismiss"]').trigger("click"):t('[data-notify-position="'+e+'"]').find('[data-notify="dismiss"]').trigger("click")}})}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)},b334fd7e4c5a19234db2:function(t,e,i){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),i("a25cd36d0cf21bc7df34");var s=function(t,e){var i=arguments.length>2&&void 0!==arguments[2]?arguments[2]:{},s=arguments.length>3&&void 0!==arguments[3]?arguments[3]:{};$('[data-notify="container"]').remove();var n="";switch(t){case"info":n="cd-icon cd-icon-info-o color-info mrm";break;case"success":n="cd-icon cd-icon-success-o color-success mrm";break;case"danger":n="cd-icon cd-icon-danger-o color-danger mrm";break;case"warning":n="cd-icon cd-icon-warning-o color-warning mrm"}var o={message:e,icon:n},a={type:t,delay:3e3,placement:{from:"top",align:"center"},animate:{enter:"animated fadeInDownSmall",exit:"animated fadeOutUp"},offset:80,z_index:1051,timer:100,template:'<div data-notify="container" class="notify-content"><div class="notify notify-{0}"><span data-notify="icon"></span><span data-notify="title">{1}</span><span data-notify="message">{2}</span><div class="progress" data-notify="progressbar"><div class="progress-bar progress-bar-{0}" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div></div><a href="{3}" target="{4}" data-notify="url"></a></div></div>'};$.notify(Object.assign(o,s),Object.assign(a,i))};e.default=s},b3c50df5d8bf6315aeba:function(t,e){(function(){(function(){(function(){(function(){(function(){(function(){(function(){(function(){(function(){(function(){(function(){!function(t,e,i,s){function n(t,e){return Math.max(0,t[0]-e[0],e[0]-t[1])+Math.max(0,t[2]-e[1],e[1]-t[3])}function o(e,i,s,n){var o=e.length,a=n?"offset":"position";for(s=s||0;o--;){var r=e[o].el?e[o].el:t(e[o]),l=r[a]();l.left+=parseInt(r.css("margin-left"),10),l.top+=parseInt(r.css("margin-top"),10),i[o]=[l.left-s,l.left+r.outerWidth()+s,l.top-s,l.top+r.outerHeight()+s]}}function a(t,e){var i=e.offset();return{left:t.left-i.left,top:t.top-i.top}}function r(t,e,i){e=[e.left,e.top],i=i&&[i.left,i.top];for(var s,o=t.length,a=[];o--;)s=t[o],a[o]=[o,n(s,e),i&&n(s,i)];return a=a.sort(function(t,e){return e[1]-t[1]||e[2]-t[2]||e[0]-t[0]})}function l(e){this.options=t.extend({},d,e),this.containers=[],this.options.rootGroup||(this.scrollProxy=t.proxy(this.scroll,this),this.dragProxy=t.proxy(this.drag,this),this.dropProxy=t.proxy(this.drop,this),this.placeholder=t(this.options.placeholder),e.isValidTarget||(this.options.isValidTarget=s))}function c(e,i){this.el=e,this.options=t.extend({},h,i),this.group=l.get(this.options),this.rootGroup=this.options.rootGroup||this.group,this.handle=this.rootGroup.options.handle||this.rootGroup.options.itemSelector;var s=this.rootGroup.options.itemPath;this.target=s?this.el.find(s):this.el,this.target.on(g.start,this.handle,t.proxy(this.dragInit,this)),this.options.drop&&this.group.containers.push(this)}var h={drag:!0,drop:!0,exclude:"",nested:!0,vertical:!0},d={afterMove:function(t,e,i){},containerPath:"",containerSelector:"ol, ul",distance:0,delay:0,handle:"",itemPath:"",itemSelector:"li",bodyClass:"dragging",draggedClass:"dragged",isValidTarget:function(t,e){return!0},onCancel:function(t,e,i,s){},onDrag:function(t,e,i,s){t.css(e)},onDragStart:function(e,i,s,n){e.css({height:e.outerHeight(),width:e.outerWidth()}),e.addClass(i.group.options.draggedClass),t("body").addClass(i.group.options.bodyClass)},onDrop:function(e,i,s,n){e.removeClass(i.group.options.draggedClass).removeAttr("style"),t("body").removeClass(i.group.options.bodyClass)},onMousedown:function(t,e,i){if(!i.target.nodeName.match(/^(input|select|textarea)$/i))return i.preventDefault(),!0},placeholderClass:"placeholder",placeholder:'<li class="placeholder"></li>',pullPlaceholder:!0,serialize:function(e,i,s){var n=t.extend({},e.data());return s?[i]:(i[0]&&(n.children=i),delete n.subContainers,delete n.sortable,n)},tolerance:0},f={},u=0,p={left:0,top:0,bottom:0,right:0},g={start:"touchstart.sortable mousedown.sortable",drop:"touchend.sortable touchcancel.sortable mouseup.sortable",drag:"touchmove.sortable mousemove.sortable",scroll:"scroll.sortable"};l.get=function(t){return f[t.group]||(t.group===s&&(t.group=u++),f[t.group]=new l(t)),f[t.group]},l.prototype={dragInit:function(e,i){this.$document=t(i.el[0].ownerDocument);var s=t(e.target).closest(this.options.itemSelector);if(s.length){if(this.item=s,this.itemContainer=i,this.item.is(this.options.exclude)||!this.options.onMousedown(this.item,d.onMousedown,e))return;this.setPointer(e),this.toggleListeners("on"),this.setupDelayTimer(),this.dragInitDone=!0}},drag:function(t){if(!this.dragging){if(!this.distanceMet(t)||!this.delayMet)return;this.options.onDragStart(this.item,this.itemContainer,d.onDragStart,t),this.item.before(this.placeholder),this.dragging=!0}this.setPointer(t),this.options.onDrag(this.item,a(this.pointer,this.item.offsetParent()),d.onDrag,t);var e=this.getPointer(t),i=this.sameResultBox,n=this.options.tolerance;(!i||i.top-n>e.top||i.bottom+n<e.top||i.left-n>e.left||i.right+n<e.left)&&(this.searchValidTarget()||(this.placeholder.detach(),this.lastAppendedItem=s))},drop:function(t){this.toggleListeners("off"),this.dragInitDone=!1,this.dragging&&(this.placeholder.closest("html")[0]?this.placeholder.before(this.item).detach():this.options.onCancel(this.item,this.itemContainer,d.onCancel,t),this.options.onDrop(this.item,this.getContainer(this.item),d.onDrop,t),this.clearDimensions(),this.clearOffsetParent(),this.lastAppendedItem=this.sameResultBox=s,this.dragging=!1)},searchValidTarget:function(t,e){t||(t=this.relativePointer||this.pointer,e=this.lastRelativePointer||this.lastPointer);for(var i=r(this.getContainerDimensions(),t,e),n=i.length;n--;){var o=i[n][0];if(!i[n][1]||this.options.pullPlaceholder){var l=this.containers[o];if(!l.disabled){if(!this.$getOffsetParent()){var c=l.getItemOffsetParent();t=a(t,c),e=a(e,c)}if(l.searchValidTarget(t,e))return!0}}}this.sameResultBox&&(this.sameResultBox=s)},movePlaceholder:function(t,e,i,s){var n=this.lastAppendedItem;!s&&n&&n[0]===e[0]||(e[i](this.placeholder),this.lastAppendedItem=e,this.sameResultBox=s,this.options.afterMove(this.placeholder,t,e))},getContainerDimensions:function(){return this.containerDimensions||o(this.containers,this.containerDimensions=[],this.options.tolerance,!this.$getOffsetParent()),this.containerDimensions},getContainer:function(t){return t.closest(this.options.containerSelector).data("sortable")},$getOffsetParent:function(){if(this.offsetParent===s){var t=this.containers.length-1,e=this.containers[t].getItemOffsetParent();if(!this.options.rootGroup)for(;t--;)if(e[0]!=this.containers[t].getItemOffsetParent()[0]){e=!1;break}this.offsetParent=e}return this.offsetParent},setPointer:function(t){var e=this.getPointer(t);if(this.$getOffsetParent()){var i=a(e,this.$getOffsetParent());this.lastRelativePointer=this.relativePointer,this.relativePointer=i}this.lastPointer=this.pointer,this.pointer=e},distanceMet:function(t){var e=this.getPointer(t);return Math.max(Math.abs(this.pointer.left-e.left),Math.abs(this.pointer.top-e.top))>=this.options.distance},getPointer:function(t){var e=t.originalEvent||t.originalEvent.touches&&t.originalEvent.touches[0];return{left:t.pageX||e.pageX,top:t.pageY||e.pageY}},setupDelayTimer:function(){var t=this;this.delayMet=!this.options.delay,this.delayMet||(clearTimeout(this._mouseDelayTimer),this._mouseDelayTimer=setTimeout(function(){t.delayMet=!0},this.options.delay))},scroll:function(t){this.clearDimensions(),this.clearOffsetParent()},toggleListeners:function(e){var i=this,s=["drag","drop","scroll"];t.each(s,function(t,s){i.$document[e](g[s],i[s+"Proxy"])})},clearOffsetParent:function(){this.offsetParent=s},clearDimensions:function(){this.traverse(function(t){t._clearDimensions()})},traverse:function(t){t(this);for(var e=this.containers.length;e--;)this.containers[e].traverse(t)},_clearDimensions:function(){this.containerDimensions=s},_destroy:function(){f[this.options.group]=s}},c.prototype={dragInit:function(t){var e=this.rootGroup;!this.disabled&&!e.dragInitDone&&this.options.drag&&this.isValidDrag(t)&&e.dragInit(t,this)},isValidDrag:function(t){return 1==t.which||"touchstart"==t.type&&1==t.originalEvent.touches.length},searchValidTarget:function(t,e){var i=r(this.getItemDimensions(),t,e),s=i.length,n=this.rootGroup,o=!n.options.isValidTarget||n.options.isValidTarget(n.item,this);if(!s&&o)return n.movePlaceholder(this,this.target,"append"),!0;for(;s--;){var a=i[s][0];if(!i[s][1]&&this.hasChildGroup(a)){if(this.getContainerGroup(a).searchValidTarget(t,e))return!0}else if(o)return this.movePlaceholder(a,t),!0}},movePlaceholder:function(e,i){var s=t(this.items[e]),n=this.itemDimensions[e],o="after",a=s.outerWidth(),r=s.outerHeight(),l=s.offset(),c={left:l.left,right:l.left+a,top:l.top,bottom:l.top+r};if(this.options.vertical){var h=(n[2]+n[3])/2;i.top<=h?(o="before",c.bottom-=r/2):c.top+=r/2}else{var d=(n[0]+n[1])/2;i.left<=d?(o="before",c.right-=a/2):c.left+=a/2}this.hasChildGroup(e)&&(c=p),this.rootGroup.movePlaceholder(this,s,o,c)},getItemDimensions:function(){return this.itemDimensions||(this.items=this.$getChildren(this.el,"item").filter(":not(."+this.group.options.placeholderClass+", ."+this.group.options.draggedClass+")").get(),o(this.items,this.itemDimensions=[],this.options.tolerance)),this.itemDimensions},getItemOffsetParent:function(){var t=this.el;return"relative"===t.css("position")||"absolute"===t.css("position")||"fixed"===t.css("position")?t:t.offsetParent()},hasChildGroup:function(t){return this.options.nested&&this.getContainerGroup(t)},getContainerGroup:function(e){var i=t.data(this.items[e],"subContainers");if(i===s){var n=this.$getChildren(this.items[e],"container");if(i=!1,n[0]){var o=t.extend({},this.options,{rootGroup:this.rootGroup,group:u++});i=n.sortable(o).data("sortable").group}t.data(this.items[e],"subContainers",i)}return i},$getChildren:function(e,i){var s=this.rootGroup.options,n=s[i+"Path"],o=s[i+"Selector"];return e=t(e),n&&(e=e.find(n)),e.children(o)},_serialize:function(e,i){var s=this,n=i?"item":"container",o=this.$getChildren(e,n).not(this.options.exclude).map(function(){return s._serialize(t(this),!i)}).get();return this.rootGroup.options.serialize(e,o,i)},traverse:function(e){t.each(this.items||[],function(i){var s=t.data(this,"subContainers");s&&s.traverse(e)}),e(this)},_clearDimensions:function(){this.itemDimensions=s},_destroy:function(){var e=this;this.target.off(g.start,this.handle),this.el.removeData("sortable"),this.options.drop&&(this.group.containers=t.grep(this.group.containers,function(t){return t!=e})),t.each(this.items||[],function(){t.removeData(this,"subContainers")})}};var m={enable:function(){this.traverse(function(t){t.disabled=!1})},disable:function(){this.traverse(function(t){t.disabled=!0})},serialize:function(){return this._serialize(this.el,!0)},refresh:function(){this.traverse(function(t){t._clearDimensions()})},destroy:function(){this.traverse(function(t){t._destroy()})}};t.extend(c.prototype,m),t.fn.sortable=function(e){var i=Array.prototype.slice.call(arguments,1);return this.map(function(){var n=t(this),o=n.data("sortable");return o&&m[e]?m[e].apply(o,i)||this:(o||e!==s&&"object"!=typeof e||n.data("sortable",new c(n,e)),this)})}}(jQuery,window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)},c683cdc32728b5d26c11:function(t,e,i){"use strict";function s(t){return t&&t.__esModule?t:{default:t}}var n=i("b334fd7e4c5a19234db2"),o=s(n);new(s(i("8f840897d9471c8c1fbd")).default)({element:"#sortable-list",ajax:!1},function(t){$.post($("#sortable-list").data("sorturl"),{ids:t},function(t){}),$("#sortable-list").find(".drag").each(function(t,e){$(e).find(".js-project-unit-number").text(t+1+".")})}),$(".remove-item").click(function(){if(confirm(Translator.trans("project_plan.item.remove_confirm_message"))){var t=$(this).data("courseid"),e=$(this).data("taskid");$.post($(this).data("url"),function(i){i?($("#course-"+t+"-task-"+e).remove(),(0,o.default)("success",Translator.trans("project_plan.item.remove_success_message")),window.location.reload()):(0,o.default)("success",Translator.trans("project_plan.item.remove_error_message"))}).error(function(){(0,o.default)("success",Translator.trans("project_plan.item.remove_error_message"))})}})}});