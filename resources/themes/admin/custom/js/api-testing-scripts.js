
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
