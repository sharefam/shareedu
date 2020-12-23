import OrgCheckTreeviewInput from '../../../org-select/Org-CheckTreeview-input';
window.$.permissionCheckTreeviewInput = OrgCheckTreeviewInput;
new window.$.permissionCheckTreeviewInput({
  $elem: $('#setOrgSelect'),
  disableNodeCheck: false,
  saveColumn: 'id',
  transportParent: true,
});
