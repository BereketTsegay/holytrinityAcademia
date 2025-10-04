<!DOCTYPE html>
<html>
<head>
    <title>Reset Your Password</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #3b82f6; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f9fafb; padding: 30px; border-radius: 0 0 8px 8px; }
        .button { background: #3b82f6; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block; }
        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>School Management System</h1>
            <h2>Reset Your Password</h2>
        </div>

        <div class="content">
            <p>Hello,</p>

            <p>You are receiving this email because we received a password reset request for your account.</p>

            <p style="text-align: center; margin: 30px 0;">
                <a href="{{ $resetUrl }}" class="button">
                    Reset Password
                </a>
            </p>

            <p>This password reset link will expire in {{ $expire }} minutes.</p>

            <p>If you did not request a password reset, no further action is required.</p>

            <div class="footer">
                <p>If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:</p>
                <p><a href="{{ $resetUrl }}">{{ $resetUrl }}</a></p>

                <p>&copy; {{ date('Y') }} School Management System. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
