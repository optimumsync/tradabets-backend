/*
Name: 			Tables / Advanced - Examples
Written by: 	Okler Themes - (http://www.okler.net)
Theme Version: 	2.1.1
*/

(function($) {

	'use strict';

	var datatableInit = function() {
		var $table = $('#datatable-details');

		// format function for row details
		var fnFormatDetails = function( datatable, tr ) {
			var data = datatable.fnGetData(tr),
				jsonData = $(tr).data('rowdetails'),
				retArr = [];

			retArr.push('<div class="is-sub-table-cont">');
			retArr.push('<table class="table mb-0 is-sub-table">');
			retArr.push('<thead>');
				retArr.push('<tr>');
					retArr.push('<th>Field Name</th>');
					retArr.push('<th>Old Value</th>');
					retArr.push('<th>New Value</th>');
				retArr.push('</tr>');
			retArr.push('</thead>');
			retArr.push('<tbody>');
				$.each(jsonData, function(field, row){
					retArr.push('<tr>');
						retArr.push('<td>' + row.title + '</td>');
						retArr.push('<td>' + row.prev_value + '</td>');
						retArr.push('<td>' + row.value + '</td>');
					retArr.push('</tr>');
				});
			retArr.push('</tbody>');
			retArr.push('</table>');
			retArr.push('</div>');

			return retArr.join('');
		};

		// insert the expand/collapse column
		var th = document.createElement( 'th' );
		var td = document.createElement( 'td' );
		td.innerHTML = '<i data-toggle class="far fa-plus-square text-primary h5 m-0" style="cursor: pointer;"></i>';
		td.className = "text-center";

		th.className = 'no-filter';

		$table
			.find( 'thead tr' ).each(function() {
				this.insertBefore( th, this.childNodes[0] );
			});

		$table
			.find( 'tbody tr' ).each(function() {
				this.insertBefore(  td.cloneNode( true ), this.childNodes[0] );
			});

		// initialize
		var datatable = $table.dataTable({
			dom: '<"row"<"col-lg-6"l><"col-lg-6"f>><"table-responsive"t>p',
			"lengthMenu": [ [25, 50, 100, 150, 250, -1], [25, 50, 100, 150, 250, "All"] ],
			"pageLength": 150,
			"orderCellsTop": true,
			"order": [],
				"columnDefs": [{
				"targets": 'no-sort',
				"orderable": false,
			}],
			drawCallback: function() {
				var info = this.api().page.info();

				// uodate
				$('#datatable-details_paginate').prepend(
					'<i>Currently showing page '+(info.page+1)+' of '+info.pages+' pages.</i>'
				);
			}
		});

		// add a listener
		$table.on('click', 'i[data-toggle]', function() {
			var $this = $(this),
				tr = $(this).closest( 'tr' ).get(0);

			if ( datatable.fnIsOpen(tr) ) {
				$this.removeClass( 'fa-minus-square' ).addClass( 'fa-plus-square' );
				datatable.fnClose( tr );
			} else {
				$this.removeClass( 'fa-plus-square' ).addClass( 'fa-minus-square' );
				datatable.fnOpen( tr, fnFormatDetails( datatable, tr), 'details' );
			}
		});

		// Setup - add a text input to each footer cell
		$('#datatable-details > thead tr').clone(true).appendTo('#datatable-details > thead');
		$('#datatable-details > thead tr:eq(1) th').each(function(i){
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
			    if($table.api().column(i).search() !== this.value){
			        $table.api().column(i).search(this.value).draw();
			    }
			});
		});
	};

	$(function() {
		datatableInit();
	});

}).apply(this, [jQuery]);
