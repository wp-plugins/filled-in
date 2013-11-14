function deleteItems (type,nonce) {
  var checked = jQuery('.item :checked');
  
  if (checked.length > 0) {
    if (confirm (wp_confirm_form)) {
      jQuery('#loading').show (); 
      jQuery.post (wp_base + '?id=0&cmd=' + type + '&_ajax_nonce=' + nonce, checked.serialize (), function () {
        jQuery('#loading').hide ();
        checked.each (function () {
          jQuery(this).parent ().parent ().remove ();
        });
      });
    }
   }
  return false;
}
// ===========
// Form list
// ===========
function delete_form (form){
  if (confirm (wp_confirm_form))  {
    jQuery('#loading').show ();
    jQuery.post(wp_base + '?id=' + form + '&cmd=delete_form', {}, function () {
      jQuery('#loading').hide ();
      jQuery('#form_' + form).remove ()
    });
  }
}

function DismissPostError( evt ){
   jQuery( "#loading" ).show();
   jQuery.post( wp_base + "?id=0&cmd=DismissPostError", {}, function(){
      jQuery( "#loading" ).hide();
      jQuery( "#post-error" ).hide();
   });
}

jQuery( document ).ready( function(){
   jQuery( "#post-error-dismiss" ).click( DismissPostError );
});

// ===========
// Email templates
// ===========

function cancel_template (name){
  jQuery ('#temp_' + name).load (wp_base + '?id=' + name + '&cmd=cancel_template', {}, function() {
    setupTemplates ();
  });
}

function setupTemplates (){
  jQuery('.filledin-template-edit').click (function ()  {
    jQuery('#loading').show ();
    jQuery(this).parent ().parent ().load (this.href, {}, function () {
      jQuery('#loading').hide ()
    });
    
    return false;
  });
}

// ===========
// Extensions
// ===========

function add_extension (item, group, form, nonce){
  jQuery('#ext_loading_' + group).show ();
  jQuery('#extension_' + group).load (wp_base + '?id=' + item + '&cmd=extension_add&group=' + group + '&_ajax_nonce=' + nonce, { type: jQuery('#add_type_' + group).val()},
      function () { jQuery('#ext_loading_' + group).hide ();});
    
  return false;
}

function delete_extension (item,group, nonce){
  if (confirm (wp_confirm_ext)) {
    jQuery('#ext_loading_' + group).show ();
    jQuery.post(wp_base + '?id=' + item + '&cmd=extension_delete&_ajax_nonce=' + nonce, {}, function(){
      jQuery('#ext_loading_' + group).hide ();
      jQuery('#ext_' + item).hide ();
    });
  }
  
  return false;
}

function edit_extension (item,group){
  jQuery('#ext_loading_' + group).show ();
  jQuery('#ext_' + item).load (wp_base + '?id=' + item + '&cmd=extension_edit', {}, function () {
    jQuery('#ext_loading_' + group).hide ();
  });
  
  return false;
}

function update_extension (item, group, obj){
  jQuery('#ext_loading_' + group).show ();
  jQuery.post (wp_base + '?id=' + item + '&cmd=extension_save', jQuery(obj).serialize (), function(response) {
    jQuery('#ext_' + item).html (response);
    jQuery('#ext_loading_' + group).hide ();
  });
  
  return false;
}

function disable_extension (item,group,nonce){
  jQuery('#ext_loading_' + group).show ();
  jQuery('#ext_' + item).load(wp_base + '?id=' + item + '&cmd=extension_disable&_ajax_nonce=' + nonce, {}, function() {
    jQuery('#ext_' + item).removeClass ().addClass ('disabled');
    jQuery('#ext_loading_' + group).hide ();
  });
  
  return false;
}

function enable_extension (item,group,nonce){  
  jQuery('#ext_loading_' + group).show ();  
  jQuery('#ext_' + item).load(wp_base + '?id=' + item + '&cmd=extension_enable&_ajax_nonce=' + nonce, {}, function()    {   
    jQuery('#ext_' + item).removeClass ();
    jQuery('#ext_loading_' + group).hide ();
  });
  return false;
}

function show_extension (item,group){  
  jQuery('#ext_loading_' + group).show (); 
  jQuery('#ext_' + item).load(wp_base + '?id=' + item + '&cmd=extension_show', {}, function()    {    
    jQuery('#ext_' + item).removeClass ();      jQuery('#ext_loading_' + group).hide ();
  });  
  return false;
}

function save_extension_order (group){
  jQuery('#ext_loading_' + group).show ();
  jQuery.post(wp_base + '?id=' + group + '_list&cmd=extension_order', jQuery('#' + group + '_list').sortable('serialize'), function()  {  
    jQuery('#ext_loading_' + group).hide ();
  });
}
                        
function refresh_files (template){  
  jQuery('#files_' + template).load (wp_base + '?id=' + template + '&cmd=refresh_files');
}

function delete_file (template, file){  
  if (confirm (wp_confirm_file))  
    jQuery('#files_' + template).load (wp_base + '?id=' + template + '&cmd=delete_file&file=' + file);
}
      