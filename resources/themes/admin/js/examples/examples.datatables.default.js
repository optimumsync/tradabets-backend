/*
Name: 			Tables / Advanced - Examples
Written by: 	Okler Themes - (http://www.okler.net)
Theme Version: 	2.1.1
*/

(function($) {

	'use strict';

	var datatableInit = function() {

		var table = $('#datatable-default').dataTable({
			dom: '<"row"<"col-lg-6"l><"col-lg-6"f>><"table-responsive"t>p',
			"lengthMenu": [ [25, 50, 100, 150, 250, -1], [25, 50, 100, 150, 250, "All"] ],
			"pageLength": 50,
			"orderCellsTop": true,
			"order": [],
				"columnDefs": [{
				"targets": 'no-sort',
				"orderable": false,
			}],
			drawCallback: function() {
				var info = this.api().page.info();

				// uodate
				$('#datatable-default_paginate').prepend(
					'<i>Currently showing page '+(info.page+1)+' of '+info.pages+' pages.</i>'
				);
			}
		});

		// Setup - add a text input to each footer cell
		$('#datatable-default thead tr').clone(true).appendTo('#datatable-default thead');
		$('#datatable-default thead tr:eq(1) th').each(function(i){
			var el = $(this),
			    title = el.text();

			// check
			if(el.hasClass('no-filter')){
			    el.text('');

			    return true;
			}

			// get attributes
			var attributes = $.map(this.attributes, function(item) {
			    return item.name;
			});

			// remove attributes
			$.each(attributes, function(atI, atItem) {
			    el.removeAttr(atItem);
			});

			// unbind
			el.unbind();

			// add
			el.addClass('is-clm-filter');

			// update
			el.html('<input type="text" class="form-control form-control-sm" placeholder="Search ' + title + '" />');

			// bind
			$('input', this).on('keyup change', function(){
			    // check
			    if(table.api().column(i).search() !== this.value){
			        table.api().column(i).search(this.value).draw();
			    }
			});
		});
	};

	$(function() {
		datatableInit();
	});

}).apply(this, [jQuery]);
