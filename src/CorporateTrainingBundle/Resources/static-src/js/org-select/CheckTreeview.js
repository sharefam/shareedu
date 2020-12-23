import Treeview from '../common/CheckTreeview';


export default class CheckTreeview extends Treeview {

  OnNodeUnChecked(e, node) {
    this.UnCheckedParentLinkTreeNode(node);

    this.unchecksubTreeNode(e, node);
  }
  UnCheckedParentLinkTreeNode(node){
    if (node.parentId !== undefined) {
      let parentNode = this.getParentNode(node);
      if(parentNode.selectable){
        this.$elem.treeview('uncheckNode', [parentNode, { silent: true }]);
        this.UnCheckedParentLinkTreeNode(parentNode);
      }

    }else{
      return;
    }

  }
}