function form_submit (form, url, top) {
  var name = form.filled_in_form.value;
  jQuery('#form_status_' + name).show ();
  jQuery.post (url, jQuery(form).serialize (), function (response) {
    jQuery('#form_status_' + name).hide ();
    jQuery('#filled_in_wrap_' + name).html(response);
    if (top)
      Element.scrollTo ('filled_in_wrap_' + name);
  });
}