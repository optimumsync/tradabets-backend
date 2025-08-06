
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

    $('.register-user').click( function() {
        var motp = $('.mobile_otp').val();
        var phone_otp = sessionStorage.getItem('phone_otp');

        var eotp = $('.email_otp').val();
        var email_otp = sessionStorage.getItem('email_otp');

        if (motp != phone_otp) {
            $("#phoneOtpError").text("Please enter valid mobile OTP");
            $("#phoneOtpError").css('color','red');
            $("#phoneOtpError").fadeIn(1000);
            $("#phoneOtpError").fadeOut(5000);
        }

        // if (eotp != email_otp) {
        //     $("#emailOtpError").text("Please enter valid email OTP");
        //     $("#emailOtpError").css('color','red');
        //     $("#emailOtpError").fadeIn(1000);
        //     $("#emailOtpError").fadeOut(5000);
        // }

        //if(motp == phone_otp && eotp == email_otp) {
        if(motp == phone_otp) {
            $('form#registration_form').submit();
        }

    });

    $('#otp_verify').click( function() {
        var otp = $('.email_mobile_otp').val();
        var phone_otp = sessionStorage.getItem('phone_otp');
        var email_otp = sessionStorage.getItem('email_otp');

        if(otp != phone_otp) {
            $("#emailMobileOtpError").text("Please enter valid email or mobile OTP");
            $("#emailMobileOtpError").css('color','red');
            $("#emailMobileOtpError").fadeIn(1000);
            $("#emailMobileOtpError").fadeOut(5000);
        }

        if(otp == phone_otp || otp == email_otp) {
            $('#loginOtpModal').modal('hide');
            $('form#user-login-form').submit();
        }
    });

});


// doc ready
$(document).ready(function(){

    $(".user-verify").click(function(event){
        event.preventDefault();

        $.ajax({
            type      : 'POST',
            url       : 'login/userVerify',
            data:{username:$('.login-email').val(),'password':$('.login-password').val(),},
            dataType  : 'json',
            success   : function(view_data) {
                if(view_data==1 || view_data==2) {
                    $('form#user-login-form').submit();
                    // $('#loginOtpModal').modal({backdrop: 'static', keyboard: false}, 'show');
                    // setTimeout(function() {
                    //     sendMobileEmailOtp($('.login-email').val());
                    // }, 2000);
                } else {
                    $("#password").text("Incorrect username or password");
                    $("#password").css("color","red");
                }
            }
        });
    });

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
	//Make readonly
    $(".readonly").keydown(function (e) {
        e.preventDefault();
    });
    $(".readonly").css('background', '#e9ecef');

});

function validDate(){

    start_date = new Date($("#start_id").val());
    end_date = new Date($("#endDate_id").val());
    if(start_date.getTime()>end_date.getTime())
    {
        // document.getElementById("endDateSpan").innerHTML="* To Date should be greater than the From Date";
        $("#date-error").text("* From date should be greater than To date");
        $("#date-error").css('color','red');
        $("#date-error").fadeIn(1000);
        $("#date-error").fadeOut(5000);
        return false;
    }
}

function onlyCharacters(e, t) {
    try {
        var RegEx=/^[\sA-Za-z]+$/;
        if (window.event) {
            var charCode = window.event.keyCode;
        }
        else if (e) {
            var charCode = e.which;
        }
        else { return true; }
        /*if (charCode > 31 && (charCode < 65  || charCode > 90 )) {
            console.log(window.event);
            return false;
        }*/
        if (window.event.key.match(RegEx)) {
            return true;
        }
        else {
            return false;
        }
    }
    catch (err) {
        alert(err.Description);
    }
}

