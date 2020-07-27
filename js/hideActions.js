CRM.$(function($) {
  $('.crm-contact_activities-list').children().children().each(function( index ) {
    var id = $(this).attr('class').split('_')[1];
    if ($.inArray(id, CRM.vars.hideActions) > -1) {
      $(this).hide();
    }
  });
});
