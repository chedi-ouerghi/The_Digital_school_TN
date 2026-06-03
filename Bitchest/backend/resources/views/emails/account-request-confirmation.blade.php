<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Your Email</title>
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
            font-size: 48px;
            margin-bottom: 20px;
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
            font-weight: 400;
        }
        
        .content {
            padding: 40px 30px;
        }
        
        .greeting {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 30px;
        }
        
        .message-box {
            background-color: #f8fafc;
            border-left: 4px solid #35A7FF;
            padding: 24px;
            border-radius: 8px;
            margin: 30px 0;
        }
        
        .cta-button {
            display: inline-block;
            background-color: #01FF19;
            color: #000000;
            text-decoration: none;
            padding: 16px 40px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(1, 255, 25, 0.2);
            margin: 20px 0;
        }
        
        .cta-button:hover {
            background-color: #00e617;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(1, 255, 25, 0.3);
        }
        
        .warning-box {
            background-color: #fff7ed;
            border: 1px solid #fed7aa;
            border-left: 4px solid #f97316;
            padding: 20px;
            border-radius: 8px;
            margin: 30px 0;
        }
        
        .warning-box strong {
            color: #9a3412;
            display: block;
            margin-bottom: 8px;
            font-size: 16px;
        }
        
        .link-box {
            background-color: #f1f5f9;
            padding: 16px;
            border-radius: 8px;
            margin: 20px 0;
            word-break: break-all;
            font-family: monospace;
            font-size: 14px;
            color: #475569;
        }
        
        .code-box {
            display: inline-block;
            padding: 12px 24px;
            background-color: #f8fafc;
            border: 2px dashed #cbd5e1;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-weight: 600;
            font-size: 18px;
            color: #38618C;
            margin: 10px 0;
        }
        
        .steps-box {
            background-color: #eff6ff;
            border-radius: 12px;
            padding: 24px;
            margin: 30px 0;
        }
        
        .steps-box strong {
            color: #1e40af;
            font-size: 16px;
            margin-bottom: 16px;
            display: block;
        }
        
        ol {
            margin-left: 20px;
            color: #4b5563;
        }
        
        li {
            margin-bottom: 8px;
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
            
            .cta-button {
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
            <div class="header-icon">📧</div>
            <h1>Confirm Your Email Address</h1>
            <p>Complete your account registration</p>
        </div>
        
        <div class="content">
            <div class="greeting">Hello {{ $name }},</div>
            
            <div class="message-box">
                <p>Thank you for your interest! Your account request has been received.</p>
                <p>To complete your registration, please confirm your email address by clicking the button below:</p>
            </div>
            
            <div style="text-align: center;">
                <a href="{{ $confirmationUrl }}" class="cta-button">Confirm My Email</a>
            </div>
            
            <div class="warning-box">
                <strong>⏰ Link Valid for 48 Hours</strong>
                <p>This link will expire in 48 hours. Please confirm your email address before expiration.</p>
            </div>
            
            <p>If the button above doesn't work, please copy and paste this link into your browser:</p>
            <div class="link-box">{{ $confirmationUrl }}</div>
            
            <p>Confirmation Code (alternative):</p>
            <div class="code-box">{{ $token }}</div>
            
            <div class="steps-box">
                <strong>Next Steps</strong>
                <ol>
                    <li>Click the confirmation link</li>
                    <li>An administrator will review your request</li>
                    <li>You'll receive a notification once approved</li>
                </ol>
            </div>
        </div>
        
        <div class="footer">
            <p>If you did not request an account, please ignore this email.</p>
            <p>This email was sent automatically. Please do not reply directly.</p>
            <p>© {{ date('Y') }} Bitchest. All rights reserved.</p>
        </div>
    </div>
</body>
</html>