function registerToggle()
{
    if($("#first-page").is(":visible")){
        if($('.first-name').val()!="" && $('.last-name').val()!="" && $('.email-input').val()!="" && $('.date-of-birth').val()!="" && $('.password').val()!="" && $('.confirm-password').val()!=""){
            var resultEmail = emailVerify();
            var resultPassword=passwordVerify();
            var resultPhone= phoneNumberVerify();
            if(resultEmail!=false && resultPassword!=false ) {
                $('#first-page').css('display', 'none');
                $('#second-page').css('display', 'block');
            }
            else
            {
                return false;
            }
        }
        else{
            $('.mandatory-fields').text('**Mandatory fields needs to be filled');
            $(".mandatory-fields").css('color','red');
            return false;
        }
    }
    else{
        $('.mandatory-fields').text('');
        $('#first-page').css('display','block');
        $('#second-page').css('display','none');
    }
}

function registerToggleThree()
{
    if($("#second-page").is(":visible")){
        var resultPhone= phoneNumberVerify();
        if(resultPhone!=false) {
            $('#first-page').css('display', 'none');
            $('#second-page').css('display', 'none');
            $('#third-page').css('display', 'block');
            var mobile = $('.phone').val();
            var country_code = $('#country_code').val();
            var phone = '+'+country_code+''+mobile;
            var email = $('.email-input').val();
            var first_name = $('.first-name').val();
            var last_name = $('.last-name').val();
            sendMobileOTP(phone);
            sendEmailOTP(email, first_name, last_name);
        }
        else
        {
            return false;
        }
    }
    else{
        $('.mandatory-fields').text('');
        $('#first-page').css('display','none');
        $('#second-page').css('display','block');
        $('#third-page').css('display','none');
    }
}

function emailMessageClear(){
        $('#email-id').text("");
}
function passwordMessageClear(){
    $("#password-message").text('');
}
function confirmPasswordMessageClear(){
    $("#pwd-message").text('');
}
function phoneMessageClear(){
    $("#phone").text("");
}
function loginEmailMessageClear(){
    $("#email-message").text('');
}
function loginPasswordMessageClear(){
    $("#password").text('');
}

function forgotPasswordURL(){
    $.ajax({
        type:'get',
        url:'password/sendOtp',
        data:{email:$('.forgot-password-email').val()},
        dataType:'json',
        async:false
    }).done(function(view_data){
        $('#forgotPasswordModal').modal('hide');
        $('#passwordOtpModal').modal('show');
    });
}

function forgotPasswordOtpVerify(){
    $.ajax({
        type:'get',
        url:'password/email',
        data:{email:$('.forgot-password-email').val(), email_otp:$('.forgot-email-otp').val()},
        dataType:'json',
        async:false
    }).done(function(view_data){
        if(view_data == 2) {
            $('#reset-message').text('Please enter valid OTP');
            $('#reset-message').css('color','red');
        }else if(view_data==1)
        {
            $('#reset-message').text('Email sent with password reset link!');
            $('#reset-message').css('color','#0A8C74');
            $(".reset-password-form").trigger("reset");
            setTimeout(function() {
                $('#passwordOtpModal').modal('hide');
            }, 3000);
        }
        else{
            $('#reset-message').text("User with given email doesnot exist!");
            $('#reset-message').css('color','red');
            $(".reset-password-form").trigger("reset");
        }
    });
}

function amountValidationMessageClear(){
    $(".amount-validation-message").text('');
}

function amountValidation(){
debugger;
    if($('#bet_count').val() < 1) {
        $('.amount-validation-message').text('Please place a bet, Min. 3 bets should be completed.');
        $('.amount-validation-message').css('color','red');
        return false;
    }

    if($('.withdraw-amount-input').val() == '') {
        $('.amount-validation-message').text('Please enter amount for withdraw');
        $('.amount-validation-message').css('color','red');
        return false;
    }

    var withdraw_amount=parseFloat($('.withdraw-amount-input').val());
    var balance_amount=parseFloat($('#balance-amount').val());
    // var kyc_status = ($('#kycstatus').val());
    var account_status = ($('#accountstatus').val());

    if(withdraw_amount <= 0 || withdraw_amount > balance_amount){
        $('.amount-validation-message').text('Please enter the valid amount for withdraw');
        $('.amount-validation-message').css('color','red');
        return false;
    }

    if(account_status != '1'){
        $('.kyc-status-message').text('Please add a bank account!');
        $('.kyc-status-message').css('color','red');
        return false;
    }
}

