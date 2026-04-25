<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Password Reset Code</title>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f4f7f6; color: #333; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #ffffff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 8px rgba(0,0,0,0.05); }
        h1 { color: #111; font-size: 24px; font-weight: 800; text-align: center; margin-bottom: 24px; }
        p { line-height: 1.6; margin-bottom: 20px; text-align: center; color: #555; }
        .code-container { text-align: center; margin: 32px 0; }
        .code { font-size: 48px; letter-spacing: 8px; font-weight: 900; color: #10b981; background: #ecfdf5; padding: 16px 32px; border-radius: 12px; display: inline-block; border: 2px dashed #10b981; }
        .footer { text-align: center; font-size: 12px; color: #999; margin-top: 40px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Verify Your Account</h1>
        <p>Hello! We received a request to reset your EduBoard password. Please use the following code to continue:</p>
        <div class="code-container">
            <div class="code">{{ $code }}</div>
        </div>
        <p>This code will expire in 15 minutes. If you did not request a password reset, you can safely ignore this email.</p>
        <div class="footer">
            &copy; {{ date('Y') }} Eduboard. All rights reserved.
        </div>
    </div>
</body>
</html>
