<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 500px;
            margin: 0 auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h2 {
            color: #333;
            margin: 0;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #6B21A8;
            margin-bottom: 10px;
        }
        .content {
            color: #555;
            line-height: 1.6;
        }
        .otp-box {
            background: #f9f9f9;
            border: 2px solid #6B21A8;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 30px 0;
        }
        .otp-code {
            font-size: 32px;
            font-weight: bold;
            color: #6B21A8;
            letter-spacing: 5px;
            font-family: monospace;
        }
        .expires {
            color: #999;
            font-size: 12px;
            margin-top: 10px;
        }
        .footer {
            text-align: center;
            color: #999;
            font-size: 12px;
            margin-top: 30px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 12px;
            border-radius: 4px;
            margin-top: 20px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">GiftBox</div>
            <h2>Email Verification</h2>
        </div>

        <div class="content">
            <p>Hi {{ $name }},</p>
            <p>Welcome to GiftBox! To complete your registration, please use the following One-Time Password (OTP):</p>

            <div class="otp-box">
                <div class="otp-code">{{ $otp }}</div>
                <div class="expires">Expires in {{ $expiresIn }}</div>
            </div>

            <p>Use this OTP to verify your email address and complete your account setup.</p>

            <div class="warning">
                <strong>⚠️ Security Notice:</strong> Never share this OTP with anyone. GiftBox support will never ask for your OTP.
            </div>

            <p style="margin-top: 20px; color: #999; font-size: 12px;">
                If you didn't request this OTP, please ignore this email.
            </p>
        </div>

        <div class="footer">
            <p>© 2026 GiftBox. All rights reserved.</p>
            <p>This is an automated email. Please do not reply.</p>
        </div>
    </div>
</body>
</html>
