<!DOCTYPE html>
<html>

<head>
    <title>Mã xác thực xuất kho</title>
</head>

<body>
    <h2>Xin chào {{ $borrowerName }},</h2>
    <p>Bạn đang thực hiện thủ tục xuất kho cho phiếu mượn #{{ $borrowId }}.</p>
    <p>Vui lòng cung cấp mã OTP sau cho nhân viên để xác nhận:</p>
    <h1 style="color: #2563eb; letter-spacing: 5px;">{{ $otp }}</h1>
    <p>Mã này có hiệu lực trong vòng 5 phút.</p>
    <p>Nếu bạn không thực hiện yêu cầu này, vui lòng bỏ qua email này.</p>
    <br>
    <p>Trân trọng,</p>
    <p>{{ config('app.name') }}</p>
</body>

</html>