<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Account is Ready</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            line-height: 1.6;
            color: #374151;
            background-color: #f9fafb;
            padding: 20px;
        }
        
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
        }
        
        .header {
            background: linear-gradient(135deg, #38618C 0%, #35A7FF 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .header-icon {
            font-size: 60px;
            margin-bottom: 20px;
            animation: bounce 1s infinite alternate;
        }
        
        @keyframes bounce {
            from { transform: translateY(0px); }
            to { transform: translateY(-10px); }
        }
        
        .header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
            color: white;
        }
        
        .header p {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .content {
            padding: 40px 30px;
        }
        
        .greeting {
            font-size: 20px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 30px;
        }
        
        .success-message {
            background-color: #f0fdf4;
            border-left: 4px solid #01FF19;
            padding: 24px;
            border-radius: 8px;
            margin: 25px 0;
        }
        
        .password-box {
            background-color: #f8fafc;
            border: 2px solid #01FF19;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            margin: 30px 0;
            position: relative;
        }
        
        .password-label {
            display: block;
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 8px;
        }
        
        .password-value {
            font-family: 'Courier New', monospace;
            font-size: 22px;
            font-weight: 700;
            color: #111827;
            letter-spacing: 1px;
        }
        
        .login-button {
            display: inline-block;
            background-color: #01FF19;
            color: #000000;
            text-decoration: none;
            padding: 16px 40px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 16px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(1, 255, 25, 0.2);
            margin: 20px 0;
        }
        
        .login-button:hover {
            background-color: #00e617;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(1, 255, 25, 0.3);
        }
        
        .security-note {
            background-color: #fef3c7;
            border: 1px solid #fbbf24;
            padding: 20px;
            border-radius: 8px;
            margin: 30px 0;
            color: #92400e;
        }
        
        .security-note strong {
            display: block;
            margin-bottom: 8px;
            font-size: 15px;
        }
        
        .footer {
            background-color: #f8fafc;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        
        .footer p {
            color: #6b7280;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 10px;
        }
        
        @media (max-width: 480px) {
            .content, .header {
                padding: 30px 20px;
            }
            
            .header h1 {
                font-size: 24px;
            }
            
            .header-icon {
                font-size: 50px;
            }
            
            .password-value {
                font-size: 18px;
            }
            
            .login-button {
                padding: 14px 32px;
                width: 100%;
                display: block;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="header">
            <div class="header-icon">🎉</div>
            <h1>Your Account is Ready!</h1>
            <p>Welcome to {{ config('app.name') }}</p>
        </div>
        
        <div class="content">
            <div class="greeting">Hello {{ $userName }},</div>
            
            <div class="success-message">
                <p>Your account has been successfully created on <strong>{{ config('app.name') }}</strong>!</p>
            </div>
            
            <p>Here are your temporary login credentials:</p>
            
            <div class="password-box">
                <span class="password-label">🔐 Temporary Password</span>
                <div class="password-value">{{ $tempPassword }}</div>
            </div>
            
            <p>To access your account, click the button below:</p>
            
            <div style="text-align: center;">
                <a href="http://localhost:5173/signin" class="login-button">Access My Account</a>
            </div>
            
            <div class="security-note">
                <strong>⚠️ Security Recommendation</strong>
                <p>For security purposes, we strongly recommend changing your password on your first login.</p>
            </div>
            
            <p style="margin-top: 30px;">
                Thank you for your trust,<br>
                The <strong>{{ config('app.name') }}</strong> Team
            </p>
        </div>
        
        <div class="footer">
            <p>© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <p>This email was sent automatically — please do not reply.</p>
        </div>
    </div>
</body>
</html>