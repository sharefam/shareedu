let tree = [];
let domWrapperName = null;
let treeId = '';
let inputData = '';
let height = 240;
let treeData;
let showCheckbox;
let org;
$('.tree-wrapper').each(function (index, elm) {
  domWrapperName = $(this).data('id');
  height = $(this).data('width');
  showCheckbox = $(this).data('flag') ? true : false;
  treeId = '#treeview-checkable-' + domWrapperName;
  inputData = '#input-data-' + domWrapperName;
  treeData = $(inputData).val() && JSON.parse($(inputData).val());
  org = showCheckbox ? $('#' + domWrapperName).find('.treeview-val').val(): undefined;
  init(treeData,org);
  treeTable({
    data: treeData || tree,
    onhoverColor: '',
    height,
    treeId,
    domWrapperName,
    showCheckbox
  });
});
function init(treeData,org){
  if(!org){
    _initSingle(treeData);
    return;
  }
  org = org.split(',');
  _initMulti(treeData,org,true);
}
function _initMulti(treeData,orgData,flag){
  for (let org of orgData) {
    for (let data of treeData) {
      if(data.orgCode===org) {
        data.state = { checked:flag };
      }
      if(data.nodes&&data.nodes.length) {
        _initMulti(data.nodes,orgData,flag);
      }else {
        delete data.nodes;
      }
    }
  }
}
function _initSingle(treeData) {
  for (let data of treeData) {
    if(data.nodes&&data.nodes.length) {
      _initSingle(data.nodes);
    }else {
      delete data.nodes;
    }
  } 
}
function treeTable(o) {
  let $checkableTree;
  $checkableTree = $(o.treeId).css({height: 'auto'}).treeview({
    data: o.data,
    showIcon: false,
    showCheckbox: o.showCheckbox,
    searchResultColor: '',
    levels: 1,
    multiSelect: true,
    selectedBackColor: 'undefined',
    expandIcon: 'es-icon es-icon-tree_switcher_open',
    collapseIcon: 'es-icon es-icon-tree_switcher_close',
    emptyIcon: 'glyphicon',
    nodeIcon: '',
    selectedIcon: '',
    checkedIcon: 'es-icon es-icon-tree_check',
    uncheckedIcon: 'es-icon es-icon-tree_checkbox',
    onhoverColor: o.onhoverColor || '#F5F5F5',
    selectedColor: '',
    onNodeSelected: function (event, node) {
      _singleGetChecked(node.name, node.orgCode);
      $(o.treeId).hide();
    },
    onNodeUnselected: function (event, node) {
      _singleGetChecked(node.name, node.orgCode);
    },
    onNodeChecked: function (event, node) {
      _multipleGetChecked();
    },
    onNodeUnchecked: function (event, node) {
      _multipleGetChecked();
    }
  });
  let findSelectableNodes = function (val) {
    return $checkableTree.treeview('search', [val, { ignoreCase: true, exactMatch: true, revealResults: true }]);
  };
  let search = function (e) {
    if(o.showCheckbox){
      $('#search-wrapper-' + o.domWrapperName).css({position:'static'});
    }  
    let pattern = o.showCheckbox ? $('#input-search-' + o.domWrapperName).val() : $('#' + o.domWrapperName).find('.input-check-node').val();
    let options = {
      ignoreCase: false,
      exactMatch: false,
      revealResults: false
    };
    let results = $checkableTree.treeview('search', [pattern, options]);
    let output = '<ul>';
    $.each(results, function (index, result) {
      output += `<li>${result.name}</li>`;
    });        
    output += '</ul>';
    $('#checkable-output-' + o.domWrapperName).show().html(output);
    $(o.treeId).hide();  
  };
  $('#checkable-output-' + o.domWrapperName).on('click', function (e) {
    if(e.target.nodeName.toLowerCase() === 'li'){
      if (o.showCheckbox) {     
        $checkableTree.treeview('checkNode', [findSelectableNodes(e.target.innerHTML), { silent: false }]); 
        $('#input-search-' + o.domWrapperName).val('');             
      }else{
        $checkableTree.treeview('uncheckAll', { silent: false });
        $checkableTree.treeview('selectNode', [findSelectableNodes(e.target.innerHTML), { silent: false }]);  
      }
      $('#checkable-output-' + o.domWrapperName).hide();
      $('#input-search-' + o.domWrapperName).focus();
    }
  });
  function _singleGetChecked(name, orgCode) {
    if (o.showCheckbox) {
      return;
    }
    $('#input-check-node-' + o.domWrapperName).val(name).focus();
    $('#' + o.domWrapperName).find('.treeview-val').val(orgCode);
  }
  function _multipleGetChecked() {
    let nameArray = [];
    let orgCodeArray = [];
    let orgIdArray = [];
    let checkedNodes = $(o.treeId).treeview('getChecked');

    if (checkedNodes) {
      for (let node of checkedNodes) {
        nameArray.push(`<div class='tag'><span class='glyphicon glyphicon-remove' data-code=${node.orgCode} data-node=${node.nodeId}></span><span>${node.name}</span></div>`);
        orgCodeArray.push(node.orgCode);
        orgIdArray.push(node.id);
      }
    }
    $('#input-check-node-' + o.domWrapperName).html(nameArray.join(''));
    $('#' + o.domWrapperName).find('.treeview-val').val(orgCodeArray.join(','));
    $('#' + o.domWrapperName).find('.treeview-val-ids').val(orgIdArray.join(','));
    $('#input-search-' + o.domWrapperName).focus();
  }
  function _initializeTreeListTags() {
    let nameArray = [];
    let checkedNodes = $(o.treeId).treeview('getChecked');
    if (checkedNodes.length) {
      for (let node of checkedNodes) {
        nameArray.push(`<div class='tag'><span class='glyphicon glyphicon-remove' data-code=${node.orgCode} data-node=${node.nodeId}></span><span>${node.name}</span></div>`);
      }
    }
    $('#input-check-node-' + o.domWrapperName).html(nameArray.join(''));
  }
  $('#input-check-node-' + o.domWrapperName).on('click', function(e){
    e.stopPropagation();
    e.preventDefault();
    searchFoucs();
    if($(e.target).hasClass('glyphicon')){
      let arr = [];
      let nodeId = $(e.target).data('node');
      $checkableTree.treeview('uncheckNode', [nodeId, { silent: false }]);
      e.stopPropagation();
      e.preventDefault();
      $(e.target).parent().remove();
      let checkedNodes = $(o.treeId).treeview('getChecked');
      for (let code of checkedNodes){
        arr.push(code.orgCode);
      }
      $('#' + domWrapperName).find('.treeview-val').val(arr.join(','));
    }        
  }).on('keyup', search);
  
  function validateTree(type){
    let $input = $('#input-check-node-' + o.domWrapperName);  
    if(type === 'leave'){
      if (o.showCheckbox && !$input.children().length && !$('#input-search-' + o.domWrapperName).is(':hidden')){
        $input.addClass('has-error');
        $input.parent().parent().parent().removeClass('in-focus').addClass('has-error');
        $('#help-block-' + o.domWrapperName).show();

  
      }else if(!o.showCheckbox && !$input.val()){
        $input.addClass('has-error');
        $input.parent().parent().removeClass('in-focus').addClass('has-error');
      } 
      $(o.treeId).hide();
      $('#input-search-' + o.domWrapperName).hide();
      $('#checkable-output-' + o.domWrapperName).hide();

    }else{
      $input.removeClass('has-error');
      $('#help-block-' + o.domWrapperName).hide();   
      if (o.showCheckbox){
        $input.parent().parent().parent().removeClass('has-error').addClass('in-focus');
      }else{
        $input.parent().parent().removeClass('has-error').addClass('in-focus');
      } 
    }
    $('#search-wrapper-' + o.domWrapperName).css({ borderColor:'transparent'});

  }
  function searchFoucs(){
    validateTree('enter');
    $('#checkable-output-' + o.domWrapperName).hide();
    $('#input-search-' + o.domWrapperName).show();
    $(o.treeId).show();
    if(o.showCheckbox){
      $('#search-wrapper-' + o.domWrapperName).css({position:'absolute', borderColor:'#ccc'});
    } 
  }
  $('#' + o.domWrapperName).on('click', function (e) {
    e.stopPropagation();
    e.preventDefault();
    if(e.target.nodeName.toLowerCase()==='li'){
      $(o.treeId).show();
    } 
    let $firstExtend = $(o.treeId).find('.list-group').children(':first').children().eq(0);
    if($firstExtend.hasClass('es-icon-tree_switcher_open')){
      $(o.treeId).css({height: 'auto'});
    }else if($firstExtend.hasClass('es-icon-tree_switcher_close')){
      let height = $(o.treeId).find('.list-group-item').length * 45;
      $(o.treeId).css({height});
      $(o.treeId).css({maxHeight: o.height});
    }
  });
  $('#input-search-' + o.domWrapperName).on('focus', function(){
    searchFoucs(); 
  }).on('keyup', search);
  $(document).on('click', function (e) {
    validateTree('leave');
  });
  if (o.showCheckbox) {
    _initializeTreeListTags();
    $(o.treeId).css({position:'static'});
  }
  $('form').on('submit',function(){
    let $input = $('#input-check-node-' + o.domWrapperName);  
    if (o.showCheckbox && !$input.children().length){
      $input.addClass('has-error');
      $input.parent().parent().parent().removeClass('in-focus').addClass('has-error');
      $('#help-block-' + o.domWrapperName).show();
    }else if(!o.showCheckbox && !$input.val()){
      $input.addClass('has-error');
      $input.parent().parent().removeClass('in-focus').addClass('has-error');
    } 
  });
}
