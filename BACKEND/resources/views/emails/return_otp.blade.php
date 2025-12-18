<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mã OTP Trả Thiết Bị</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .container {
            background-color: #f9f9f9;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .otp-box {
            background-color: #4F46E5;
            color: white;
            font-size: 32px;
            font-weight: bold;
            text-align: center;
            padding: 20px;
            border-radius: 8px;
            letter-spacing: 8px;
            margin: 20px 0;
        }

        .info {
            background-color: #EEF2FF;
            padding: 15px;
            border-left: 4px solid #4F46E5;
            margin: 20px 0;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h2>🔐 Mã Xác Thực Trả Thiết Bị</h2>
        </div>

        <p>Xin chào <strong>{{ $borrowerName }}</strong>,</p>

        <p>Bạn đang thực hiện trả thiết bị cho phiếu mượn <strong>#{{ $borrowId }}</strong>.</p>

        <p>Vui lòng cung cấp mã OTP sau cho nhân viên để xác thực:</p>

        <div class="otp-box">
            {{ $otp }}
        </div>

        <div class="info">
            <strong>⏰ Lưu ý:</strong>
            <ul style="margin: 10px 0;">
                <li>Mã OTP này có hiệu lực trong <strong>5 phút</strong></li>
                <li>Không chia sẻ mã này với bất kỳ ai khác</li>
                <li>Nếu bạn không thực hiện thao tác này, vui lòng bỏ qua email</li>
            </ul>
        </div>

        <p>Cảm ơn bạn đã sử dụng dịch vụ!</p>

        <div class="footer">
            <p>Email này được gửi tự động, vui lòng không trả lời.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>

</html>