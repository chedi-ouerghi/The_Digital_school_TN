<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Création de votre compte</title>
    <style>
        body {
            font-family: "Segoe UI", Roboto, Arial, sans-serif;
            background-color: #f7f9fc;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        .header {
            background-color: #0b0d12;
            text-align: center;
            padding: 25px 0;
        }
        .header img {
            width: 140px;
            height: auto;
        }
        .content {
            padding: 30px 40px;
            color: #333333;
            line-height: 1.6;
        }
        .content h1 {
            color: #0b0d12;
            font-size: 22px;
            margin-bottom: 15px;
        }
        .temp-pass {
            background-color: #f3f6fb;
            border: 1px dashed #b0c4de;
            color: #0b0d12;
            font-weight: bold;
            font-size: 18px;
            padding: 12px;
            text-align: center;
            border-radius: 6px;
            margin: 20px 0;
        }
        .button-container {
            text-align: center;
            margin-top: 25px;
        }
        a.button {
            display: inline-block;
            background-color: #007bff;
            color: #fff !important;
            padding: 14px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            letter-spacing: 0.3px;
            transition: background-color 0.2s ease-in-out;
        }
        a.button:hover {
            background-color: #0056b3;
        }
        .footer {
            background-color: #f3f6fb;
            text-align: center;
            padding: 20px;
            font-size: 13px;
            color: #666;
            border-top: 1px solid #e4e4e4;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header avec logo -->
        <div class="header">
            <img src="{{ $message->embed(public_path('assets/bitchest_logo.png')) }}" alt="Bitchest Logo">
        </div>

        <!-- Contenu -->
        <div class="content">
            <h1>Bonjour {{ $userName }},</h1>

            <p>🎉 Votre compte a été créé avec succès sur <strong>{{ config('app.name') }}</strong> !</p>

            <p>Voici vos informations de connexion temporaires :</p>

            <div class="temp-pass">
                🔐 Mot de passe temporaire : <strong>{{ $tempPassword }}</strong>
            </div>

            <p>Pour accéder à votre compte, cliquez sur le bouton ci-dessous :</p>

            <div class="button-container">
                <a href="http://localhost:5173/signin" target="_blank" class="button">Accéder à mon compte</a>
            </div>

            <p style="margin-top: 25px;">
                Par mesure de sécurité, nous vous recommandons de changer votre mot de passe dès votre première connexion.<br><br>
                Merci de votre confiance,<br>
                L’équipe <strong>{{ $note ?? config('app.name') }}</strong>
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. Tous droits réservés.<br>
            Cet email a été envoyé automatiquement — merci de ne pas y répondre.
        </div>
    </div>
</body>
</html>