function depositAmountValidation(){debugger;
    var deposit_amount=parseFloat($('.deposit_amount').val());

    if(deposit_amount <= 0){
        $('.amount-validation-message').text('Please enter the valid amount for Deposit');
        $('.amount-validation-message').css('color','red');
        return false;
    }
}

function documentStatus(t){
    if(t.value=="Reject")
    {
        $('.status-message').val('rejected');
        $('.kyc-form').submit();
    }
    else{
        $('.status-message').val('approved');
        $('.kyc-form').submit();
    }
}


$(document).ready(function(){
    $('#second-page').css('display','none');
    $('#third-page').css('display','none');
    $(".responsive-menu").on('click',function(){

        $(".gaming-menu").slideToggle("slow");
    });
    $(".left-menu").hide();
    $(".toggle-sidebar-left").on('click',function(){
        $(".left-menu").slideToggle("slow");
        $(".left-menu").show();
    });

    var x=parseFloat($(".balance-amount-class").val());
    x=x.toString();
    var afterPoint = '';
    if(x.indexOf('.') > 0)
        afterPoint = x.substring(x.indexOf('.'),x.length);
    x = Math.floor(x);
    x=x.toString();
    var lastThree = x.substring(x.length-3);
    var otherNumbers = x.substring(0,x.length-3);
    if(otherNumbers != '')
        lastThree = ',' + lastThree;
    var avail_balance = otherNumbers.replace(/\B(?=(\d{2})+(?!\d))/g, ",") + lastThree + afterPoint;
    $(".user-balance-label").text(" "+avail_balance);
    $(".dashboard-amount-balance").text(" "+avail_balance);
    //var header = $('.sports-list');

});

$( function() {
    var date = new Date();
    var currentMonth = date.getMonth();
    var currentDate = date.getDate();
    var currentYear = date.getFullYear();
    $( "#datepicker" ).datepicker({
        endDate: '-19Y',
        autoclose: true,
        format: 'yyyy-mm-dd',
    });

} );

function phoneNumberVerify(){
    var userinput = $('.phone').val();
    if($('.phone').val().length<1)
    {
        $("#phone").text('Please enter valid number');
        $("#phone").css('color','red');
        return false;
    }
    else
    {
        sessionStorage.setItem("ajax-return",true);
        $.ajax({
            type:'get',
            url:'/phoneCheck/'+userinput,
            dataType:'json',
            async:false
        }).done(function(view_data){
           if(view_data!=1) {
               $("#phone").text('User already registered with this phone number. Please use other Phone Number.');
               $("#phone").css('color', 'red');
               sessionStorage.setItem("ajax-return",false);
           }

        });
        if(sessionStorage.getItem('ajax-return')=="false"){
            return false;
        }

    }
}

// function sendOTP(mobile, email){
//     var postForm = {
//         'phone' : mobile,
//         'email' : email
//     };
//
//     $.ajax({
//         type      : 'POST',
//         url       : '/sendOTP',
//         data      : postForm,
//         dataType  : 'json',
//         success   : function(data) {
//             sessionStorage.setItem("phone_otp", data.phone_otp);
//             sessionStorage.setItem("email_otp", data.email_otp);
//         }
//     });
// }

function sendMobileOTP(mobile){
    var postForm = {
        'phone' : mobile
    };

    $.ajax({
        type      : 'POST',
        url       : '/sendMobileOtp',
        data      : postForm,
        dataType  : 'json',
        async: false,
        success   : function(data) {
            sessionStorage.setItem("phone_otp", data.phone_otp);
        }
    });
}

function sendEmailOTP(email, first_name, last_name){
    var postForm = {
        'first_name' : first_name,
        'last_name' : last_name,
        'email' : email
    };

    $.ajax({
        type      : 'POST',
        url       : '/sendEmailOtp',
        data      : postForm,
        dataType  : 'json',
        async: false,
        success   : function(data) {
            sessionStorage.setItem("email_otp", data.email_otp);
        }
    });
}

