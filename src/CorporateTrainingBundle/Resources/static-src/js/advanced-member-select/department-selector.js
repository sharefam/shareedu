import Treeview from '../common/Treeview';

class CheckTreeview extends Treeview {
  static getDefaultOpts() {
    return {
      showIcon: false,
      showCheckbox: true,
      highlightSelected: false,
    };
  }

  constructor($elem, opts) {
    super();
    
    let defaultopts = CheckTreeview.getDefaultOpts();    
    this.$elem = $elem;
    opts = Object.assign({}, defaultopts, opts);
    this.itemType = 'org';
    this.init(opts);
    this.selectItemArr = [];
  }

  init(opts) {
    this.initEvent(opts);
    this.$elem.treeview(opts);
  }

  initEvent(opts) {
    opts.onNodeChecked = (e, node) => { this.OnNodeChecked(e, node); this.addItems(); };
    opts.onNodeUnchecked = (e, node) => { this.OnNodeUnChecked(e, node); this.removeItems(); };
  }

  setOpts({ addCb = function() {}, removeCb = function() {} }) {
    this.addCb = addCb;
    this.removeCb = removeCb;
  }

  OnNodeChecked(e, node) {
    this.checksubTreeNode(e, node);
  }

  OnNodeUnChecked(e, node) {
    this.unchecksubTreeNode(e, node);
  }

  getUniqueItems() {
    let selectNodeArr = this.getCheckNodes();
    let map = {};
    let t = [];
    let arr1, arr2;

    if (selectNodeArr.length > this.selectItemArr.length) {
      arr1 = this.selectItemArr;
      arr2 = selectNodeArr; 
    } else {
      arr1 = selectNodeArr;
      arr2 = this.selectItemArr;
    }

    for (let i = 0; i < arr1.length; i++) {
      map[arr1[i].id] = true;
    }

    for (let i = 0; i < arr2.length; i++) {
      if (!map[arr2[i].id]) {
        t.push(arr2[i]);
      }
    }

    return t;
  }

  addItems() {
    let selectNodeArr = this.getCheckNodes();
    let t = this.getUniqueItems();

    this.selectItemArr = selectNodeArr;

    for (let i = 0; i < t.length; i++) {
      let obj = this.getItemObj(t[i]);
      this.addCb && this.addCb(obj);
    }
  }

  removeItem(node) {
    this.$elem.treeview('uncheckNode', [ node.nodeId, { silent: true } ]);
    this.selectItemArr = this.getCheckNodes();
  }

  removeItems() {
    let selectNodeArr = this.getCheckNodes();
    let t = this.getUniqueItems();

    this.selectItemArr = selectNodeArr;

    for (let i = 0; i < t.length; i++) {
      let obj = this.getItemObj(t[i]);
      this.removeCb && this.removeCb(obj);
    }
  }

  getItemObj(node) {
    return  {
      key: `icon-1/${node.id}`,
      item: node,
      id: node.id,
      type: this.itemType,
      iconClassName: 'es-icon-department_management',
      name: node.name
    };
  }

  getCheckNodes() {
    let checkNodes = this.$elem.treeview('getChecked');
    return checkNodes;
  }
}

export default class DepartmentSelector {
  constructor(selector) {
    this.$elem = $(selector);
    this.treeviewData = '.js-search-input';
    this.treeviewSelectMenu = '.js-search-by-department-menu';
    this.itemType = 'org';
    this.init();
  }

  init() {
    this.initEvent();
    this.initTreeview();
  }

  initEvent() {
    this.initSelect2();
  }

  initTreeview() {
    let t =this.getData();
    let nodeArr = JSON.parse(this.getData());
    let tmpArr = [].concat(nodeArr);

    while (tmpArr.length > 0) {
      let node = tmpArr.pop();

      node.state = {};

      if (node.selectable != undefined && !node.selectable) {
        node.state.disabled = true;
        node.state.checked = false;
        node.state.expanded = true;
      }

      node.state.selected = false;

      if(node.nodes) {
        tmpArr = tmpArr.concat(node.nodes);
      }
    }

    this.checkTreeview = new CheckTreeview(this.$elem.find(this.treeviewSelectMenu), { data: nodeArr });
  }

  getData() {
    return this.$elem.find(this.treeviewData).val();
  }

  setOpts(opts) {
    this.checkTreeview.setOpts(opts);
  }

  removeItem(node) {
    this.checkTreeview.removeItem(node);
  }

  addItems(nodes) {
    for(let i = 0; i < nodes.length; i++) {
      this.checkTreeview.$elem.treeview('checkNode', [ nodes[i].nodeId, { silent: true } ]);
    }
    
    this.checkTreeview.addItems();
  }

  initSelect2() {
    let that = this;
    let $orgContainer = $('#orgModalInput');
    let dataSource = [];
    let nodeArr = JSON.parse(this.getData());
    let tmpArr = [].concat(nodeArr);
    while (tmpArr.length > 0) {
      let node = tmpArr.pop();
      if(node.selectable == true){
        dataSource.push({id: node.id, text: node.name + ' ' + '(' + node.code + ')'});
      }
      if(node.nodes) {
        tmpArr = tmpArr.concat(node.nodes);
      }
    }

    let $select = $orgContainer.select2({
      data: dataSource,
      formatSelection: function (item) {
        return item.text;
      },
      formatResult: function (item) {
        return item.text;
      },
      formatNoMatches: function() {
        return Translator.trans('advanced_user_select.not_found_hint');
      },
      maximumSelectionSize: 20,
      allowClear: true,
      width: 'off',
      placeholder: Translator.trans('advanced_user_select.department_input'),
      multiple: true
    });
    $select.on('change', (e) => {
      let t = that.checkTreeview.$elem.treeview('getUnselected');
      let tempArr = [];
      let result = e.added.id;
      for (let i = 0; i < t.length; i++) {
        if (t[i].id == result) {
          tempArr.push(t[i]);
        }
      }
      that.addItems(tempArr);
      $select.select2('val', '');
    });
  }
}
