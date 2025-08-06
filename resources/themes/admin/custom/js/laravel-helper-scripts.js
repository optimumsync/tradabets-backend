
// doc ready
$(document).ready(function(){

	// delete form
	$('body').on('click', '.is-delete-link', function(e){
		e.preventDefault();

		var frm = $('form[name="delete_form"]');

		frm.attr('action', $(this).attr('href'));

		// Display a confirm modal, with custom title.
		eModal.confirm('Are you sure you want to continue?', 'Please Confirm')
			.then(function(){
				frm.submit();
			}, function(){
				// cancel
			});
	});

	// logout
	$('body').on('click', '.is-logout-link', function(e){
		e.preventDefault();

		$('form[name="logout_user_form"]').submit();
	});

	// back
	$('body').on('click', '.is-history-back-btn', function(e){
		e.preventDefault();

		window.history.go(-1);

		return false;
	});

});