function sendMobileEmailOtp(email){
    var postForm = {
        'email' : email
    };

    $.ajax({
        type      : 'POST',
        url       : '/sendMobileEmailOtp',
        data      : postForm,
        dataType  : 'json',
        async: false,
        success   : function(data) {
            sessionStorage.setItem("phone_otp", data.phone_otp);
            sessionStorage.setItem("email_otp", data.email_otp);
        }
    });
}

// function phoneNumberVerify(){
//     if($('.phone').val().length<10)
//     {
//         $("#phone").text('Please enter 10 digit valid number');
//         $("#phone").css('color','red');
//         return false;
//     }
// }

function emailVerify(){
    var userinput = $('.email-input').val();
    var pattern = /^\b[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b$/i

    if(!pattern.test(userinput)) {
        // commented for email as optional
        // $("#email-id").text('Enter valid email address');
        // $("#email-id").css('color','red');
        // return false;
    }
    else
    {
        sessionStorage.setItem("ajax-return",true);
        $.ajax({
            type:'get',
            url:'/emailCheck/'+userinput,
            dataType:'json',
            async:false
        }).done(function(view_data){
           if(view_data!=1) {
               $("#email-id").text('User already registered with this email. Please use other email.');
               $("#email-id").css('color', 'red');
               sessionStorage.setItem("ajax-return",false);
           }

        });
        if(sessionStorage.getItem('ajax-return')=="false"){
            return false;
    }

    }
}

function passwordVerify(){
    if($('.password').val().length<=6)
    {
        $("#password-message").text('Password should be greater than 6 characters');
        $("#password-message").css('color','red');
        return false;
    }
}
function confirmPasswordVerify(){
    if($('.password').val()!=$('.confirm-password').val())
    {
        $("#pwd-message").text('Password did not match');
        $("#pwd-message").css('color','red');
        $('.confirm-password').val("");
    }
}
function loginEmailVerify() {
    var userinput = $('.login-email').val();
    var pattern = /^\b[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b$/i

    if (!pattern.test(userinput)) {
        $("#email-message").text('Enter valid email address');
        $("#email-message").css('color', 'red');
        return false;
    }
}
function loginPasswordVerify(){
    if($('.login-password').val().length<=6)
    {
        $("#password").text('Password should be greater than 6 characters');
        $("#password").css('color','red');
        return false;
    }
}
function forgotPasswordModalShow(){
    $("#loginModal").modal('hide');
    $("#forgotPasswordModal").modal('show');
}

// parse jwt
function parseJwt (token) {
    var retStr = '';

    // check
    if( (token.split('.').length - 1) == 2 ){
        var base64Url = token.split('.')[1],
            base64 = decodeURIComponent(atob(base64Url).split('').map(function(c) {
                return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
            }).join(''));

        retStr = JSON.parse(base64);
    }else{
        retStr = 'Invalid token.';
    }

    return retStr;
}

// convert form to JSON
function convertFormToJSON($form){
    var unindexed_array = $form.find('input:not([name^="_"]), textarea:not([name^="_"]), select:not([name^="_"])').serializeArray();
    var indexed_array = {};

    $.map(unindexed_array, function(n, i){
        indexed_array[n['name']] = n['value'];
    });

    return indexed_array;
}

// update form JSON sample request
function updateFormJSONSampleRequest(frm)
{
    var respCont = frm.find('.api-response-container');

    // clear
    respCont.text('').hide();

    var jsonPretty = JSON.stringify(convertFormToJSON(frm), null, '\t');

    respCont.text('JSON Request:\n' + jsonPretty).show();
}

// doc ready
$(document).ready(function(){

	// submit request
    $('body').on('submit', 'form.is-api-test-form', function(e){
        var frm = $(this),
            tokenInp = frm.find('input[name="token"]'),
            idInp = frm.find('input[name="id"]'),
            respCont = frm.find('.api-response-container'),
            url = frm.attr('action');

        e.preventDefault();

        // set
        url = (idInp.length > 0) ? url + '/' + idInp.val() : url;

        // clear
        respCont.text('').removeClass('success').hide();

        // call
        $.ajax({
            url: url,
            data: frm.serialize(),
            dataType: 'json',
            method: frm.attr('method')
        }).done(function(data){
            var jsonPretty = JSON.stringify(data, null, '\t');

            respCont.text('JSON Response:\n' + jsonPretty).show();

            if(data.hasOwnProperty('access_token')){
                localStorage.setItem('API_ACCESS_TOKEN', data.access_token);
            }
            if(data.hasOwnProperty('status') && data.status == 1){
                respCont.addClass('success');
            }
            if(data.hasOwnProperty('data') && data.data.length > 0){
                respCont.addClass('success');
            }
        }).always(function(xhr, textStatus){
            if(xhr.responseJSON.hasOwnProperty('exception')){
                delete xhr.responseJSON.exception;
                delete xhr.responseJSON.file;
                delete xhr.responseJSON.line;
                delete xhr.responseJSON.trace;
            }

            var jsonPretty = JSON.stringify(xhr.responseJSON, null, '\t');

            if(xhr.status == 401){
                respCont.text('Response: 401 Unauthorized, Invalid Token\n' + jsonPretty).show();
            }
            if(xhr.status == 404){
                respCont.text('Response: 404 Not Found, Invalid Call\n' + jsonPretty).show();
            }
        });
    });

    // clear request
    $('body').on('click', '.clear-api-response', function(e){
        var frm = $(this).closest('form'),
            respCont = frm.find('.api-response-container');

        e.preventDefault();

        // clear
        frm.trigger('reset');
        respCont.text('').hide();

    });

    // decode token
    $('body').on('submit', 'form.is-api-token-decode-form', function(e){
        var frm = $(this),
            token = frm.find('input[name="token"]').val(),
            respCont = frm.find('.api-response-container');

        e.preventDefault();

        // clear
        respCont.text('').hide();

        var jsonPretty = JSON.stringify(parseJwt(token), null, '\t');

        respCont.text('Decode:\n' + jsonPretty).show();
    });

    // on update
    $('body').on('change keyup', 'form.is-api-test-form input, form.is-api-test-form textarea, form.is-api-test-form select', function(){
        updateFormJSONSampleRequest( $(this).closest('form') );
    });

    // pre-load
    if($('form.is-api-test-form').length > 0){
        $('form.is-api-test-form').find('input[name="token"]').val( localStorage.getItem('API_ACCESS_TOKEN') );

        $('form.is-api-test-form').each(function(i, item){
            var frm = $(item);

            updateFormJSONSampleRequest(frm);
        });
    }
    if($('form.is-api-token-decode-form').length > 0){
        $('form.is-api-token-decode-form').find('input[name="token"]').val( localStorage.getItem('API_ACCESS_TOKEN') );
    }

});



    function paystackFunction(){
        deposit_amount=$("#deposit_block").val();
        $("#paystack_amount").val(deposit_amount*100);

    }
    function flutterwaveFunction(){
        deposit_amount=$("#deposit_block").val();
        //$("#flutterwave_amount").val(deposit_amount*100);
        $("#flutterwave_amount").val(deposit_amount);

    }

    function interswitchFunction(){
        deposit_amount=$("#deposit_block").val();
        $("#interswitch_amount").val(deposit_amount*100);

    }


    function opayFunction(){
        deposit_amount=$("#deposit_block").val();
        $("#opay_amount").val(deposit_amount*100);

    }


    function depositclick() {

        if ($(paystk).is(":checked")) {

            var deposit_amount=parseFloat($('.deposit_amount').val());

            if(deposit_amount <= 0){
                $('.amount-validation-message').text('Please enter the valid amount for Deposit');
                $('.amount-validation-message').css('color','red');
                return false;
            }
            $(paymentForm).submit();

        }
        else if ($(flutterwave).is(":checked")) {
            $(paymentForm_flutterwave).submit();
        }
        else if ($(interswitch).is(":checked")) {
            $(paymentForm_interswitch).submit();
        }
        else if ($(opay).is(":checked")) {
            $(paymentForm_opay).submit();
        }
    }

    function checkAll(table, bx) {

        var checkname = document.getElementsByClassName("bulkCheckbox");

        for (i = checkname.length; i--; ) {
            console.log(checkname[i]);
            console.log(bx);
            checkname[i].checked = bx.checked;
        }
    }

    function ApproveSelected() {

        var checkboxes = document.getElementsByName('select_request');
        var selected_requests = [];
        for (var checkbox of checkboxes) {
            if (checkbox.checked) {
                selected_requests.push(checkbox.value);
            }
        }
        if (confirm("Are you sure you want to approve the selected Transfers?")) {

        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
        });
            $.ajax({
                type:'post',
                url:'/bulkTransfer',
                data:{ data : selected_requests },
                success:function(data){
                    console.log(data);
                    var responseJSON = JSON.parse(data);
                    if (responseJSON.status === 'success') {
                        $("#datatable-default input[type='checkbox']:checked:not('.toggleCheckbox')").closest("tr").remove();
                        $('#message-area').append('<div class="alert alert-success"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a> '+ responseJSON.message +'</div>');
                        $('div.container-body').fadeOut();
                        $('div.container-body').load('/withdraw-requests',function(){
                        $('div.container-body').fadeIn();
                        });

                    }
                    else {
                        $('#message-area').append('<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a> '+ responseJSON.message +'</div>');
                    }

                },
                error: function(e){
                    // alert(e.error);
                }
            });
        }
        else{
            return;
        }
    }

    function ApproveSelectedtest() {

        var checkboxes = document.getElementsByName('select_request');
        var selected_requests = [];
        for (var checkbox of checkboxes) {
            if (checkbox.checked) {
                selected_requests.push(checkbox.value);
            }
        }
        if (confirm("Are you sure you want to approve the selected Transfers?")) {

        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
        });
            $.ajax({
                type:'post',
                url:'/bulkTransfer',
                data:{ data : selected_requests },
                success:function(data){
                    console.log(data);
                },
                error: function(e){
                    // alert(e.error);
                }
            });
        }
        else{
            return;
        }
    }

    function ApproveRow(path) {
        if (confirm("Are you sure you want to approve the selected Transfers?")) {
        location.href = path;
        }
        else{
            return;
        }
    }

    function RejectRow(path) {
        if (confirm("Are you sure you want to reject the selected Transfers?")) {
        location.href = path;
        }
        else{
            return;
        }
    }

    function RejectSelected() {
        var checkboxes = document.getElementsByName('select_request');
        var selected_requests = [];
        for (var checkbox of checkboxes) {
            if (checkbox.checked) {
                selected_requests.push(checkbox.value);
            }
        }
        // alert($('meta[name="csrf-token"]').attr('content'));
                $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
        });
            $.ajax({
                type:'post',
                url:'/withdraw-request-bulk-reject',
                data:{ data : selected_requests },
                success:function(data){
                    // console.log(data);
                },
                error: function(e){
                    //alert(e.error);
                }
            });
    }

    function errorBulkTransfer() {
            $('.error-message').text('Transfer could not be finalized');
            $('.error-message').css('color','red');
    }

    // function addBankAccount() {
    //     var oSelectOne = oForm.elements["bank"];
    //     index = oSelectOne.selectedIndex;
    //     var selected_option_value = oSelectOne.options[index].value;

    //         $.ajax({
    //             type:'post',
    //             url:'/add_account',
    //             data:{ data : selected_option_value },
    //             success:function(data){
    //                 // console.log(data);
    //             },
    //             error: function(e){
    //                 //alert(e.error);
    //             }
    //         });

    // }

    function addBankAccount() {
        // var dataSelected = document.getElementsById('bank');
        var dataSelected = document.getElementsById(bank);
        alert(dataSelected);
    }

    $(document).ready(function() {
        $('.user-select').select2({
            placeholder: "Search for a user",
            allowClear: true,
            width: 'resolve'
        });
    });
