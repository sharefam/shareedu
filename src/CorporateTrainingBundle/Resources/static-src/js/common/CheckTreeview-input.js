import CheckTreeview from './CheckTreeview';


export default class CheckTreeviewInput {

  static getDefaultOpts() {

    return {
      treeviewText: '.js-treeview-text',
      treeviewSelect: '.js-treeview-select',
      treeviewSelectMenu: '.js-treeview-select-menu',
      treeviewIpt: '.js-treeview-ipt',
      treeviewData: '.js-treeview-data',
      disableNodeCheck: false,
      saveColumn: 'orgCode',
      showCheckbox: true,
      transportChildren: false,
      unCheckParent: false,
    };
  }

  constructor(opts) {

    if ('single' == opts.selectType) {
      opts.showCheckbox = false;
    }

    Object.assign(this, CheckTreeviewInput.getDefaultOpts(), opts);

    if (!this.$elem.length) {
      return;
    }

    if ('single' == this.selectType) {
      this.disableNodeCheck = true;
    }

    this.init();
  }

  init() {
    this.initTreeview();
    this.initEvent();
    this.nodeSelect();
  }

  initTreeview() {
    let data = this.getData() ? this.getData() : '{}';
    let nodeArr = JSON.parse(data);
    let checkStr = this.$elem.find(this.treeviewIpt).val() ? this.$elem.find(this.treeviewIpt).val() : '';
    let columnName = this.$elem.data('checkColumnName');
    let checkArr = checkStr.split(',');
    let checkMap = {};
    let tmpArr = [].concat(nodeArr);

    for (let i = 0; i < checkArr.length; i++) {
      checkMap[checkArr[i]] = true;
    }

    while (tmpArr.length > 0) {
      let node = tmpArr.pop();

      node.state = {
        expanded: false
      };

      if (checkMap[node[columnName]]) {
        node.state.checked = true;
      }

      if (node.selectable != undefined && !node.selectable) {
        node.state.disabled = true;
        node.state.checked = false;
        node.hideCheckbox = true;
      }

      // 不传输的节点，设置字体颜色，背景色
      if (node.exclude) {
        node.state.color = '#0093FF';
        node.state.backColor = '#F5F5F5';
        node.hideCheckbox = true;
      }

      if (node.expanded) {
        node.state.expanded = true;
        node.hideCheckbox = true;
      }

      node.state.selected = false;

      if (node.nodes) {
        tmpArr = tmpArr.concat(node.nodes);
      }
    }

    this.checkTreeview = new CheckTreeview(this.$elem.find(this.treeviewSelectMenu), {
      data: nodeArr,
      disableNodeCheck: this.disableNodeCheck,
      showCheckbox: this.showCheckbox,
      unCheckParent: this.unCheckParent,
    });

    const node = this.checkTreeview.getCheckNodes();

    if (node.length) {
      let name = node.reduce(function (tot, item) {
        return tot + (tot && ',') + item.name + ' ';
      }, '');
      this.$elem.find(this.treeviewText).val(name);
    }
  }

  initEvent() {
    const _self = this;

    this.$elem.on('focus', _self.treeviewText, (e) => this.focusHandler(e));
    this.$elem.find(_self.treeviewSelect).on('click', (e) => this.clickHandler(e));
    $(document).on('click', 'body', (e) => this.hideEvent(e));
  }

  clickHandler(e) {
    const _self = this;
    const node = _self.checkTreeview.getCheckNodes();

    let name = '';

    let len = Math.min(node.length, 10);

    for (let i = 0; i < len; i++) {
      if (!node[i]['disable'] && !node[i]['exclude']) {
        name = name + (name && ',') + node[i].name + ' ';
      }
    }

    if (len != node.length) {
      name = name + '...';
    }

    let $queryIds = [];
    let id = node.reduce(function (tot, item) {
      if (!item['disable'] && !item['exclude']) {
        console.log(_self.transportChildren);
        if ($queryIds.indexOf(item['parentId']) >= 0 && !_self.transportChildren) {
          console.log(item['nodeId']);
          $queryIds.push(item['nodeId']);
          return tot;
        }

        $queryIds.push(item['nodeId']);
        return tot + (tot && ',') + item[_self.saveColumn];
      }

      return tot;

    }, '');

    $(e.currentTarget).find(_self.treeviewText).val(name);
    $(e.currentTarget).find(_self.treeviewIpt).val(id);
    e.stopPropagation();

  }

  focusHandler(e) {
    const _self = this;

    $(_self.treeviewSelectMenu).removeClass('is-active');
    $(e.currentTarget).parents(_self.treeviewSelect).find(_self.treeviewSelectMenu).addClass('is-active');

  }

  nodeSelect() {

    if ('single' == this.selectType) {
      this.singleSelect();

      return;
    }

    this.multipleSelect();
  }

  singleSelect() {
    this.$elem.on('nodeElementSelect', (e, node) => {

      if (!node.selectable) {
        return;
      }

      if (node.exclude) {
        return false;
      }

      let tree = this.checkTreeview.getTreeObject();
      tree.uncheckAll();
      this.$elem.find(this.treeviewIpt).val('');
      tree.checkNode(node.nodeId);
      $(this.treeviewSelectMenu).removeClass('is-active');
    });
  }

  multipleSelect() {

    this.$elem.on('nodeElementSelect', (e, node) => {

      if (!node.selectable) {
        return;
      }

      const tree = this.checkTreeview.getTreeObject();

      this.multipleSelectToggle(e, node, tree);

    });
  }

  multipleSelectToggle(e, node, tree) {
    const checkNode = CheckTreeviewInput.getSelectNode(node);

    if (!checkNode) {
      return;
    }

    if (checkNode.state && checkNode.state.checked) {
      this.checkTreeview.unchecksubTreeNode(e, node);
      if (!checkNode.exclude) this.checkTreeview.uncheckTreeNode(node);
      return;
    }

    this.checkTreeview.checksubTreeNode(e, node);
    if (!checkNode.exclude) this.checkTreeview.checkTreeNode(node);
    tree.expandNode(node.nodeId);
  }

  static getSelectNode(node) {

    if (!node) {
      return null;
    }

    return (!node.exclude) ? node : node.nodes && node.nodes.length ? node.nodes[0] : null;
  }

  getData() {
    let text = this.$elem.find(this.treeviewData).text();

    return text ? text : this.$elem.find(this.treeviewData).val();
  }

  hideEvent(e) {
    $('.js-treeview-select-menu.is-active').each(function (e, elment) {
      $(elment).removeClass('is-active').closest('.js-treeview-select-wrap').trigger('treeHide');
    });
  }
}