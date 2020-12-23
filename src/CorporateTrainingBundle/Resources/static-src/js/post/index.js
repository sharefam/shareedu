$('[data-role="tree-select"], [name="categoryId"]').select2({
  treeview: true,
  dropdownAutoWidth: true,
  treeviewInitState: 'collapsed',
  placeholderOption: 'first',
  allowClear: true,
});
