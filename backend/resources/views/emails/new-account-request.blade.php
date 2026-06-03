<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Account Request</title>
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
            background-color: #1f2937;
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header-icon {
            font-size: 40px;
            margin-bottom: 15px;
        }
        
        .header h1 {
            font-size: 24px;
            font-weight: 700;
            color: white;
        }
        
        .content {
            padding: 40px 30px;
        }
        
        .greeting {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 25px;
        }
        
        .notification-box {
            background-color: #f8fafc;
            border-radius: 12px;
            padding: 24px;
            margin: 25px 0;
            border-left: 4px solid #01FF19;
        }
        
        .details-grid {
            display: grid;
            gap: 16px;
            margin-top: 20px;
        }
        
        .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .detail-item:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: 600;
            color: #4b5563;
        }
        
        .detail-value {
            font-weight: 500;
            color: #111827;
        }
        
        .admin-button {
            display: inline-block;
            background-color: #38618C;
            color: white;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 15px;
            text-align: center;
            transition: all 0.3s ease;
            margin: 20px 0;
        }
        
        .admin-button:hover {
            background-color: #2d4f6f;
            transform: translateY(-1px);
        }
        
        .footer {
            background-color: #f8fafc;
            padding: 24px 30px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }
        
        @media (max-width: 480px) {
            .content, .header {
                padding: 30px 20px;
            }
            
            .header h1 {
                font-size: 22px;
            }
            
            .details-grid {
                gap: 12px;
            }
            
            .admin-button {
                padding: 12px 24px;
                width: 100%;
                display: block;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="header">
            <div class="header-icon">🔔</div>
            <h1>New Account Request</h1>
        </div>
        
        <div class="content">
            <div class="greeting">Hello Admin,</div>
            
            <div class="notification-box">
                <p>A new account request has been submitted:</p>
                
                <div class="details-grid">
                    <div class="detail-item">
                        <span class="detail-label">Name:</span>
                        <span class="detail-value">{{ $name }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Email:</span>
                        <span class="detail-value">{{ $email }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Request Date:</span>
                        <span class="detail-value">{{ now()->format('F j, Y H:i') }}</span>
                    </div>
                </div>
            </div>
            
            <p>To manage this request, please log in to the admin interface:</p>
            
            <div style="text-align: center;">
                <a href="{{ config('app.url') }}/admin/account-requests" class="admin-button">
                    Manage Request
                </a>
            </div>
        </div>
        
        <div class="footer">
            <p>This email was sent automatically. Please do not reply.</p>
        </div>
    </div>
</body>
</html>