!function(t){function e(o,r){if(n[o])return n[o].exports;var c={i:o,l:!1,exports:{}};return 0!=r&&(n[o]=c),t[o].call(c.exports,c,c.exports,e),c.l=!0,c.exports}var n={};e.m=t,e.c=n,e.d=function(t,n,o){e.o(t,n)||Object.defineProperty(t,n,{configurable:!1,enumerable:!0,get:o})},e.n=function(t){var n=t&&t.__esModule?function(){return t.default}:function(){return t};return e.d(n,"a",n),n},e.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},e.p="/static-dist/",e(e.s=20)}({20:function(t,e,n){t.exports=n("a140ba2b3f9117751115")},a140ba2b3f9117751115:function(t,e){(function(){(function(){(function(){(function(){(function(){(function(){(function(){var t=!1;(function(){!function(e,n){"function"==typeof t&&t.amd?t(function(){return n(e)}):e.echo=n(e)}(this,function(t){"use strict";var e,n,o,r,c,a={},u=function(){},i=function(t){return null===t.offsetParent},l=function(t,e){if(i(t))return!1;var n=t.getBoundingClientRect();return n.right>=e.l&&n.bottom>=e.t&&n.left<=e.r&&n.top<=e.b},d=function(){!r&&n||(clearTimeout(n),n=setTimeout(function(){a.render(),n=null},o))};return a.init=function(n){n=n||{};var i=n.offset||0,l=n.offsetVertical||i,f=n.offsetHorizontal||i,s=function(t,e){return parseInt(t||e,10)};e={t:s(n.offsetTop,l),b:s(n.offsetBottom,l),l:s(n.offsetLeft,f),r:s(n.offsetRight,f)},o=s(n.throttle,250),r=!1!==n.debounce,c=!!n.unload,u=n.callback||u,a.render(),document.addEventListener?(t.addEventListener("scroll",d,!1),t.addEventListener("load",d,!1)):(t.attachEvent("onscroll",d),t.attachEvent("onload",d))},a.render=function(){for(var n,o,r=document.querySelectorAll("img[data-echo], [data-echo-background]"),i=r.length,d={l:0-e.l,t:0-e.t,b:(t.innerHeight||document.documentElement.clientHeight)+e.b,r:(t.innerWidth||document.documentElement.clientWidth)+e.r},f=0;f<i;f++)o=r[f],l(o,d)?(c&&o.setAttribute("data-echo-placeholder",o.src),null!==o.getAttribute("data-echo-background")?o.style.backgroundImage="url("+o.getAttribute("data-echo-background")+")":o.src=o.getAttribute("data-echo"),c||(o.removeAttribute("data-echo"),o.removeAttribute("data-echo-background")),u(o,"load")):c&&(n=o.getAttribute("data-echo-placeholder"))&&(null!==o.getAttribute("data-echo-background")?o.style.backgroundImage="url("+n+")":o.src=n,o.removeAttribute("data-echo-placeholder"),u(o,"unload"));i||a.detach()},a.detach=function(){document.removeEventListener?t.removeEventListener("scroll",d):t.detachEvent("onscroll",d),clearTimeout(n)},a})}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}).call(window)}});