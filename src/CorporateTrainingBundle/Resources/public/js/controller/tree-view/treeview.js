define(function (require, exports, module) {
  require('treeview');
  exports.run = function () {
    var tree = [];
    var domWrapperName = null;
    var treeId = '';
    var inputData = '';
    var height = 240;
    var treeData;
    var showCheckbox;
    var org;
    $('.tree-wrapper').each(function (index, elm) {
      domWrapperName = $(this).data('id');
      height = $(this).data('width');
      showCheckbox = $(this).data('flag') ? true : false;
      treeId = '#treeview-checkable-' + domWrapperName;
      inputData = '#input-data-' + domWrapperName;
      treeData = $(inputData).val() && JSON.parse($(inputData).val());
      org = showCheckbox ? $('#' + domWrapperName).find('.treeview-val').val() : undefined;
      init(treeData, org);
      treeTable({
        data: treeData || tree,
        onhoverColor: '',
        height: height,
        treeId: treeId,
        domWrapperName: domWrapperName,
        showCheckbox: showCheckbox
      });
    });

    function init(treeData, org) {
      if (org === undefined) {
        _initSingle(treeData);
        return
      } 
       if(org === '') {
        _disabled(treeData);
        return
      }

      org = org.split(',');
      _disabled(treeData);
      _initMulti(treeData, org, true)
    }

    function _initMulti(treeData, orgData, flag) {
      orgData.forEach(function (item) {
        treeData.forEach(function (node) {
          if (node.orgCode === item) {
            node.state = { checked: flag}
          };
          if (node.nodes && node.nodes.length) {
            _initMulti(node.nodes, orgData, flag)
          } else {
            delete node.nodes
          }
        })
      });
    }

    function _initSingle(treeData) {
      treeData.forEach(function (node) {
        if (node.nodes && node.nodes.length) {
           selectable(node)
          _initSingle(node.nodes)
        } else {
          delete node.nodes
        }
      })
    }
    
    function _disabled(treeData) {
      treeData.forEach(function(node) {
        selectable(node)
        _disabled(node.nodes);
      })
      return treeData;
    }
    
    function selectable(node) {
      node.state = {};
      if (node.selectable != undefined && !node.selectable) {
        node.state.disabled = true;
        node.state.checked = false;
        node.state.expanded = false;
      }
       node.state.selected = false;
    }

    function treeTable(o) {
      var $checkableTree;
      $checkableTree = $(o.treeId).css({ height: 'auto', maxHeight: o.height}).treeview({
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
          _singleGetChecked(node.name, node.orgCode)
        },
        onNodeUnselected: function (event, node) {
          _singleGetChecked(node.name, node.orgCode)
        },
        onNodeChecked: function (event, node) {
          _multipleGetChecked();
        },
        onNodeUnchecked: function (event, node) {
          _multipleGetChecked();
        }
      });
      var findSelectableNodes = function (val) {
        return $checkableTree.treeview('search', [val, {
          ignoreCase: true,
          exactMatch: true,
          revealResults: true
        }]);
      };
      var search = function (e) {
        if (o.showCheckbox) {
          $('#search-wrapper-' + o.domWrapperName).css({ position: 'static'})
        }
        var pattern = o.showCheckbox ? $('#input-search-' + o.domWrapperName).val() : $('#' + o.domWrapperName).find('.input-check-node').val();
        var options = {
          ignoreCase: false,
          exactMatch: false,
          revealResults: false
        };
        var results = $checkableTree.treeview('search', [pattern, options]);
        var output = '<ul>';
        $.each(results, function (index, result) {
          output += '<li>'+result.name+'</li>';
        });
        output += '</ul>'
        $('#checkable-output-' + o.domWrapperName).show().html(output);
        $(o.treeId).hide();
      }
      $('#checkable-output-' + o.domWrapperName).on('click', function (e) {
        if (e.target.nodeName.toLowerCase() === 'li') {
          if (o.showCheckbox) {
            $checkableTree.treeview('checkNode', [findSelectableNodes(e.target.innerHTML), {
              silent: false
            }]);
            $('#input-search-' + o.domWrapperName).val('')
          } else {
            $checkableTree.treeview('uncheckAll', {
              silent: false
            });
            $checkableTree.treeview('selectNode', [findSelectableNodes(e.target.innerHTML), {
              silent: false
            }]);
          }
          $('#checkable-output-' + o.domWrapperName).hide();
          $('#input-search-' + o.domWrapperName).focus();
        }
      })

      function _singleGetChecked(name, orgCode) {
        if (o.showCheckbox) {
          return;
        }
        $('#input-check-node-' + o.domWrapperName).val(name).focus();
        $('#' + o.domWrapperName).find('.treeview-val').val(orgCode);
        $(o.treeId).hide();
      }

      function _multipleGetChecked() {
        var nameArray = [];
        var orgCodeArray = [];
        var orgIdArray = [];
        var checkedNodes = $(o.treeId).treeview('getChecked');

        if (checkedNodes) {
          checkedNodes.forEach(function(node) {
            nameArray.push("<div class='tag'><span class='glyphicon glyphicon-remove' data-code=" +node.orgCode +" data-node="+node.nodeId+"></span><span>"+node.name+"</span></div>");
            orgCodeArray.push(node.orgCode);
            orgIdArray.push(node.id);
          })
        }
        $('#input-check-node-' + o.domWrapperName).html(nameArray.join(''));
        $('#' + o.domWrapperName).find('.treeview-val').val(orgCodeArray.join(','));
        $('#' + o.domWrapperName).find('.treeview-val-ids').val(orgIdArray.join(','));
        $('#input-search-' + o.domWrapperName).focus();
      }

      function _initializeTreeListTags() {
        var nameArray = [];
        var checkedNodes = $(o.treeId).treeview('getChecked');
        if (checkedNodes.length) {
          checkedNodes.forEach(function(node) {
            nameArray.push("<div class='tag'><span class='glyphicon glyphicon-remove' data-code=" +node.orgCode+" data-node="+node.nodeId+"></span><span>"+node.name+"</span></div>");
          })
        }
        $('#input-check-node-' + o.domWrapperName).html(nameArray.join(''));
      }
      $('#input-check-node-' + o.domWrapperName).on('click', function (e) {
        e.stopPropagation();
        e.preventDefault();
        searchFoucs();
        if ($(e.target).hasClass('glyphicon')) {
          var arr = [];
          var nodeId = $(e.target).data('node');
          $checkableTree.treeview('uncheckNode', [nodeId, {
            silent: false
          }]);
          e.stopPropagation();
          e.preventDefault();
          $(e.target).parent().remove();
          var checkedNodes = $(o.treeId).treeview('getChecked');
          checkedNodes.forEach(function(code) {
            arr.push(code.orgCode)
          })
          $('#' + domWrapperName).find('.treeview-val').val(arr.join(','))
        }
      }).on('keyup', search)

      function validateTree(type) {
        var $input = $('#input-check-node-' + o.domWrapperName);
        if (type === 'leave') {
          if (o.showCheckbox && !$input.children().length && !$('#input-search-' + o.domWrapperName).is(':hidden')) {
            $input.addClass('has-error');
            $input.parent().parent().parent().removeClass('in-focus').addClass('has-error');
            $('#help-block-' + o.domWrapperName).show();
          } else if (!o.showCheckbox && !$input.val()) {
            $input.addClass('has-error');
            $input.parent().parent().removeClass('in-focus').addClass('has-error');
          }
          $(o.treeId).hide();
          $('#input-search-' + o.domWrapperName).hide();
          $('#checkable-output-' + o.domWrapperName).hide();
        } else {
          $input.removeClass('has-error');
          $('#help-block-' + o.domWrapperName).hide();
          if (o.showCheckbox) {
            $input.parent().parent().parent().removeClass('has-error').addClass('in-focus')
          } else {
            $input.parent().parent().removeClass('has-error').addClass('in-focus')
          }
        }
        $('#search-wrapper-' + o.domWrapperName).css({ borderColor: 'transparent'})
      }

      function searchFoucs() {
        validateTree('enter');
        $('#checkable-output-' + o.domWrapperName).hide();
        $('#input-search-' + o.domWrapperName).show();
        $(o.treeId).show();
        if (o.showCheckbox) {
          $('#search-wrapper-' + o.domWrapperName).css({
            position: 'absolute',
            borderColor: '#ccc'
          })
        }
      }

      $('#' + o.domWrapperName).on('click', function (e) {
        e.stopPropagation();
        e.preventDefault();
        if (e.target.nodeName.toLowerCase() === 'li') {
          $(o.treeId).show();
        }
      })

      $('#input-search-' + o.domWrapperName).on('focus', function () {
        searchFoucs();
      }).on('keyup', search)
      $(document).on('click', function (e) {
        validateTree('leave')
      })
      if (o.showCheckbox) {
        _initializeTreeListTags();
        $(o.treeId).css({ position: 'static'})
      }
      $('form').on('submit', function () {
        var $input = $('#input-check-node-' + o.domWrapperName);
        if (o.showCheckbox && !$input.children().length) {
          $input.addClass('has-error');
          $input.parent().parent().parent().removeClass('in-focus').addClass('has-error');
          $('#help-block-' + o.domWrapperName).show();
        } else if (!o.showCheckbox && !$input.val()) {
          $input.addClass('has-error');
          $input.parent().parent().removeClass('in-focus').addClass('has-error');
        }
      })
    }
  };
});