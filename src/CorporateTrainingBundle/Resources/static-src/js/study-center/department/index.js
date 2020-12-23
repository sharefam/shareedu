const $postsContainer = $('#postId');
$postsContainer.select2({
  ajax: {
    url: $postsContainer.data('url'),
    dataType: 'json',
    quietMillis: 100,
    data: function (term, page) {
      return {
        q: term,
        page_limit: 10
      };
    },
    results: function (data) {
      let results = [];
      $.each(data, function (index, item) {

        results.push({
          id: item.id,
          name: item.name
        });
      });

      return {
        results: results
      };

    }
  },
  formatSelection: function (item) {
    return item.name;
  },
  formatResult: function (item) {
    return item.name;
  },
  initSelection: function(element, callback) {
    let id = $(element).val();
    if (id !== '') {
      let name = $(element).data('name');
      callback({id:id, name:name});
    }
  },
  placeholder: Translator.trans('study_center.department.all_post')
});
