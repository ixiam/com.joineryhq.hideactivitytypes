CRM.$(function($) {
  $('a[title="Activities"]').click(function() {
    $(document).ajaxSend(function() {
      $('.action-link.crm-activityLinks select').children().each(function( index ) {
        var id = $(this).attr('value').split('atype=')[1];
        if ($.inArray(id, CRM.vars.hideActivities) > -1) {
          $(this).remove();
        }
      });
    });
    $('.crm-select2').one().trigger('change');
  });
});
