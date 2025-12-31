<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 30px 20px;
            text-align: center;
            margin-bottom: 20px;
            border-radius: 5px 5px 0 0;
        }
        .header h2 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            background: #f9f9f9;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 0 0 5px 5px;
        }
        .message {
            margin: 20px 0;
            padding: 15px;
            background: #fff;
            border-left: 4px solid #667eea;
            border-radius: 3px;
        }
        .confirmation-button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
            font-weight: bold;
            text-align: center;
        }
        .confirmation-button:hover {
            background-color: #45a049;
        }
        .code {
            display: inline-block;
            padding: 5px 10px;
            background: #f0f0f0;
            border: 1px solid #ddd;
            border-radius: 3px;
            font-family: monospace;
            font-weight: bold;
            color: #667eea;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 0.9em;
            color: #666;
            text-align: center;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 12px;
            border-radius: 3px;
            margin: 20px 0;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>✉️ Confirmez votre adresse email</h2>
    </div>

    <div class="content">
        <p>Bonjour {{ $name }},</p>
        
        <div class="message">
            <p>Merci de votre intérêt ! Votre demande de création de compte a été reçue.</p>
            <p>Pour finaliser votre inscription, veuillez confirmer votre adresse email en cliquant sur le bouton ci-dessous :</p>
        </div>

        <div style="text-align: center;">
            <a href="{{ $confirmationUrl }}" class="confirmation-button">
                ✓ Confirmer mon email
            </a>
        </div>

        <div class="warning">
            <strong>⚠️ Lien valide pendant 48 heures</strong>
            <p style="margin: 10px 0 0 0; font-size: 0.95em;">
                Ce lien expirera dans 48 heures. Assurez-vous de confirmer votre adresse email avant l'expiration.
            </p>
        </div>

        <p style="margin-top: 20px;">
            Si le bouton ci-dessus ne fonctionne pas, veuillez copier et coller ce lien dans votre navigateur :
        </p>
        <p style="word-break: break-all; padding: 10px; background: #f5f5f5; border-radius: 3px;">
            {{ $confirmationUrl }}
        </p>

        <p style="margin-top: 20px;">
            Code de confirmation (alternative) :<br>
            <span class="code">{{ $token }}</span>
        </p>

        <div style="margin-top: 30px; padding: 15px; background: #e3f2fd; border-radius: 3px; border-left: 4px solid #2196F3;">
            <strong>Prochaines étapes :</strong>
            <ol style="margin: 10px 0 0 0; padding-left: 20px;">
                <li>Cliquez sur le lien de confirmation</li>
                <li>Un administrateur examinera votre demande</li>
                <li>Vous recevrez une notification une fois approuvé</li>
            </ol>
        </div>

        <div class="footer">
            <p style="margin: 10px 0;">
                Si vous n'avez pas demandé la création d'un compte, veuillez ignorer cet email.
            </p>
            <p style="margin: 5px 0; font-size: 0.85em;">
                Cet email a été envoyé automatiquement. Merci de ne pas y répondre directement.
            </p>
            <p style="margin: 10px 0 0 0; padding-top: 10px; border-top: 1px solid #ddd; font-size: 0.85em;">
                © {{ date('Y') }} Bitchest - Tous droits réservés
            </p>
        </div>
    </div>
</body>
</html>
