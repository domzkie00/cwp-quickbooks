jQuery(function($){
	var invoice_table = $('#quickbooks-invoices-table');

	$(document).on('click', '.tab-link', function(){
		var target = $(this).attr('data-target');

		$('.tab-content').hide();
		$('.tab-link').removeClass('active');
		$(this).addClass('active');
		$('.tab-content[data-target="'+target+'"]').show();
	});

	$(document).ready(function() {

	});
});