export const buyBtn  = ($element) => {
  $element.on('click', event => {
    if(!$element.hasClass('disable')) {
      $.post($(event.currentTarget).data('url'), resp => {
        if (typeof resp === 'object') {
          window.location.href = resp.url;
        } else {
          $('#modal').modal('show').html(resp);
        }
      });
    }
  });
};
