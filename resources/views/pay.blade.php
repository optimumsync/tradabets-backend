<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
          integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <title>Interswitch | Quickteller Business</title>
</head>
<h4 class="text-center" style="margin-top: 25%">Please wait...</h4>

<body onload="document.interswitchForm.submit()">
<form action="{{ $transactionData['initializationURL'] }}" method="post" name="interswitchForm"
      style="display:none">
    <input name="site_redirect_url" value="{{ $transactionData['callbackURL'] }}" type="hidden" />
    <input name="pay_item_id" value="{{ $transactionData['payItemID'] }}" type="hidden" />
    <input name="txn_ref" value="{{ $transactionData['transactionReference'] }}" type="hidden" />
    <input name="amount" value="{{ $transactionData['amount'] }}" type="hidden" />
    <input name="currency" value="{{ $transactionData['currency'] }}" type="hidden" />
    <input name="cust_name" value="{{ $transactionData['customerName'] }}" type="hidden" />
    <input name="cust_email" value="{{ $transactionData['customerEmail'] }}" type="hidden" />
    <input name="cust_id" value="{{ $transactionData['customerID'] }}" type="hidden" />
    <input name="pay_item_name" value="{{ $transactionData['payItemName'] }}" type="hidden" />
    <input name="merchant_code" value="{{ $transactionData['merchantCode'] }}" type="hidden" />
    <input name="tokenise_card" value="{{ $transactionData['tokeniseCard'] }}" type="hidden" />
    <input name="display_mode" value="PAGE" type="hidden" />
</form>
</body>

</html>
