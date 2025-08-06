
// doc ready
$(document).ready(function(){

	// toggle password
	$('body form').on('click', '.is-show-password-icon', function(){
		var el = $(this),
			inp = el.closest('.input-group').find('input');

		if(el.hasClass('is-visible')){
			el.removeClass('fa-eye fa-eye-slash is-visible').addClass('fa-eye-slash');

			inp.prop('type', 'password');
		}else{
			el.removeClass('fa-eye fa-eye-slash').addClass('fa-eye is-visible');

			inp.prop('type', 'text');
		}

	});

	// loop inputs
	$('body form').find('input, select, textarea').each(function(){
		var el = $(this);

		// check
		if(el.prop('required')){
			el.closest('.form-group').find('label:not(.is-req-label)').addClass('is-req-label').append(' <i>*</i>');
		}
	});

	// submit form
	$('body form').on('click', '[data-submit-form-with-params]', function(e){
		e.preventDefault();

		var el = $(this),
			frm = el.closest('form'),
			data = el.data('submit-form-with-params');

		// check
		if(frm.length > 0){
			frm.find('input.is-hidden-param').remove();

			// loop
			$.each(data, function(key, val){
				frm.append('<input type="hidden" name="' + key + '" value="' + val + '" class="is-hidden-param" />');
			});

			frm.trigger('submit');
		}
	});

	// auto select view permission
	$('body').on('click', '.is-security-group-system-access-item-row input', function(){
		var el = $(this),
			row = el.closest('.row');

		// checked
		if(el.is(':checked')){
			row.find('input[name*="[can_read]"]').prop('checked', true);
		}
	});

	// datepicker
	$('input.is-date, input[name*="date"]:visible').not('input[type="checkbox"], input[type="file"]').datepicker({
		format: "yyyy-mm-dd",
		weekStart: 1,
		clearBtn: true,
		orientation: "right auto",
		todayHighlight: true,
		autoclose: true
	}).on('hide', function(e) {

    });

	// add date icon
	$('input.is-date, input[name*="date"]:visible')
		.not('input[type="checkbox"], input[type="file"]')
		.wrap('<div class="input-group"></div>')
		.after('<span class="input-group-append"><span class="input-group-text"><i class="fas fa-calendar-alt"></i></span></span>');

	// check content tags
	if($('select.has-content-type-tags').length > 0) {
		// content types
		var contenTypes = new Bloodhound({
			datumTokenizer: Bloodhound.tokenizers.obj.whitespace('text'),
			queryTokenizer: Bloodhound.tokenizers.whitespace,
			prefetch: {
				url: '/master-data/tags/json-get-all',
				cache: false,
			}
		});
		// init
		contenTypes.initialize();

		// tags select
		$('select.has-content-type-tags').tagsinput({
			typeaheadjs: {
				name: 'tags',
				display: 'text',
				source: contenTypes.ttAdapter()
			}
		});
		$('body').bind('blur focusout', '.bootstrap-tagsinput input', function () {
			var ev = $.Event('keydown', {which: 13});

			$(this).trigger(ev);
		});
	}



});
