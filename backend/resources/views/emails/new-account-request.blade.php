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
            background: #1a1a1a;
            color: #fff;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }
        .content {
            background: #f9f9f9;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 5px;
        }
        .details {
            margin: 20px 0;
            padding: 15px;
            background: #fff;
            border-left: 4px solid #2196F3;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Nouvelle demande de compte</h2>
    </div>

    <div class="content">
        <p>Bonjour,</p>
        
        <p>Une nouvelle demande de création de compte a été reçue :</p>

        <div class="details">
            <p><strong>Nom :</strong> {{ $name }}</p>
            <p><strong>Email :</strong> {{ $email }}</p>
            <p><strong>Date de la demande :</strong> {{ now()->format('d/m/Y H:i') }}</p>
        </div>

        <p>
            Pour traiter cette demande, veuillez vous connecter à l'interface d'administration :
        </p>

        <p style="text-align: center;">
            <a href="{{ config('app.url') }}/admin/account-requests" class="button">
                Gérer la demande
            </a>
        </p>

        <p style="margin-top: 20px; font-size: 0.9em; color: #666;">
            Cet email a été envoyé automatiquement, merci de ne pas y répondre.
        </p>
    </div>
</body>
</html>
