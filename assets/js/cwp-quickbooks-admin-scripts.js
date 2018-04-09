jQuery(function($){
	$(document).on('change', '#integration-select-type', function(){
        if($(this).val() == 'quickbooks') {
        	var app_token = $(this).find(':selected').attr('data-key');
            $('#integration-select-folder').hide();
        }
    });

    $(document).ready(function() {
		if($('#integration-select-type').val() == 'quickbooks') {
			$('#integration-select-folder').hide();
		}
	});
});