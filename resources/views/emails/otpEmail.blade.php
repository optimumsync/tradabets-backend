<!-- <!DOCTYPE html>
<html>
<head>
    <title>Tradabets Registration OTP</title>
</head>
<body>
<h1>{{$details['title']}}:</h1>
<p>{{ $details['email_otp'] }}</p>

<p>Thank you</p>
</body>
</html> -->


<!DOCTYPE html>
<html>
<head>
    <title>Tradabets Registration OTP</title>
</head>
<body>
<h1>Tradabets Registration OTP:</h1>
<p>{{ $details['otp'] }}</p>
<p>This code is valid for {{ $details['minutes'] }} minutes.</p>
<p>Thank you</p>
</body>
</html>
