!function(e){function t(i,r){if(n[i])return n[i].exports;var a={i:i,l:!1,exports:{}};return 0!=r&&(n[i]=a),e[i].call(a.exports,a,a.exports,t),a.l=!0,a.exports}var n={};t.m=e,t.c=n,t.d=function(e,n,i){t.o(e,n)||Object.defineProperty(e,n,{configurable:!1,enumerable:!0,get:i})},t.n=function(e){var n=e&&e.__esModule?function(){return e.default}:function(){return e};return t.d(n,"a",n),n},t.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},t.p="/static-dist/",t(t.s="92e2212d883fe3eefa00")}({"0b0efb48335f882ef5bb":function(e,t,n){"use strict";function i(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}Object.defineProperty(t,"__esModule",{value:!0});var r=function(){function e(e,t){for(var n=0;n<t.length;n++){var i=t[n];i.enumerable=i.enumerable||!1,i.configurable=!0,"value"in i&&(i.writable=!0),Object.defineProperty(e,i.key,i)}}return function(t,n,i){return n&&e(t.prototype,n),i&&e(t,i),t}}(),a=n("2cc101bc4f98f5bd7ce9"),o=function(e){return e&&e.__esModule?e:{default:e}}(a),c=function(){function e(t){i(this,e),"single"==t.selectType&&(t.showCheckbox=!1),Object.assign(this,e.getDefaultOpts(),t),this.$elem.length&&("single"==this.selectType&&(this.disableNodeCheck=!0),this.init())}return r(e,null,[{key:"getDefaultOpts",value:function(){return{treeviewText:".js-treeview-text",treeviewSelect:".js-treeview-select",treeviewSelectMenu:".js-treeview-select-menu",treeviewIpt:".js-treeview-ipt",treeviewData:".js-treeview-data",disableNodeCheck:!1,saveColumn:"orgCode",showCheckbox:!0,transportChildren:!1,unCheckParent:!1}}}]),r(e,[{key:"init",value:function(){this.initTreeview(),this.initEvent(),this.nodeSelect()}},{key:"initTreeview",value:function(){for(var e=this.getData()?this.getData():"{}",t=JSON.parse(e),n=this.$elem.find(this.treeviewIpt).val()?this.$elem.find(this.treeviewIpt).val():"",i=this.$elem.data("checkColumnName"),r=n.split(","),a={},c=[].concat(t),u=0;u<r.length;u++)a[r[u]]=!0;for(;c.length>0;){var l=c.pop();l.state={expanded:!1},a[l[i]]&&(l.state.checked=!0),void 0==l.selectable||l.selectable||(l.state.disabled=!0,l.state.checked=!1,l.hideCheckbox=!0),l.exclude&&(l.state.color="#0093FF",l.state.backColor="#F5F5F5",l.hideCheckbox=!0),l.expanded&&(l.state.expanded=!0,l.hideCheckbox=!0),l.state.selected=!1,l.nodes&&(c=c.concat(l.nodes))}this.checkTreeview=new o.default(this.$elem.find(this.treeviewSelectMenu),{data:t,disableNodeCheck:this.disableNodeCheck,showCheckbox:this.showCheckbox,unCheckParent:this.unCheckParent});var s=this.checkTreeview.getCheckNodes();if(s.length){var d=s.reduce(function(e,t){return e+(e&&",")+t.name+" "},"");this.$elem.find(this.treeviewText).val(d)}}},{key:"initEvent",value:function(){var e=this,t=this;this.$elem.on("focus",t.treeviewText,function(t){return e.focusHandler(t)}),this.$elem.find(t.treeviewSelect).on("click",function(t){return e.clickHandler(t)}),$(document).on("click","body",function(t){return e.hideEvent(t)})}},{key:"clickHandler",value:function(e){for(var t=this,n=t.checkTreeview.getCheckNodes(),i="",r=Math.min(n.length,10),a=0;a<r;a++)n[a].disable||n[a].exclude||(i=i+(i&&",")+n[a].name+" ");r!=n.length&&(i+="...");var o=[],c=n.reduce(function(e,n){return n.disable||n.exclude?e:o.indexOf(n.parentId)>=0&&!t.transportChildren?(o.push(n.nodeId),e):(o.push(n.nodeId),e+(e&&",")+n[t.saveColumn])},"");$(e.currentTarget).find(t.treeviewText).val(i),$(e.currentTarget).find(t.treeviewIpt).val(c),e.stopPropagation()}},{key:"focusHandler",value:function(e){var t=this;$(t.treeviewSelectMenu).removeClass("is-active"),$(e.currentTarget).parents(t.treeviewSelect).find(t.treeviewSelectMenu).addClass("is-active")}},{key:"nodeSelect",value:function(){if("single"==this.selectType)return void this.singleSelect();this.multipleSelect()}},{key:"singleSelect",value:function(){var e=this;this.$elem.on("nodeElementSelect",function(t,n){if(n.selectable){if(n.exclude)return!1;var i=e.checkTreeview.getTreeObject();i.uncheckAll(),e.$elem.find(e.treeviewIpt).val(""),i.checkNode(n.nodeId),$(e.treeviewSelectMenu).removeClass("is-active")}})}},{key:"multipleSelect",value:function(){var e=this;this.$elem.on("nodeElementSelect",function(t,n){if(n.selectable){var i=e.checkTreeview.getTreeObject();e.multipleSelectToggle(t,n,i)}})}},{key:"multipleSelectToggle",value:function(t,n,i){var r=e.getSelectNode(n);if(r){if(r.state&&r.state.checked)return this.checkTreeview.unchecksubTreeNode(t,n),void(r.exclude||this.checkTreeview.uncheckTreeNode(n));this.checkTreeview.checksubTreeNode(t,n),r.exclude||this.checkTreeview.checkTreeNode(n),i.expandNode(n.nodeId)}}},{key:"getData",value:function(){var e=this.$elem.find(this.treeviewData).text();return e||this.$elem.find(this.treeviewData).val()}},{key:"hideEvent",value:function(e){$(".js-treeview-select-menu.is-active").each(function(e,t){$(t).removeClass("is-active").closest(".js-treeview-select-wrap").trigger("treeHide")})}}],[{key:"getSelectNode",value:function(e){return e?e.exclude?e.nodes&&e.nodes.length?e.nodes[0]:null:e:null}}]),e}();t.default=c},"2cc101bc4f98f5bd7ce9":function(e,t,n){"use strict";function i(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}function r(e,t){if(!e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return!t||"object"!=typeof t&&"function"!=typeof t?e:t}function a(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function, not "+typeof t);e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,enumerable:!1,writable:!0,configurable:!0}}),t&&(Object.setPrototypeOf?Object.setPrototypeOf(e,t):e.__proto__=t)}Object.defineProperty(t,"__esModule",{value:!0});var o=function(){function e(e,t){for(var n=0;n<t.length;n++){var i=t[n];i.enumerable=i.enumerable||!1,i.configurable=!0,"value"in i&&(i.writable=!0),Object.defineProperty(e,i.key,i)}}return function(t,n,i){return n&&e(t.prototype,n),i&&e(t,i),t}}(),c=n("eb3d1a6f842eb5dfa94b"),u=function(e){return e&&e.__esModule?e:{default:e}}(c),l=function(e){function t(e,n){i(this,t);var a=r(this,(t.__proto__||Object.getPrototypeOf(t)).call(this)),o=t.getDefaultOpts();return a.$elem=e,n=Object.assign({},o,n),a.init(n),a.silent=!0,a.unCheckParent=n.unCheckParent,a}return a(t,e),o(t,null,[{key:"getDefaultOpts",value:function(){return{showIcon:!1,showCheckbox:!0,highlightSelected:!1}}}]),o(t,[{key:"init",value:function(e){this.initEvent(e),this.$elem.treeview(e)}},{key:"initEvent",value:function(e){var t=this;e.disableNodeCheck||(e.onNodeChecked=function(e,n){t.OnNodeChecked(e,n)},e.onNodeUnchecked=function(e,n){t.OnNodeUnChecked(e,n)})}},{key:"OnNodeChecked",value:function(e,t){this.checksubTreeNode(e,t)}},{key:"OnNodeUnChecked",value:function(e,t){var n=this.unCheckParent;this.unchecksubTreeNode(e,t,n)}},{key:"getCheckNodes",value:function(){return this.$elem.treeview("getChecked")}},{key:"getTreeObject",value:function(){return this.$elem.data("treeview")}},{key:"checkParentNode",value:function(e,t){if(void 0==t.parentId)return!1;for(var n=this.getParentNode(t),i=n.nodes,r=0;r<i.length;r++)if(!i[r].state.checked)return!1;this.$elem.treeview("checkNode",[n,{silent:!0}]),this.checkParentNode(e,n)}}]),t}(u.default);t.default=l},"92e2212d883fe3eefa00":function(e,t,n){"use strict";function i(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}var r=function(){function e(e,t){for(var n=0;n<t.length;n++){var i=t[n];i.enumerable=i.enumerable||!1,i.configurable=!0,"value"in i&&(i.writable=!0),Object.defineProperty(e,i.key,i)}}return function(t,n,i){return n&&e(t.prototype,n),i&&e(t,i),t}}(),a=n("0b0efb48335f882ef5bb"),o=function(e){return e&&e.__esModule?e:{default:e}}(a);jQuery.validator.addMethod("endDate_check",function(){return $('[name="startDateTime"]').val()<=$('[name="endDateTime"]').val()},Translator.trans("my.department.data_report.endDate_check")),new(function(){function e(){i(this,e),this.init()}return r(e,[{key:"init",value:function(){this.initSelect(),this.initDatetimePicker(),this.initvalidator(),this.initTreeviewInput(),this.initEvent()}},{key:"initEvent",value:function(){$(".data-list").on("click",".pagination li",function(){var e=$(this).data("url");void 0!==e&&$.post(e,$(".department-manage-search-form").serialize(),function(e){$(".data-list").html(e),$('[data-toggle="popover"]').popover()})}),$(".js-exporter").on("click",function(){var e=$(".form-inline"),t=$(this).data("url");$(e).attr("action",t),e.submit(),$(e).attr("action","")})}},{key:"initSelect",value:function(){$('[data-role="tree-select"], [name="categoryId"]').select2({treeview:!0,dropdownAutoWidth:!0,treeviewInitState:"collapsed",placeholderOption:"first"})}},{key:"initDatetimePicker",value:function(){var e={language:"zh",autoclose:!0,format:"yyyy-mm-dd",minView:"month"};$("[name=startDateTime]").datetimepicker(e),$("[name=endDateTime]").datetimepicker(e)}},{key:"initvalidator",value:function(){var e=$("#postId");e.select2({ajax:{url:e.data("url"),dataType:"json",quietMillis:100,data:function(e,t){return{q:e,page_limit:10}},results:function(e){var t=[];return $.each(e,function(e,n){t.push({id:n.id,name:n.name})}),{results:t}}},initSelection:function(e,t){var n=$(e).val();if(""!==n){t({id:n,name:$(e).data("name")})}},formatSelection:function(e){return e.name},formatResult:function(e){return e.name},formatSearching:function(){return Translator.trans("site.searching_hint")},formatNoMatches:function(){return Translator.trans("select.format_no_matches")},allowClear:!0,width:"off",placeholder:Translator.trans("my.department.data_report.all_post")})}},{key:"initTreeviewInput",value:function(){for(var e=$(".js-treeview-select-wrap"),t=0;t<e.length;t++)new o.default({$elem:e.eq(t)})}}]),e}())},eb3d1a6f842eb5dfa94b:function(e,t,n){"use strict";function i(e){if(Array.isArray(e)){for(var t=0,n=Array(e.length);t<e.length;t++)n[t]=e[t];return n}return Array.from(e)}function r(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}Object.defineProperty(t,"__esModule",{value:!0});var a=function(){function e(e,t){for(var n=0;n<t.length;n++){var i=t[n];i.enumerable=i.enumerable||!1,i.configurable=!0,"value"in i&&(i.writable=!0),Object.defineProperty(e,i.key,i)}}return function(t,n,i){return n&&e(t.prototype,n),i&&e(t,i),t}}(),o=function(){function e(t){r(this,e)}return a(e,[{key:"setTreeNodeState",value:function(e){var t=e.state,n=void 0===t?"checkNode":t,i=e.nodeId;this.$elem.treeview(n,[i,{silent:!0}])}},{key:"checkTreeNode",value:function(e){this.setTreeNodeState({state:"checkNode",nodeId:e})}},{key:"uncheckTreeNode",value:function(e){this.setTreeNodeState({state:"uncheckNode",nodeId:e})}},{key:"checksubTreeNode",value:function(e,t){var n=t.nodes,i=[];n&&n.length&&(i=this.iteratorCheckChildNodes(n)),i.length&&this.checkTreeNode(i)}},{key:"unchecksubTreeNode",value:function(e,t,n){var r=t.nodes,a=[];r&&(a=this.iteratorCheckChildNodes(r));var o=this.uncheckParentTreeNode(t,n);o=void 0===o?[]:o;var c=[].concat(i(a),i(o));this.uncheckTreeNode(c)}},{key:"uncheckParentTreeNode",value:function(e,t){if(t)return this.getAncestorsNodeId(e)}},{key:"getParentNode",value:function(e){return void 0!==e.parentId&&this.$elem.treeview("getNode",e.parentId)}},{key:"getAncestorsNodeId",value:function(e){for(var t=[],n=e;void 0!==n.parentId;)n=this.$elem.treeview("getNode",n.parentId),t.push(n.nodeId);return t}},{key:"getAncestorsNode",value:function(e){for(var t=[],n=e;void 0!==n.parentId;)n=this.$elem.treeview("getNode",n.parentId),t.push(n);return t}},{key:"iteratorCheckChildNodes",value:function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:[],n=!0,i=!1,r=void 0;try{for(var a,o=e[Symbol.iterator]();!(n=(a=o.next()).done);n=!0){var c=a.value;c&&(t.push(c.nodeId),c.nodes&&c.nodes.length&&t.concat(this.iteratorCheckChildNodes(c.nodes,t)))}}catch(e){i=!0,r=e}finally{try{!n&&o.return&&o.return()}finally{if(i)throw r}}return t}}]),e}();t.default=o}});