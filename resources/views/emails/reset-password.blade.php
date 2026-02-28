<html>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto;">
        <h2>Réinitialisation de votre mot de passe</h2>
        
        <p>Bonjour {{ $userName }},</p>
        
        <p>Vous avez demandé la réinitialisation de votre mot de passe. Cliquez sur le lien ci-dessous pour continuer :</p>
        
        <div style="margin: 30px 0;">
            <a href="{{ $resetLink }}" style="display: inline-block; padding: 12px 30px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">
                Réinitialiser mon mot de passe
            </a>
        </div>
        
        <p>Ce lien expire dans 60 minutes.</p>
        
        <p>Si vous n'avez pas demandé cette réinitialisation, ignorez cet e-mail.</p>
        
        <hr style="border: none; border-top: 1px solid #ddd; margin: 30px 0;">
        
        <p style="color: #666; font-size: 12px;">
            Cordialement,<br>
            <strong>{{ config('app.name') }}</strong>
        </p>
    </div>
</body>
</html>
